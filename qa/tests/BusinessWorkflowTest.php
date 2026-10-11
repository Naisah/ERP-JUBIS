<?php
namespace JubisQA;
use App\Models\{Invoice, Product, Quote, QuoteItem, PurchaseOrder, PurchaseOrderItem, RMA, Shipment, StockMovement, Supplier, User};
use Illuminate\Support\Facades\{DB, Mail};
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\{DataProvider, Group};

#[Group('baseline')]
class BusinessWorkflowTest extends IsolatedTestCase
{
    public function test_sales_quote_invoice_and_partial_payment_workflow(): void
    {
        $client = $this->actor(); $product = $this->product();
        $this->actingAs($client)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 3])->assertRedirect();
        $quote = Quote::where('user_id', $client->id)->firstOrFail();
        $this->assertEquals(300, $quote->subtotal); $this->assertEquals(36, $quote->vat_amount); $this->assertEquals(336, $quote->total_amount);
        $this->post('/cart/submit', ['shipping_address' => 'QA Address', 'client_notes' => 'QA'])->assertRedirect('/products');
        $this->assertEquals(97, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'quantity' => -3, 'user_id' => $client->id, 'type' => 'sale']);
        $this->actingAs($this->actor('sales'))->put('/admin/quotes/'.$quote->id, ['status' => 'approved'])->assertRedirect();
        $invoice = Invoice::where('quote_id', $quote->id)->firstOrFail();
        $this->assertEquals(336, $invoice->total_amount);
        $this->actingAs($this->actor('finance'))->post('/admin/invoices/'.$invoice->id.'/payment', ['amount_paid' => 100])->assertRedirect();
        $this->assertEquals(100, $invoice->fresh()->amount_paid); $this->assertSame('partially_paid', $invoice->fresh()->status);
        $this->post('/admin/invoices/'.$invoice->id.'/payment', ['amount_paid' => 236])->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->status); $this->assertEquals(336, $invoice->fresh()->amount_paid);
    }

    public function test_insufficient_second_item_rolls_back_stock_movements_and_status(): void
    {
        $client = $this->actor(); $first = $this->product(); $second = $this->product(['stock_quantity' => 0]);
        $quote = $this->quote($client, $first, 2, 'draft');
        QuoteItem::create(['quote_id' => $quote->id, 'product_id' => $second->id, 'quantity' => 1, 'quoted_price' => 100]);
        $this->actingAs($client)->post('/cart/submit', ['shipping_address' => 'QA'])->assertRedirect()->assertSessionHas('error');
        $this->assertEquals(100, $first->fresh()->stock_quantity); $this->assertEquals(0, $second->fresh()->stock_quantity);
        $this->assertSame('draft', $quote->fresh()->status); $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_rejecting_reserved_quote_restores_stock_once(): void
    {
        $client = $this->actor(); $product = $this->product(); $quote = $this->quote($client, $product, 2, 'draft');
        $this->actingAs($client)->post('/cart/submit', ['shipping_address' => 'QA'])->assertRedirect();
        $this->actingAs($this->actor('sales'))->put('/admin/quotes/'.$quote->id, ['status' => 'rejected'])->assertRedirect();
        $this->put('/admin/quotes/'.$quote->id, ['status' => 'rejected'])->assertRedirect();
        $this->assertEquals(100, $product->fresh()->stock_quantity);
        $this->assertEquals(1, StockMovement::where('reference_id', 'QUOTE-REJECT-'.$quote->id)->count());
    }

    public function test_cart_cannot_remove_another_clients_item(): void
    {
        $victim = $this->actor(); $quote = $this->quote($victim, $this->product(), 1, 'draft'); $item = $quote->items()->first();
        $this->actingAs($this->actor())->delete('/cart/'.$item->id)->assertNotFound();
        $this->assertDatabaseHas('quote_items', ['id' => $item->id]);
    }

    #[DataProvider('quantities')]
    public function test_invalid_order_quantities_are_rejected(mixed $quantity): void
    {
        $product = $this->product();
        $this->actingAs($this->actor())->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => $quantity])->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->assertDatabaseCount('quote_items', 0); $this->assertEquals(100, $product->fresh()->stock_quantity);
    }
    public static function quantities(): array { return [[0], [-1], [1.5], ['NaN'], ['abc'], [null], [PHP_INT_MAX.'0']]; }

    public function test_cart_rejects_out_of_stock_and_cumulative_over_stock(): void
    {
        $product = $this->product(['stock_quantity' => 3]); $this->actingAs($this->actor());
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 3])->assertRedirect();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertSessionHas('error');
        $this->assertEquals(3, QuoteItem::first()->quantity);
        $empty = $this->product(['stock_quantity' => 0]);
        $this->post('/cart/add', ['product_id' => $empty->id, 'quantity' => 1])->assertSessionHas('error');
    }

    #[DataProvider('creditQuantities')]
    public function test_net30_quantity_threshold(int $quantity, int $status): void
    {
        $client = $this->actor(); $invoice = $this->invoice($client, $quantity);
        $this->actingAs($client)->post('/api/invoices/'.$invoice->id.'/accept-terms')->assertStatus($status);
        if ($status === 302) {
            $this->assertSame('on_terms', $invoice->fresh()->status); $this->assertSame(now()->addDays(30)->toDateString(), $invoice->fresh()->due_date->toDateString());
        } else $this->assertSame('unpaid', $invoice->fresh()->status);
    }
    public static function creditQuantities(): array { return [[999,400], [1000,302], [1001,302]]; }

    public function test_terms_and_pdf_are_isolated_between_clients(): void
    {
        $invoice = $this->invoice($this->actor(), 1000);
        $this->actingAs($this->actor())->post('/api/invoices/'.$invoice->id.'/accept-terms')->assertForbidden();
        $this->get('/invoices/'.$invoice->id.'/pdf')->assertForbidden();
    }

    public function test_purchase_receipt_is_idempotent_and_reversal_restores_original_stock(): void
    {
        $supplier = Supplier::create(['name' => 'QA Supplier']); $product = $this->product(); $this->actingAs($this->actor('purchasing'));
        $this->post('/admin/purchase-orders', ['supplier_id' => $supplier->id, 'expected_delivery_date' => null, 'notes' => '', 'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => '80.25']]])->assertRedirect();
        $po = PurchaseOrder::firstOrFail(); $this->assertEquals(401.25, $po->total_cost);
        $this->post('/admin/purchase-orders/'.$po->id.'/status', ['status'=>'ordered'])->assertRedirect();
        $this->post('/admin/purchase-orders/'.$po->id.'/status', ['status'=>'received'])->assertRedirect();
        $this->post('/admin/purchase-orders/'.$po->id.'/status', ['status'=>'received'])->assertRedirect();
        $this->assertEquals(105, $product->fresh()->stock_quantity); $this->assertEquals(1, StockMovement::where('type','purchase')->count());
        $this->post('/admin/purchase-orders/'.$po->id.'/status', ['status'=>'ordered'])->assertRedirect();
        $this->assertEquals(100, $product->fresh()->stock_quantity);
        $this->assertEquals(0, StockMovement::where('product_id', $product->id)->sum('quantity'));
    }

    public function test_rma_creation_is_itemized_and_duplicate_safe_sequentially(): void
    {
        $client = $this->actor(); $invoice = $this->invoice($client, 2, 'paid'); $this->actingAs($client);
        $this->post('/invoices/'.$invoice->id.'/rma')->assertRedirect();
        $this->post('/invoices/'.$invoice->id.'/rma')->assertSessionHas('error');
        $this->assertDatabaseCount('r_m_a_s',1); $this->assertDatabaseCount('r_m_a_items',1);
        $this->assertDatabaseHas('r_m_a_items', ['quantity'=>2]);
    }

    public function test_shipment_fields_and_sequential_duplicate_invoice_validation(): void
    {
        $invoice = $this->invoice($this->actor(), 2, 'paid'); $this->actingAs($this->actor('warehouse'));
        $payload = ['invoice_id'=>$invoice->id, 'carrier'=>'Manual courier', 'shipping_address'=>'QA'];
        $this->post('/admin/shipments', $payload)->assertRedirect();
        $this->postJson('/admin/shipments', $payload)->assertUnprocessable()->assertJsonValidationErrors('invoice_id');
        $this->assertDatabaseCount('shipments',1);
    }

    public function test_duplicate_sku_and_negative_stock_are_rejected(): void
    {
        $product = $this->product(); $this->actingAs($this->actor('warehouse'));
        $this->postJson('/admin/inventory', $this->productPayload($product))->assertUnprocessable()->assertJsonValidationErrors('sku');
        $this->putJson('/admin/inventory/'.$product->id, $this->productPayload($product,['stock_quantity'=>-1]))->assertUnprocessable()->assertJsonValidationErrors('stock_quantity');
        $this->assertEquals(100, $product->fresh()->stock_quantity);
    }

    public function test_catalog_search_filters_and_sql_injection_do_not_expand_results(): void
    {
        $product = $this->product(['name'=>'QA Unique Product']); $this->product(['is_active'=>false]);
        $this->get('/products?search=Unique')->assertInertia(fn(Assert $p)=>$p->where('products.total',1)->where('products.data.0.id',$product->id));
        $this->get('/products?search='.urlencode("' OR 1=1 --"))->assertOk()->assertInertia(fn(Assert $p)=>$p->where('products.total',0));
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->actor('admin'); $this->actingAs($admin)->delete('/admin/users/'.$admin->id)->assertSessionHas('error');
        $this->assertDatabaseHas('users',['id'=>$admin->id]);
    }
}

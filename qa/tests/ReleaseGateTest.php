<?php
namespace JubisQA;
use App\Models\{Invoice, Product, PurchaseOrder, PurchaseOrderItem, Quote, Shipment, StockMovement, Supplier};
use Illuminate\Support\Facades\{Cache, DB, Mail};
use PHPUnit\Framework\Attributes\{DataProvider, Group};

// Assertions describe required release behavior. Existing defects intentionally fail.
#[Group('release-gates')]
class ReleaseGateTest extends IsolatedTestCase
{
    public function test_sec01_unsigned_payment_event_is_rejected_without_mutation(): void
    {
        $invoice=$this->invoice($this->actor()); $invoice->update(['payment_reference_id'=>'link_unsigned']);
        putenv('PAYMONGO_WEBHOOK_SECRET=qa-secret'); $_ENV['PAYMONGO_WEBHOOK_SECRET']=$_SERVER['PAYMONGO_WEBHOOK_SECRET']='qa-secret';
        config(['services.paymongo.webhook_secret' => 'qa-secret']);
        try {
            $response=$this->postJson('/webhooks/paymongo',['data'=>['attributes'=>['type'=>'link.payment.paid','data'=>['id'=>'link_unsigned']]]]);
            $this->assertSame('unpaid',$invoice->fresh()->status,'Unsigned event changed financial state despite a configured webhook secret.');
            $response->assertUnauthorized();
        } finally { putenv('PAYMONGO_WEBHOOK_SECRET='); $_ENV['PAYMONGO_WEBHOOK_SECRET']=$_SERVER['PAYMONGO_WEBHOOK_SECRET']=''; }
    }
    public function test_sec02_guest_cannot_process_simulator_payment(): void
    {
        $invoice=$this->invoice($this->actor());
        $response=$this->postJson('/api/mock/payment/process/invoice/'.$invoice->id,['payment_method'=>'card']);
        $this->assertSame('unpaid',$invoice->fresh()->status,'Guest changed an invoice to paid.'); $response->assertUnauthorized();
    }
    public function test_sec03_other_client_cannot_process_simulator_payment(): void
    {
        $invoice=$this->invoice($this->actor());
        $response=$this->actingAs($this->actor())->postJson('/api/mock/payment/process/invoice/'.$invoice->id,['payment_method'=>'card']);
        $this->assertSame('unpaid',$invoice->fresh()->status); $response->assertForbidden();
    }
    public function test_fin01_paid_invoice_cannot_be_reopened_as_credit_terms(): void
    {
        $client=$this->actor(); $invoice=$this->invoice($client,1000,'paid');
        $this->actingAs($client)->post('/api/invoices/'.$invoice->id.'/accept-terms');
        $this->assertSame('paid',$invoice->fresh()->status);
    }
    public function test_fin02_manual_payment_cannot_exceed_outstanding_balance(): void
    {
        $invoice=$this->invoice($this->actor());
        $response=$this->actingAs($this->actor('finance'))->postJson('/admin/invoices/'.$invoice->id.'/payment',['amount_paid'=>225]);
        $this->assertEquals(0,$invoice->fresh()->amount_paid,'Payment exceeding outstanding balance was persisted.');
        $response->assertUnprocessable();
    }
    public function test_fin03_payment_must_be_positive(): void
    {
        $invoice=$this->invoice($this->actor());
        $response=$this->actingAs($this->actor('finance'))->postJson('/admin/invoices/'.$invoice->id.'/payment',['amount_paid'=>0]);
        $this->assertSame('unpaid',$invoice->fresh()->status,'Zero payment changed the financial status.');
        $response->assertUnprocessable();
    }
    public function test_fin04_payment_reference_is_persisted_when_quote_is_approved(): void
    {
        $quote=$this->quote($this->actor(),$this->product());
        $this->actingAs($this->actor('sales'))->put('/admin/quotes/'.$quote->id,['status'=>'approved']);
        $this->assertNotEmpty(Invoice::firstOrFail()->payment_reference_id);
    }
    public function test_fin05_simulation_checkout_url_resolves_to_a_real_route(): void
    {
        $invoice=$this->invoice($this->actor()); $link=(new \App\Services\PayMongoService)->createPaymentLink($invoice);
        $request=\Illuminate\Http\Request::create($link['checkout_url']);
        try { $route=app('router')->getRoutes()->match($request); }
        catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) { $this->fail('Generated simulator checkout URL has no registered route.'); }
        $this->assertNotNull($route);
    }
    public function test_exp01_invoice_lines_match_quoted_price_and_line_total(): void
    {
        $invoice=$this->invoice($this->actor()); $invoice->load(['quote.items.product','user','shipments']);
        $html=view('pdf.invoice',['invoice'=>$invoice])->render();
        $this->assertStringContainsString('PHP 100.00',$html,'Unit price in invoice is not the quoted price.');
        $this->assertStringContainsString('PHP 200.00',$html);
    }
    public function test_exp02_super_admin_can_download_another_clients_invoice(): void
    {
        $invoice=$this->invoice($this->actor());
        $response=$this->actingAs($this->actor('super_admin'))->get('/invoices/'.$invoice->id.'/pdf')->assertOk();
        $this->assertStringStartsWith('%PDF-',$response->getContent());
    }
    public function test_exp03_csv_escapes_commas_quotes_and_formula_prefixes(): void
    {
        $product=$this->product(['sku'=>'QA,CSV','brand'=>'Brand, Inc','name'=>'=1+1']);
        $csv=$this->actingAs($this->actor('warehouse'))->get('/admin/inventory-export')->assertOk()->getContent();
        $lines=explode("\n",trim($csv)); $row=str_getcsv($lines[1]);
        $this->assertCount(8,$row,'Export columns shifted because a cell contains a comma.');
        $this->assertSame('QA,CSV',$row[1]); $this->assertFalse(preg_match('/^[=+@-]/',$row[2])===1,'Spreadsheet formula injection was not neutralized.');
    }
    public function test_inv01_rejected_quote_cannot_be_reapproved_then_restore_stock_twice(): void
    {
        $client=$this->actor(); $product=$this->product(); $quote=$this->quote($client,$product,2,'draft');
        $this->actingAs($client)->post('/cart/submit'); $this->actingAs($this->actor('sales'));
        $this->put('/admin/quotes/'.$quote->id,['status'=>'rejected']);
        $this->put('/admin/quotes/'.$quote->id,['status'=>'approved']);
        $this->put('/admin/quotes/'.$quote->id,['status'=>'rejected']);
        $this->assertLessThanOrEqual(100,$product->fresh()->stock_quantity,'Status cycling manufactured inventory.');
    }
    public function test_inv02_direct_stock_adjustment_records_actor_and_delta(): void
    {
        $product=$this->product(); $actor=$this->actor('warehouse');
        $this->actingAs($actor)->put('/admin/inventory/'.$product->id,$this->productPayload($product,['stock_quantity'=>101]));
        $this->assertDatabaseHas('stock_movements',['product_id'=>$product->id,'user_id'=>$actor->id,'quantity'=>1,'type'=>'adjustment']);
    }
    public function test_inv03_po_reversal_cannot_make_stock_negative(): void
    {
        $product=$this->product(['stock_quantity'=>2]); $supplier=Supplier::create(['name'=>'QA']);
        $po=PurchaseOrder::create(['supplier_id'=>$supplier->id,'status'=>'received','total_cost'=>500]);
        PurchaseOrderItem::create(['purchase_order_id'=>$po->id,'product_id'=>$product->id,'quantity'=>5,'unit_cost'=>100]);
        $this->actingAs($this->actor('purchasing'))->post('/admin/purchase-orders/'.$po->id.'/status',['status'=>'ordered']);
        $this->assertGreaterThanOrEqual(0,$product->fresh()->stock_quantity);
    }
    public function test_int01_repeated_delivered_status_does_not_duplicate_email(): void
    {
        $invoice=$this->invoice($this->actor(),2,'paid'); $shipment=Shipment::create(['invoice_id'=>$invoice->id,'carrier'=>'QA','status'=>'in_transit','shipping_address'=>'QA']);
        $this->actingAs($this->actor('warehouse'));
        $this->post('/admin/shipments/'.$shipment->id.'/status',['status'=>'delivered']);
        $this->post('/admin/shipments/'.$shipment->id.'/status',['status'=>'delivered']);
        Mail::assertSent(\App\Mail\ShipmentDeliveredMail::class,1);
    }
    public function test_int02_tracking_view_renders_known_shipment(): void
    {
        $invoice=$this->invoice($this->actor(),2,'paid');
        Shipment::create(['invoice_id'=>$invoice->id,'carrier'=>'QA','status'=>'dispatched','tracking_number'=>'QA-TRACK','shipping_address'=>'QA']);
        $this->get('/api/mock/tracking/QA-TRACK')->assertOk()->assertSee('QA-TRACK');
    }
    public function test_cfg01_unknown_settings_fields_are_rejected_or_discarded(): void
    {
        $this->actingAs($this->actor('admin'))->post('/admin/settings',['company_name'=>'QA','vat_rate'=>12,'role'=>'super_admin','smtp_password'=>'QA should not be public']);
        $settings=Cache::get('global_settings',[]); $this->assertArrayNotHasKey('smtp_password',$settings); $this->assertArrayNotHasKey('role',$settings);
    }
    public function test_cfg02_invalid_vat_rate_is_rejected(): void
    {
        $this->actingAs($this->actor('admin'))->postJson('/admin/settings',['vat_rate'=>-1])->assertUnprocessable();
    }
    public function test_cat01_inactive_product_cannot_be_added_by_direct_api_request(): void
    {
        $product=$this->product(['is_active'=>false]);
        $this->actingAs($this->actor())->post('/cart/add',['product_id'=>$product->id,'quantity'=>1]);
        $this->assertDatabaseCount('quote_items',0);
    }
    public function test_schema01_one_invoice_per_quote_is_enforced_in_database(): void
    {
        $invoice=$this->invoice($this->actor()); $values=$invoice->getAttributes(); unset($values['id']);
        $this->expectException(\Illuminate\Database\QueryException::class); DB::table('invoices')->insert($values);
    }
    public function test_fin06_centavo_conversion_does_not_truncate_binary_float(): void
    {
        $invoice=$this->invoice($this->actor(),1);
        $money=['subtotal'=>'0.26','vat_amount'=>'0.03','total_amount'=>'0.29'];
        $invoice->quote->update($money); $invoice->quote->items()->update(['quoted_price'=>'0.26']);
        $invoice->update($money);
        putenv('PAYMONGO_SECRET_KEY=sk_test_QA_only'); $_ENV['PAYMONGO_SECRET_KEY']=$_SERVER['PAYMONGO_SECRET_KEY']='sk_test_QA_only';
        config(['services.paymongo.secret_key' => 'sk_test_QA_only']);
        \Illuminate\Support\Facades\Http::fake(['api.paymongo.com/*'=>\Illuminate\Support\Facades\Http::response(['data'=>['id'=>'link_QA','attributes'=>['checkout_url'=>'https://example.test/checkout']]],200)]);
        try {
            (new \App\Services\PayMongoService)->createPaymentLink($invoice);
            \Illuminate\Support\Facades\Http::assertSent(fn($request)=>$request['data']['attributes']['amount']===29);
        } finally { putenv('PAYMONGO_SECRET_KEY='); $_ENV['PAYMONGO_SECRET_KEY']=$_SERVER['PAYMONGO_SECRET_KEY']=''; }
    }
    public function test_sec04_stale_signed_webhook_cannot_change_financial_state(): void
    {
        $invoice=$this->invoice($this->actor()); $invoice->update(['payment_reference_id'=>'link_QA_stale']);
        $body=json_encode(['data'=>['attributes'=>['type'=>'link.payment.paid','livemode'=>false,'data'=>['id'=>'link_QA_stale']]]]);
        $ts=time()-3600; $signature=hash_hmac('sha256',$ts.'.'.$body,'qa-secret');
        putenv('PAYMONGO_WEBHOOK_SECRET=qa-secret'); $_ENV['PAYMONGO_WEBHOOK_SECRET']=$_SERVER['PAYMONGO_WEBHOOK_SECRET']='qa-secret';
        config(['services.paymongo.webhook_secret' => 'qa-secret']);
        try {
            $this->call('POST','/webhooks/paymongo',[],[],[],['CONTENT_TYPE'=>'application/json','HTTP_PAYMONGO_SIGNATURE'=>"t=$ts,te=$signature"],$body);
            $this->assertSame('unpaid',$invoice->fresh()->status,'A valid but hour-old signature was replayed.');
        } finally { putenv('PAYMONGO_WEBHOOK_SECRET='); $_ENV['PAYMONGO_WEBHOOK_SECRET']=$_SERVER['PAYMONGO_WEBHOOK_SECRET']=''; }
    }
    public function test_rma01_item_failure_rolls_back_entire_return_request(): void
    {
        $client=$this->actor(); $invoice=$this->invoice($client,2,'paid');
        \App\Models\RMAItem::creating(fn()=>throw new \RuntimeException('QA injected line failure'));
        try {
            $this->actingAs($client)->post('/invoices/'.$invoice->id.'/rma')->assertStatus(500);
            $this->assertDatabaseCount('r_m_a_s',0); $this->assertDatabaseCount('r_m_a_items',0);
        } finally { \App\Models\RMAItem::flushEventListeners(); }
    }
    public function test_schema02_one_shipment_per_invoice_is_enforced_in_database(): void
    {
        $invoice=$this->invoice($this->actor()); $shipment=Shipment::create(['invoice_id'=>$invoice->id,'carrier'=>'QA','shipping_address'=>'QA']);
        $values=$shipment->getAttributes(); unset($values['id']);
        $this->expectException(\Illuminate\Database\QueryException::class); DB::table('shipments')->insert($values);
    }
    public static function missingHandlers(): array
    {
        $rows=json_decode(file_get_contents(__DIR__.'/../data/routes.json'),true); $cases=[];
        foreach($rows as $row) if(!$row['handler_exists']) $cases[$row['uri'].'|'.$row['methods'][0]]=[$row['action']];
        return $cases ?: ['none'=>['Closure']];
    }
    #[DataProvider('missingHandlers')]
    public function test_api01_registered_controller_actions_exist(string $action): void
    {
        if($action==='Closure') { $this->assertTrue(true); return; }
        [$controller,$method]=explode('@',$action); $this->assertTrue(method_exists($controller,$method),$action.' is registered but missing.');
    }
}

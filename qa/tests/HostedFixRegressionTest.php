<?php

namespace JubisQA;

use App\Models\{Invoice, Product, Quote, Shipment};
use App\Services\{CompanySettings, LalamoveService, SalesMetrics};
use Illuminate\Support\Facades\{Cache, DB, Mail};
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\{DataProvider, Group};

#[Group('baseline')]
class HostedFixRegressionTest extends IsolatedTestCase
{
    private function mockCourier(): void
    {
        $this->mock(LalamoveService::class, fn ($mock) => $mock->shouldReceive('bookDelivery')->once()->andReturn([
            'success' => true, 'carrier' => 'Lalamove (API Simulated)', 'tracking_number' => 'QA-FIX-TRACK',
            'tracking_url' => url('/api/mock/tracking/QA-FIX-TRACK'), 'delivery_fee' => 164,
        ]));
    }

    public function test_shipping_reconciles_once_across_invoice_checkout_and_pdf(): void
    {
        $client = $this->actor(); $invoice = $this->invoice($client); $this->mockCourier();
        $payload = ['invoice_id' => $invoice->id, 'carrier' => 'Lalamove', 'shipping_address' => 'QA'];
        $this->actingAs($this->actor('warehouse'))->post('/admin/shipments', $payload)->assertRedirect();
        $this->postJson('/admin/shipments', $payload)->assertUnprocessable();
        $invoice->refresh(); $this->assertEquals(388, $invoice->total_amount); $this->assertEquals(164, $invoice->shipping_amount);
        $this->assertEquals(388, $invoice->balance_due); $this->assertNull($invoice->payment_url);
        $this->actingAs($client)->get('/api/mock/payment/invoice/'.$invoice->id)->assertOk()->assertSee('388.00');
        $html = view('pdf.invoice', ['invoice' => $invoice->load(['quote.items.product', 'user', 'shipments'])])->render();
        $this->assertStringContainsString('PHP 388.00', $html); $this->assertStringNotContainsString('PHP 552.00', $html);
    }

    public function test_shipping_after_payment_adds_only_unpaid_shipping(): void
    {
        $client = $this->actor(); $invoice = $this->invoice($client, 2, 'paid'); $this->mockCourier();
        $this->actingAs($this->actor('warehouse'))->post('/admin/shipments', ['invoice_id' => $invoice->id, 'carrier' => 'Lalamove', 'shipping_address' => 'QA'])->assertRedirect();
        $invoice->refresh(); $this->assertEquals(224, $invoice->amount_paid); $this->assertEquals(164, $invoice->balance_due);
        $this->assertSame('partially_paid', $invoice->status);
        $this->actingAs($client)->post('/api/mock/payment/process/invoice/'.$invoice->id, ['payment_method' => 'gcash', 'expected_balance' => 164])->assertRedirect();
        $this->assertEquals(388, $invoice->fresh()->amount_paid);
        $this->assertDatabaseHas('payment_events', ['invoice_id' => $invoice->id, 'amount' => 164, 'source' => 'simulation']);
    }

    #[DataProvider('paymentMethods')]
    public function test_owned_simulations_are_repeat_safe(string $method): void
    {
        $client = $this->actor(); $invoice = $this->invoice($client); $this->actingAs($client);
        $url = '/api/mock/payment/process/invoice/'.$invoice->id;
        $this->post($url, ['payment_method' => $method])->assertRedirect();
        $this->post($url, ['payment_method' => $method])->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->status); $this->assertEquals(224, $invoice->fresh()->amount_paid);
        $this->assertDatabaseCount('payment_events', 1);
    }
    public static function paymentMethods(): array { return [['card'], ['gcash'], ['qrph']]; }

    public function test_simulators_are_disabled_when_configuration_is_off(): void
    {
        $client = $this->actor(); $invoice = $this->invoice($client);
        config(['services.paymongo.simulation' => false, 'services.lalamove.simulation' => false]);
        $this->actingAs($client)->get('/api/mock/payment/invoice/'.$invoice->id)->assertNotFound();
        $this->postJson('/api/mock/payment/process/invoice/'.$invoice->id, ['payment_method' => 'gcash'])->assertNotFound();
        $this->get('/api/mock/tracking/unknown')->assertNotFound();
        $this->assertSame('unpaid', $invoice->fresh()->status);
    }

    public function test_guest_and_other_client_cannot_read_checkout(): void
    {
        $invoice = $this->invoice($this->actor());
        $this->getJson('/api/mock/payment/invoice/'.$invoice->id)->assertUnauthorized();
        $this->getJson('/invoices/'.$invoice->id.'/checkout')->assertUnauthorized();
        $this->actingAs($this->actor())->get('/api/mock/payment/invoice/'.$invoice->id)->assertForbidden();
        $this->get('/invoices/'.$invoice->id.'/checkout')->assertForbidden();
    }

    public function test_invalid_method_and_stale_balance_do_not_post_payment(): void
    {
        $client = $this->actor(); $invoice = $this->invoice($client); $this->actingAs($client);
        $url = '/api/mock/payment/process/invoice/'.$invoice->id;
        $this->postJson($url, ['payment_method' => 'unknown'])->assertUnprocessable();
        $this->postJson($url, ['payment_method' => 'gcash', 'expected_balance' => 223])->assertStatus(409);
        $this->assertSame('unpaid', $invoice->fresh()->status); $this->assertDatabaseCount('payment_events', 0);
    }

    public function test_manual_payment_token_prevents_double_post(): void
    {
        $invoice = $this->invoice($this->actor()); $this->actingAs($this->actor('finance'));
        $url = '/admin/invoices/'.$invoice->id.'/payment'; $token = (string) \Illuminate\Support\Str::uuid();
        $this->postJson($url, ['amount_paid' => 100, 'payment_token' => $token])->assertRedirect();
        $this->postJson($url, ['amount_paid' => 100, 'payment_token' => $token])->assertRedirect();
        $this->postJson($url, ['amount_paid' => 101, 'payment_token' => $token])->assertUnprocessable();
        $this->postJson($url, ['amount_paid' => '0.001'])->assertUnprocessable();
        $this->assertEquals(100, $invoice->fresh()->amount_paid); $this->assertDatabaseCount('payment_events', 1);
    }

    public function test_stock_counts_ignore_folders_and_pagination(): void
    {
        for ($i = 0; $i < 20; $i++) $this->product(['stock_quantity' => 0]);
        $folder = $this->product(['stock_quantity' => 0]);
        $this->product(['parent_id' => $folder->id, 'stock_quantity' => 10]);
        $this->product(['stock_quantity' => 0, 'is_active' => false]);
        $this->actingAs($this->actor('warehouse'));
        foreach ([1, 2] as $page) $this->get('/admin/inventory?stock=out&page='.$page)->assertOk()->assertInertia(fn (Assert $view) => $view
            ->where('products.total', 20)->where('stockSummary.outOfStockCount', 20)->where('stockSummary.lowStockCount', 0));
        $this->assertSame(20, Product::outOfStock()->count()); $this->assertSame(1, Product::lowStock()->count());
    }

    public function test_dashboard_and_reports_share_cash_and_receivable_rules(): void
    {
        $invoice = $this->invoice($this->actor()); $this->invoice($this->actor(), 2, 'on_terms');
        $this->actingAs($this->actor('finance'))->post('/admin/invoices/'.$invoice->id.'/payment', ['amount_paid' => 100])->assertRedirect();
        $this->get('/admin/dashboard')->assertOk()->assertInertia(fn (Assert $view) => $view
            ->where('metrics.totalRevenue', fn ($v) => $v == 100)->where('metrics.outstandingReceivables', fn ($v) => $v == 348));
        $this->get('/admin/reports')->assertOk()->assertInertia(fn (Assert $view) => $view
            ->where('metrics.totalRevenue', fn ($v) => $v == 100)->where('metrics.pendingReceivables', fn ($v) => $v == 348));
        $this->assertEquals(100, collect(app(SalesMetrics::class)->monthlyRevenue())->sum('total'));
        $this->post('/admin/invoices/'.$invoice->id.'/payment', ['amount_paid' => 124])->assertRedirect();
        $this->get('/admin/dashboard')->assertOk()->assertInertia(fn (Assert $view) => $view->has('topProducts', 1)->where('topProducts.0.total_sold', fn ($v) => $v == 2));
    }

    public function test_settings_survive_cache_clear_without_public_secrets(): void
    {
        $this->actingAs($this->actor('admin'))->post('/admin/settings', ['company_name' => 'QA Persisted', 'vat_rate' => 0, 'smtp_password' => 'secret', 'role' => 'admin'])->assertRedirect();
        Cache::forget('global_settings');
        $this->assertSame('QA Persisted', app(CompanySettings::class)->all()['company_name']);
        $this->assertEquals(0, app(CompanySettings::class)->all()['vat_rate']);
        $this->get('/')->assertInertia(fn (Assert $view) => $view->where('settings.company_name', 'QA Persisted')->missing('settings.smtp_password')->missing('settings.role'));
    }

    public function test_zero_vat_snapshot_survives_a_later_rate_change(): void
    {
        app(CompanySettings::class)->save(['vat_rate' => 0]); $client = $this->actor(); $product = $this->product();
        $this->actingAs($client)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertRedirect();
        $quote = Quote::firstOrFail(); $this->assertEquals(0, $quote->vat_amount); $this->assertEquals(200, $quote->total_amount);
        $this->post('/cart/submit')->assertRedirect();
        $this->actingAs($this->actor('sales'))->put('/admin/quotes/'.$quote->id, ['status' => 'approved'])->assertRedirect();
        $invoice = Invoice::firstOrFail(); $this->assertEquals(0, $invoice->vat_rate);
        app(CompanySettings::class)->save(['vat_rate' => 12]);
        $this->assertEquals(0, $invoice->fresh()->vat_amount); $this->assertEquals(200, $invoice->fresh()->total_amount);
    }

    public function test_manila_pdf_and_csv_dates_agree_near_midnight(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-11T17:30:00Z'));
        $invoice = $this->invoice($this->actor());
        $html = view('pdf.invoice', ['invoice' => $invoice->load(['quote.items.product', 'user', 'shipments'])])->render();
        $this->assertStringContainsString('Oct 12, 2026', $html);
        $response = $this->actingAs($this->actor('warehouse'))->get('/admin/inventory-export')->assertOk();
        $this->assertStringContainsString('2026-10-12.csv', $response->headers->get('Content-Disposition'));
        $this->assertStringNotContainsString('123 Warehouse Avenue', $html); $this->assertStringNotContainsString('123-456-789-000', $html);
    }

    public function test_mail_failure_does_not_claim_success(): void
    {
        Mail::swap(\Mockery::mock()->shouldReceive('to')->andThrow(new \RuntimeException('QA SMTP unavailable'))->getMock());
        $this->post('/contact', ['first_name' => 'QA', 'last_name' => 'Test', 'email' => 'qa@example.test', 'message' => 'Test'])
            ->assertRedirect()->assertSessionHas('error')->assertSessionMissing('success');
    }

    public function test_tracking_unknown_is_404_and_known_status_renders(): void
    {
        $this->get('/api/mock/tracking/QA-UNKNOWN')->assertNotFound();
        Shipment::create(['invoice_id' => $this->invoice($this->actor())->id, 'carrier' => 'QA Demo', 'tracking_number' => 'QA-FIX-TRACK', 'status' => 'in_transit']);
        $this->get('/api/mock/tracking/QA-FIX-TRACK')->assertOk()->assertSee('Demo Delivery Tracking')->assertSee('In transit')->assertDontSee('Rider Juan');
    }

    public function test_live_courier_failure_rolls_back_instead_of_fabricating_success(): void
    {
        config(['services.lalamove.api_key' => 'qa', 'services.lalamove.api_secret' => 'qa', 'services.lalamove.simulation' => false]);
        $invoice = $this->invoice($this->actor());
        $this->actingAs($this->actor('warehouse'))->postJson('/admin/shipments', ['invoice_id' => $invoice->id, 'carrier' => 'Lalamove', 'shipping_address' => 'QA'])->assertUnprocessable();
        $this->assertDatabaseCount('shipments', 0); $this->assertEquals(224, $invoice->fresh()->total_amount);
    }

    public function test_manually_booked_lalamove_tracking_does_not_call_booking_again(): void
    {
        config(['services.lalamove.simulation' => false]);
        $this->mock(LalamoveService::class, fn ($mock) => $mock->shouldNotReceive('bookDelivery'));
        $invoice = $this->invoice($this->actor());
        $this->actingAs($this->actor('warehouse'))->post('/admin/shipments', [
            'invoice_id' => $invoice->id, 'carrier' => 'Lalamove', 'shipping_address' => 'QA', 'tracking_number' => 'QA-MANUAL-ORDER',
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('shipments', ['invoice_id' => $invoice->id, 'tracking_number' => 'QA-MANUAL-ORDER']);
        $this->assertEquals(224, $invoice->fresh()->total_amount);
    }

    public function test_pdf_finance_and_other_role_boundaries(): void
    {
        $invoice = $this->invoice($this->actor());
        $this->actingAs($this->actor('finance'))->get('/invoices/'.$invoice->id.'/pdf')->assertOk();
        $this->actingAs($this->actor('warehouse'))->get('/invoices/'.$invoice->id.'/pdf')->assertForbidden();
        $this->actingAs($this->actor())->get('/invoices/'.$invoice->id.'/pdf')->assertForbidden();
    }

    public function test_signed_webhook_is_repeat_safe_and_checks_its_amount(): void
    {
        config(['services.paymongo.webhook_secret' => 'qa-secret', 'services.paymongo.secret_key' => 'sk_test_QA']);
        $invoice = $this->invoice($this->actor()); $invoice->update(['payment_reference_id' => 'link_QA_replay']);
        $send = function (int $amount) {
            $body = json_encode(['data' => ['id' => 'evt_QA_replay', 'attributes' => ['type' => 'link.payment.paid', 'livemode' => false,
                'data' => ['id' => 'link_QA_replay', 'attributes' => ['amount' => $amount, 'currency' => 'PHP', 'status' => 'paid']]]]]);
            $timestamp = time(); $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'qa-secret');
            return $this->call('POST', '/webhooks/paymongo', [], [], [], ['CONTENT_TYPE' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => "t=$timestamp,te=$signature"], $body);
        };
        $send(22300)->assertUnprocessable(); $this->assertSame('unpaid', $invoice->fresh()->status);
        $send(22400)->assertOk(); $send(22400)->assertOk();
        $this->assertEquals(224, $invoice->fresh()->amount_paid); $this->assertDatabaseCount('payment_events', 1);
    }

    public function test_missing_secret_and_wrong_mode_signatures_fail_closed(): void
    {
        $invoice = $this->invoice($this->actor()); $invoice->update(['payment_reference_id' => 'link_QA_mode']);
        $body = json_encode(['data' => ['id' => 'evt_QA_mode', 'attributes' => ['type' => 'link.payment.paid', 'livemode' => true,
            'data' => ['id' => 'link_QA_mode', 'attributes' => ['amount' => 22400, 'currency' => 'PHP', 'status' => 'paid']]]]]);
        $timestamp = time(); $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'qa-secret');
        foreach ([null, 'qa-secret'] as $secret) {
            config(['services.paymongo.webhook_secret' => $secret, 'services.paymongo.secret_key' => 'sk_test_QA']);
            $this->call('POST', '/webhooks/paymongo', [], [], [], ['CONTENT_TYPE' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => "t=$timestamp,li=$signature"], $body)->assertUnauthorized();
        }
        $this->assertSame('unpaid', $invoice->fresh()->status); $this->assertDatabaseCount('payment_events', 0);
    }

    public function test_prices_reject_three_decimal_places_and_optional_po_fields_work(): void
    {
        $product = $this->product(); $this->actingAs($this->actor('admin'));
        $this->putJson('/admin/inventory/'.$product->id, $this->productPayload($product, ['wholesale_price' => '0.001']))->assertUnprocessable();
        $supplier = \App\Models\Supplier::create(['name' => 'QA decimal supplier']);
        $payload = ['supplier_id' => $supplier->id, 'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_cost' => '0.001']]];
        $this->postJson('/admin/purchase-orders', $payload)->assertUnprocessable();
        $payload['items'][0]['unit_cost'] = '0.29';
        $this->postJson('/admin/purchase-orders', $payload)->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', ['total_cost' => '0.29']);
    }

    public function test_stale_inventory_form_cannot_overwrite_changed_stock(): void
    {
        $product = $this->product(['stock_quantity' => 98, 'image_path' => null]);
        $this->actingAs($this->actor('warehouse'));
        $url = '/admin/inventory/'.$product->id;
        $this->putJson($url, $this->productPayload($product, ['original_stock_quantity' => 100, 'stock_quantity' => 101]))
            ->assertUnprocessable()->assertJsonValidationErrors('stock_quantity');
        $this->assertSame(98, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_movements', 0);
        // Multipart forms send numbers as strings; an unchanged snapshot must still save.
        $this->putJson($url, $this->productPayload($product, ['original_stock_quantity' => '98', 'stock_quantity' => '99']))->assertRedirect();
        $this->assertSame(99, $product->fresh()->stock_quantity);
        $this->assertNull($product->fresh()->getRawOriginal('image_path'));
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'quantity' => 1, 'user_id' => auth()->id()]);
    }
}

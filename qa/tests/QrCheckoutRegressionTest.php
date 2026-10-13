<?php
namespace JubisQA;

use App\Models\Shipment;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Group;

#[Group('baseline')]
class QrCheckoutRegressionTest extends IsolatedTestCase
{
    private function fakeTestLink(array $overrides = []): void
    {
        config(['services.paymongo.secret_key'=>'sk_test_QA']);
        Http::fake(['api.paymongo.com/*'=>Http::response(['data'=>[
            'id'=>'link_QA_QR', 'attributes'=>array_merge([
                'checkout_url'=>'https://pm.link/qa/test/checkout', 'livemode'=>false,
            ], $overrides),
        ]], 200)]);
    }

    public function test_qr_redirect_does_not_pay_until_verified_provider_notification(): void
    {
        $client=$this->actor(); $invoice=$this->invoice($client); $this->fakeTestLink();
        $this->actingAs($client)->post('/api/mock/payment/process/invoice/'.$invoice->id,
            ['payment_method'=>'qrph','expected_balance'=>224])->assertRedirect('https://pm.link/qa/test/checkout');
        $this->assertSame('unpaid',$invoice->fresh()->status);
        $this->assertEquals(0,$invoice->fresh()->amount_paid);
        $this->assertDatabaseCount('payment_events',0);
        $this->assertDatabaseHas('payment_links',['invoice_id'=>$invoice->id,'reference'=>'link_QA_QR','amount'=>224]);
        Http::assertSent(fn($request)=>$request['data']['attributes']['amount']===22400);
        config(['services.paymongo.webhook_secret'=>'qa-secret']);
        $body=json_encode(['data'=>['id'=>'evt_QA_QR','attributes'=>['type'=>'link.payment.paid','livemode'=>false,
            'data'=>['id'=>'link_QA_QR','attributes'=>['amount'=>22400,'currency'=>'PHP','status'=>'paid']]]]]);
        $timestamp=time(); $signature=hash_hmac('sha256',$timestamp.'.'.$body,'qa-secret');
        for($attempt=0;$attempt<2;$attempt++) {
            $this->call('POST','/webhooks/paymongo',[],[],[],['CONTENT_TYPE'=>'application/json',
                'HTTP_PAYMONGO_SIGNATURE'=>"t=$timestamp,te=$signature"],$body)->assertOk();
        }
        $this->assertSame('paid',$invoice->fresh()->status);
        $this->assertEquals(224,$invoice->fresh()->amount_paid);
        $this->assertDatabaseCount('payment_events',1);
    }

    public function test_qr_without_test_key_never_falls_back_to_a_fake_payment(): void
    {
        $client=$this->actor(); $invoice=$this->invoice($client); $this->actingAs($client);
        foreach(['','sk_live_QA'] as $key) {
            config(['services.paymongo.secret_key'=>$key]);
            $this->postJson('/api/mock/payment/process/invoice/'.$invoice->id,['payment_method'=>'qrph'])->assertUnprocessable();
        }
        $this->assertSame('unpaid',$invoice->fresh()->status);
        $this->assertDatabaseCount('payment_events',0); Http::assertNothingSent();
    }

    public function test_qr_rejects_live_response_and_untrusted_redirects(): void
    {
        $client=$this->actor(); $invoice=$this->invoice($client); $this->actingAs($client);
        foreach([['livemode'=>true],['checkout_url'=>'https://example.test/checkout']] as $response) {
            $this->fakeTestLink($response);
            $this->postJson('/api/mock/payment/process/invoice/'.$invoice->id,['payment_method'=>'qrph'])->assertUnprocessable();
        }
        $this->assertDatabaseCount('payment_events',0); $this->assertDatabaseCount('payment_links',0);
    }

    public function test_gateway_key_does_not_replace_the_demo_method_selector(): void
    {
        $client=$this->actor(); $invoice=$this->invoice($client);
        config(['services.paymongo.secret_key'=>'sk_test_QA']);
        $invoice->update(['payment_url'=>'https://pm.link/qa/test/previous']);
        $this->actingAs($client)->get('/invoices/'.$invoice->id.'/checkout')
            ->assertRedirect('/api/mock/payment/invoice/'.$invoice->id);
        $this->get('/api/mock/payment/invoice/'.$invoice->id)->assertOk()->assertSee('QR Ph (Demo)');
        Http::assertNothingSent();
    }

    public function test_paid_qr_retry_does_not_create_another_checkout(): void
    {
        $client=$this->actor(); $invoice=$this->invoice($client,2,'paid');
        $this->actingAs($client)->post('/api/mock/payment/process/invoice/'.$invoice->id,['payment_method'=>'qrph'])->assertRedirect('/dashboard');
        $this->assertEquals(0,$invoice->fresh()->balance_due); Http::assertNothingSent();
        $this->assertDatabaseCount('payment_links',0);
    }

    public function test_delivery_does_not_reopen_a_fully_paid_invoice(): void
    {
        $client=$this->actor(); $invoice=$this->invoice($client,2,'paid');
        $shipment=Shipment::create(['invoice_id'=>$invoice->id,'carrier'=>'QA manual courier',
            'tracking_number'=>'QA-DELIVERY','shipping_address'=>'QA','status'=>'processing']);
        $this->actingAs($this->actor('warehouse'));
        foreach(['picking_packing','dispatched','in_transit','delivered'] as $status) {
            $this->post('/admin/shipments/'.$shipment->id.'/status',['status'=>$status])->assertRedirect();
            $this->assertSame('paid',$invoice->fresh()->status);
            $this->assertEquals(0,$invoice->fresh()->balance_due);
        }
        $this->actingAs($client)->get('/dashboard')->assertOk()->assertInertia(fn(Assert $view)=>$view
            ->where('recentQuotes.0.invoice.balance_due','0.00')
            ->where('recentQuotes.0.invoice.shipments.0.status','delivered'));
    }

}

<?php
namespace JubisQA;
use App\Models\Shipment;
use App\Services\{LalamoveService, PayMongoService};
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Mail};
use PHPUnit\Framework\Attributes\Group;

#[Group('baseline')]
class IntegrationTest extends IsolatedTestCase
{
    private function gatewayKey(): void { config(['services.paymongo.secret_key' => 'sk_test_QA_only']); }
    protected function tearDown(): void { putenv('PAYMONGO_SECRET_KEY='); $_ENV['PAYMONGO_SECRET_KEY']=$_SERVER['PAYMONGO_SECRET_KEY']=''; parent::tearDown(); }

    public function test_paymongo_valid_response_maps_checkout_and_reference_and_centavos(): void
    {
        $this->gatewayKey(); $invoice=$this->invoice($this->actor());
        Http::fake(['api.paymongo.com/*'=>Http::response(['data'=>['id'=>'link_QA','attributes'=>['checkout_url'=>'https://example.test/checkout']]],200)]);
        $result=(new PayMongoService)->createPaymentLink($invoice);
        $this->assertTrue($result['success']); $this->assertSame('link_QA',$result['reference_id']);
        Http::assertSent(fn($r)=>$r['data']['attributes']['amount']===22400);
    }
    public function test_gateway_timeout_and_rejection_are_reported_as_failure(): void
    {
        $this->gatewayKey(); $invoice=$this->invoice($this->actor());
        Http::fake(fn()=>throw new ConnectionException('QA timeout'));
        $this->assertFalse((new PayMongoService)->createPaymentLink($invoice)['success']);
        Http::fake(['api.paymongo.com/*'=>Http::response(['error'=>'QA declined'],422)]);
        $this->assertFalse((new PayMongoService)->createPaymentLink($invoice)['success']);
    }
    public function test_logistics_demo_has_tracking_and_does_not_make_network_calls(): void
    {
        $shipment=new Shipment(['id'=>1]);
        $result=(new LalamoveService)->bookDelivery($shipment,'QA Pickup','QA Destination');
        $this->assertTrue($result['success']); $this->assertNotEmpty($result['tracking_number']);
        $this->assertStringContainsString('/api/mock/tracking/',$result['tracking_url']); Http::assertNothingSent();
    }
    public function test_contact_mail_has_reply_to_and_escaped_body(): void
    {
        $data=['first_name'=>'<script>QA</script>','last_name'=>'User','email'=>'qa@example.test','company'=>'QA','phone'=>'','message'=>'<img src=x onerror=alert(1)>'];
        $mail=new \App\Mail\ContactMessageMail($data);
        $this->assertSame('qa@example.test',$mail->envelope()->replyTo[0]->address);
        $html=$mail->render(); $this->assertStringNotContainsString('<img src=x onerror=',$html);
        $this->assertStringContainsString('&lt;img',$html);
    }
    public function test_valid_signed_payment_webhook_marks_matching_invoice_only(): void
    {
        $invoice=$this->invoice($this->actor()); $invoice->update(['payment_reference_id'=>'link_QA']);
        $other=$this->invoice($this->actor());
        $payload=['data'=>['id'=>'evt_QA', 'attributes'=>['type'=>'link.payment.paid','livemode'=>false,'data'=>['id'=>'link_QA', 'attributes'=>['amount'=>22400, 'currency'=>'PHP', 'status'=>'paid']]]]];
        $body=json_encode($payload); $ts=time(); putenv('PAYMONGO_WEBHOOK_SECRET=qa-secret'); $_ENV['PAYMONGO_WEBHOOK_SECRET']=$_SERVER['PAYMONGO_WEBHOOK_SECRET']='qa-secret';
        try {
            config(['services.paymongo.webhook_secret' => 'qa-secret']);
            $signature=hash_hmac('sha256',$ts.'.'.$body,'qa-secret');
            $this->call('POST','/webhooks/paymongo',[],[],[],['CONTENT_TYPE'=>'application/json','HTTP_PAYMONGO_SIGNATURE'=>"t=$ts,te=$signature"],$body)->assertOk();
            $this->assertSame('paid',$invoice->fresh()->status); $this->assertSame('unpaid',$other->fresh()->status);
        } finally { putenv('PAYMONGO_WEBHOOK_SECRET='); $_ENV['PAYMONGO_WEBHOOK_SECRET']=$_SERVER['PAYMONGO_WEBHOOK_SECRET']=''; }
    }
}

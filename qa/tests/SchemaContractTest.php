<?php
namespace JubisQA;
use App\Models\{Product, PurchaseOrder, PurchaseOrderItem, RMAItem, Supplier, User};
use Illuminate\Support\Facades\{Hash, Mail};
use PHPUnit\Framework\Attributes\{DataProvider, Group};

#[Group('baseline')]
class SchemaContractTest extends IsolatedTestCase
{
    public static function validationRoutes(): array
    {
        return [
            ['POST','/contact','guest','first_name'], ['POST','/register','guest','email'],
            ['POST','/login','guest','email'], ['POST','/forgot-password','guest','email'],
            ['POST','/reset-password','guest','token'], ['POST','/cart/add','client','quantity'],
            ['POST','/admin/inventory','warehouse','sku'], ['PUT','/admin/inventory/{product}','warehouse','name'],
            ['PATCH','/admin/inventory/{product}','warehouse','name'], ['POST','/admin/suppliers','purchasing','name'],
            ['PUT','/admin/suppliers/{supplier}','purchasing','name'], ['POST','/admin/purchase-orders','purchasing','items'],
            ['POST','/admin/purchase-orders/{po}/status','purchasing','status'], ['PUT','/admin/quotes/{quote}','sales','status'],
            ['POST','/admin/invoices/{invoice}/payment','finance','amount_paid'], ['POST','/admin/shipments','warehouse','invoice_id'],
            ['POST','/admin/users','admin','role'], ['PUT','/admin/users/{user}','admin','role'],
            ['PATCH','/profile','client','email'], ['PUT','/password','client','current_password'],
            ['POST','/confirm-password','client','password'], ['DELETE','/profile','client','password'],
        ];
    }
    #[DataProvider('validationRoutes')]
    public function test_missing_fields_return_422_without_resource_mutation(string $method,string $uri,string $role,string $field): void
    {
        $client=$this->actor(); $product=$this->product(); $invoice=$this->invoice($client);
        $supplier=Supplier::create(['name'=>'QA Supplier']); $po=PurchaseOrder::create(['supplier_id'=>$supplier->id,'total_cost'=>0,'status'=>'draft']);
        $uri=strtr($uri,['{product}'=>$product->id,'{supplier}'=>$supplier->id,'{po}'=>$po->id,'{quote}'=>$invoice->quote_id,'{invoice}'=>$invoice->id,'{user}'=>$client->id]);
        $before=['products'=>Product::count(),'users'=>User::count(),'purchase_orders'=>PurchaseOrder::count()];
        if($role!=='guest') $this->actingAs($this->actor($role));
        // Capture the actor too; validation must not create a second user.
        $before['users']=User::count();
        $this->json($method,$uri,[])->assertUnprocessable()->assertJsonValidationErrors($field);
        foreach($before as $table=>$count) $this->assertDatabaseCount($table,$count);
        $this->assertEquals(100,$product->fresh()->stock_quantity); $this->assertSame('unpaid',$invoice->fresh()->status);
        Mail::assertNothingSent();
    }
    public function test_malformed_json_is_a_validation_error_not_internal_error(): void
    {
        $this->call('POST','/contact',[],[],[],['CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json'],'{"first_name":')
            ->assertUnprocessable()->assertJsonValidationErrors('first_name');
        Mail::assertNothingSent();
    }
    public function test_supplier_and_user_crud_contracts(): void
    {
        $admin=$this->actor('admin'); $this->actingAs($admin);
        $this->post('/admin/suppliers',['name'=>'QA Vendor','email'=>'qa@example.test'])->assertRedirect();
        $supplier=Supplier::firstOrFail();
        $this->put('/admin/suppliers/'.$supplier->id,['name'=>'QA Renamed Vendor','email'=>'qa@example.test'])->assertRedirect();
        $this->assertDatabaseHas('suppliers',['id'=>$supplier->id,'name'=>'QA Renamed Vendor']);
        $this->post('/admin/users',['name'=>'QA Staff','email'=>'staff@example.test','password'=>'QA-long-password','role'=>'warehouse'])->assertRedirect();
        $staff=User::where('email','staff@example.test')->firstOrFail(); $this->assertTrue(Hash::check('QA-long-password',$staff->password));
        $this->patch('/admin/users/'.$staff->id,['name'=>'QA Staff','email'=>'staff@example.test','role'=>'purchasing'])->assertRedirect();
        $this->assertSame('purchasing',$staff->fresh()->role);
        $this->delete('/admin/users/'.$staff->id)->assertRedirect(); $this->assertDatabaseMissing('users',['id'=>$staff->id]);
        $this->delete('/admin/suppliers/'.$supplier->id)->assertRedirect(); $this->assertSoftDeleted('suppliers',['id'=>$supplier->id]);
    }
    public function test_profile_updates_cannot_escalate_role_or_credit(): void
    {
        $user=$this->actor(); $user->update(['credit_status'=>'pending']);
        $this->actingAs($user)->patch('/profile',['name'=>'QA Updated','email'=>'new@example.test','role'=>'super_admin','credit_status'=>'approved'])->assertRedirect('/profile');
        $this->assertSame('client',$user->fresh()->role); $this->assertSame('pending',$user->fresh()->credit_status);
        $this->assertNull($user->fresh()->email_verified_at);
    }
    public function test_po_insert_failure_rolls_back_header_and_all_items(): void
    {
        $supplier=Supplier::create(['name'=>'QA']); $a=$this->product(); $b=$this->product(); $inserts=0;
        PurchaseOrderItem::creating(function() use(&$inserts) { if(++$inserts===2) throw new \RuntimeException('QA injected second-row failure'); });
        try {
            $this->actingAs($this->actor('purchasing'))->post('/admin/purchase-orders',['supplier_id'=>$supplier->id,'notes'=>'QA','expected_delivery_date'=>null,'items'=>[
                ['product_id'=>$a->id,'quantity'=>1,'unit_cost'=>1],['product_id'=>$b->id,'quantity'=>1,'unit_cost'=>1]]])->assertStatus(500);
            $this->assertDatabaseCount('purchase_orders',0); $this->assertDatabaseCount('purchase_order_items',0);
        } finally { PurchaseOrderItem::flushEventListeners(); }
    }
    public function test_low_stock_variant_threshold_10_sends_one_notification(): void
    {
        $parent=$this->product(['stock_quantity'=>0]); $this->product(['parent_id'=>$parent->id,'stock_quantity'=>10]);
        $this->product(['parent_id'=>$parent->id,'stock_quantity'=>11]);
        $this->artisan('inventory:check-stock')->assertExitCode(0); Mail::assertSent(\App\Mail\LowStockAlertMail::class,1);
    }
}

<?php
namespace JubisQA;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Mail, RateLimiter};
use PHPUnit\Framework\Attributes\{DataProvider, Group};
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Group('baseline')]
class AccessAndValidationTest extends IsolatedTestCase
{
    public static function accessCases(): array
    {
        $rows = json_decode(file_get_contents(__DIR__.'/../data/routes.json'), true);
        $cases=[];
        foreach ($rows as $row) foreach ($row['middleware'] as $middleware) {
            if (!str_starts_with($middleware,'role:')) continue;
            $roles=explode(',',substr($middleware,5));
            foreach (['super_admin','admin','sales','finance','purchasing','warehouse','client'] as $role)
                $cases[$row['uri'].'|'.$row['methods'][0].'|'.$role]=[$role,$roles,in_array($role,['super_admin','admin']) || in_array($role,$roles)];
        }
        return $cases;
    }

    #[DataProvider('accessCases')]
    public function test_every_role_boundary_in_registered_admin_routes(string $role,array $allowed,bool $expected): void
    {
        $this->actingAs($this->actor($role));
        $request=Request::create('/qa-rbac');
        if (!$expected) { $this->expectException(HttpException::class); $this->expectExceptionCode(0); }
        try {
            $response=(new CheckRole)->handle($request,fn()=>response('allowed',200),...$allowed);
            $this->assertSame(200,$response->getStatusCode());
        } catch (HttpException $e) { $this->assertSame(403,$e->getStatusCode()); throw $e; }
    }

    #[DataProvider('adminLists')]
    public function test_real_http_guest_and_client_boundaries(string $uri): void
    {
        $this->getJson($uri)->assertUnauthorized();
        $this->actingAs($this->actor())->getJson($uri)->assertForbidden();
    }
    public static function adminLists(): array { return array_map(fn($p)=>[$p],['/admin/dashboard','/admin/inventory','/admin/inventory-export','/admin/quotes','/admin/invoices','/admin/purchase-orders','/admin/suppliers','/admin/shipments','/admin/users','/admin/settings','/admin/rmas','/admin/reports']); }

    public function test_registration_ignores_role_escalation_and_duplicate_email(): void
    {
        $payload=['name'=>'QA User','email'=>'qa@example.test','password'=>'QaPassword123!','password_confirmation'=>'QaPassword123!','company_name'=>'QA Company','phone'=>'09123456789','shipping_address'=>'QA','role'=>'super_admin','credit_status'=>'approved'];
        $this->post('/register',$payload)->assertRedirect();
        $this->assertDatabaseHas('users',['email'=>'qa@example.test','role'=>'client','credit_status'=>'pending']);
        $this->post('/logout')->assertRedirect();
        $this->postJson('/register',$payload)->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_contact_missing_fields_has_422_and_sends_no_email(): void
    {
        $this->postJson('/contact',[])->assertUnprocessable()->assertJsonValidationErrors(['first_name','last_name','email','message']);
        Mail::assertNothingSent();
    }

    public function test_login_rate_limit_blocks_sixth_attempt(): void
    {
        $email='rate-limit@example.test'; $key=$email.'|127.0.0.1'; RateLimiter::clear($key);
        for($i=0;$i<5;$i++) $this->postJson('/login',['email'=>$email,'password'=>'wrong'])->assertUnprocessable();
        $this->postJson('/login',['email'=>$email,'password'=>'wrong'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertTrue(RateLimiter::tooManyAttempts($key,5));
    }

    public function test_logout_ends_authenticated_access(): void
    {
        $this->actingAs($this->actor())->post('/logout')->assertRedirect('/');
        $this->assertGuest(); $this->getJson('/cart')->assertUnauthorized();
    }

    public function test_csrf_is_enforced_when_test_bypass_is_disabled(): void
    {
        // Laravel normally bypasses CSRF in unit tests. Change only the app environment for this request.
        $this->app['env']='qa-csrf';
        $this->post('/contact',['first_name'=>'QA'])->assertStatus(419); Mail::assertNothingSent();
        $this->app['env']='testing';
    }
}

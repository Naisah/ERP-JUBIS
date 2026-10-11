<?php
namespace JubisQA;
use App\Models\User;
use Illuminate\Support\Facades\{Hash, Notification, Password};
use PHPUnit\Framework\Attributes\Group;

#[Group('baseline')]
class SessionAndTokenTest extends IsolatedTestCase
{
    public function test_reset_token_changes_password_once_and_cannot_be_replayed(): void
    {
        $user=$this->actor(); $token=Password::broker()->createToken($user);
        $payload=['email'=>$user->email,'token'=>$token,'password'=>'QA-new-password123!','password_confirmation'=>'QA-new-password123!'];
        $this->post('/reset-password',$payload)->assertRedirect('/login');
        $this->assertTrue(Hash::check($payload['password'],$user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens',['email'=>$user->email]);
        $hash=$user->fresh()->password;
        $this->postJson('/reset-password',array_merge($payload,['password'=>'QA-different-password123!','password_confirmation'=>'QA-different-password123!']))
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertSame($hash,$user->fresh()->password);
    }
    public function test_expired_and_foreign_reset_tokens_cannot_change_password(): void
    {
        $user=$this->actor(); $other=$this->actor(); $token=Password::broker()->createToken($user); $hash=$user->password;
        $data=['token'=>$token,'password'=>'QA-new-password123!','password_confirmation'=>'QA-new-password123!'];
        $this->postJson('/reset-password',array_merge($data,['email'=>$other->email]))->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->travel((int)config('auth.passwords.users.expire',60)+1)->minutes();
        $this->postJson('/reset-password',array_merge($data,['email'=>$user->email]))->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertSame($hash,$user->fresh()->password);
    }
    public function test_password_change_requires_current_password_and_confirmation(): void
    {
        $user=$this->actor(); $this->actingAs($user); $before=$user->password;
        $this->putJson('/password',['current_password'=>'wrong','password'=>'QA-new-password123!','password_confirmation'=>'QA-new-password123!'])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->assertSame($before,$user->fresh()->password);
        $this->put('/password',['current_password'=>'password','password'=>'QA-new-password123!','password_confirmation'=>'QA-new-password123!'])->assertRedirect();
        $this->assertTrue(Hash::check('QA-new-password123!',$user->fresh()->password));
    }
    public function test_verification_notification_route_returns_429_after_six_requests(): void
    {
        $user=$this->actor(); $user->forceFill(['email_verified_at'=>null])->save(); $this->actingAs($user);
        for($i=0;$i<6;$i++) $this->post('/email/verification-notification')->assertRedirect();
        $this->postJson('/email/verification-notification')->assertTooManyRequests();
        Notification::assertSentToTimes($user,\Illuminate\Auth\Notifications\VerifyEmail::class,6);
    }
}

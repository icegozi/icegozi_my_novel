<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_login_and_authenticate(): void
    {
        $user = User::factory()->create([
            'email' => 'reader@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->get('/login')->assertStatus(200)->assertSee('Đăng Nhập');

        $response = $this->post('/login', [
            'email' => 'reader@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_register_with_validation(): void
    {
        $this->get('/register')->assertStatus(200)->assertSee('Tạo Tài Khoản');

        $response = $this->post('/register', [
            'name' => 'Nguyễn Độc Giả',
            'email' => 'newreader@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);
        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['email' => 'newreader@example.com']);
    }

    public function test_forgot_and_reset_password_views_render(): void
    {
        $this->get('/forgot-password')->assertStatus(200)->assertSee('Khôi Phục Mật Khẩu');
        $this->get('/reset-password/sample-token')->assertStatus(200)->assertSee('Đặt Lại Mật Khẩu');
    }

    public function test_reset_password_rejects_invalid_token_and_accepts_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'victim@example.com',
            'password' => bcrypt('oldpassword123'),
        ]);

        // Attempt reset with invalid/fake token
        $response = $this->post('/reset-password', [
            'token' => 'invalid-token-123',
            'email' => 'victim@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('oldpassword123', $user->fresh()->password));

        // Insert valid token record
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
            'email' => 'victim@example.com',
            'token' => \Illuminate\Support\Facades\Hash::make('secret-valid-token'),
            'created_at' => now(),
        ]);

        // Reset with valid token
        $validResponse = $this->post('/reset-password', [
            'token' => 'secret-valid-token',
            'email' => 'victim@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $validResponse->assertRedirect('/login');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPassword123!', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'victim@example.com']);
    }
}

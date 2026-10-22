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
}

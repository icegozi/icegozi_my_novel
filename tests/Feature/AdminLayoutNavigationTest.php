<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayoutNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
    }

    public function test_admin_layout_renders_sidebar_and_navigation_links(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/kiem-duyet');
        $response->assertStatus(200);

        // Sidebar brand and navigation sections
        $response->assertSee('NOVEL ADMIN', false);
        $response->assertSee('/admin/dashboard', false);
        $response->assertSee('Bảng Điều Khiển', false);
        $response->assertSee('/admin/tac-pham', false);
        $response->assertSee('Quản Lý Tác Phẩm', false);
        $response->assertSee('/admin/tac-gia', false);
        $response->assertSee('Quản Lý Tác Giả', false);
        $response->assertSee('/admin/kiem-duyet', false);
        $response->assertSee('Hàng Đợi Kiểm Duyệt', false);
        $response->assertSee('/admin/ban-quyen', false);
        $response->assertSee('Bản Quyền &amp; DMCA', false);
        $response->assertSee('/admin/dinh-danh-tac-gia', false);
        $response->assertSee('Định Danh e-KYC', false);
        $response->assertSee('/admin/du-lieu', false);
        $response->assertSee('Nhập Xuất Dữ Liệu', false);
        $response->assertSee('/admin/nhat-ky-an-ninh', false);
        $response->assertSee('Nhật Ký An Ninh', false);

        // Accessibility & Responsive Drawer elements
        $response->assertSee('aria-label="Menu điều hướng quản trị"', false);
        $response->assertSee('aria-label="Mở menu quản trị"', false);
        $response->assertDontSee('transition-all');
    }
}

<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;
    protected User $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->reader = User::factory()->create(['role' => 'reader', 'status' => 'active']);

        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        AuthorProfile::create([
            'user_id' => $author->id,
            'pen_name' => 'Tác Giả Mẫu',
            'legal_full_name' => 'Nguyễn Văn Mẫu',
            'identity_number_encrypted' => 'enc',
            'identity_hash' => hash('sha256', '012345678999'),
            'verification_status' => 'verified',
        ]);

        $pendingAuthor = User::factory()->create(['role' => 'reader', 'status' => 'active']);
        AuthorProfile::create([
            'user_id' => $pendingAuthor->id,
            'pen_name' => 'Tác Giả Chờ Duyệt',
            'legal_full_name' => 'Lê Văn Chờ',
            'identity_number_encrypted' => 'enc2',
            'identity_hash' => hash('sha256', '012345678888'),
            'verification_status' => 'pending',
        ]);

        Novel::create([
            'author_id' => $author->id,
            'title' => 'Tác Phẩm 1',
            'slug' => 'tac-pham-1',
            'synopsis' => 'Mô tả',
            'status' => 'published',
        ]);

        Novel::create([
            'author_id' => $author->id,
            'title' => 'Tác Phẩm 2 Chờ Duyệt',
            'slug' => 'tac-pham-2-cho-duyet',
            'synopsis' => 'Mô tả',
            'status' => 'pending_approval',
        ]);

        Report::create([
            'reporter_email' => 'victim@example.com',
            'reportable_type' => 'Novel',
            'reportable_id' => 1,
            'violation_category' => 'copyright_infringement',
            'description' => 'Khiếu nại bản quyền',
            'status' => 'received',
        ]);

        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'system_boot',
            'target_type' => 'System',
            'target_id' => 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_reader_cannot_access_dashboard(): void
    {
        $response = $this->actingAs($this->reader)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_and_moderator_can_access_dashboard_with_kpi_metrics(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        // KPI metrics
        $response->assertSee('Tổng Tác Phẩm', false);
        $response->assertSee('Chờ Duyệt Xuất Bản', false);
        $response->assertSee('Tác Giả Định Danh', false);
        $response->assertSee('Hồ Sơ e-KYC Chờ', false);
        $response->assertSee('Báo Cáo Vi Phạm', false);

        // Recent activity feed
        $response->assertSee('system_boot', false);

        // Moderator can also view dashboard
        $modResponse = $this->actingAs($this->moderator)->get('/admin/dashboard');
        $modResponse->assertStatus(200);
    }

    public function test_admin_root_redirects_to_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertRedirect('/admin/dashboard');
    }
}

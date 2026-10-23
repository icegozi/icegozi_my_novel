<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CopyrightNoticeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $author;
    protected User $unrelatedUser;
    protected Novel $novel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->author = User::factory()->create(['role' => 'author']);
        $this->unrelatedUser = User::factory()->create(['role' => 'reader']);

        AuthorProfile::create([
            'user_id' => $this->author->id,
            'pen_name' => 'Tác Giả Bản Quyền',
            'legal_full_name' => 'Trần Văn K',
            'identity_number_encrypted' => 'encK',
            'identity_hash' => 'hash_k',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $genre = Genre::create(['name' => 'Kiếm Hiệp', 'slug' => 'kiem-hiep']);

        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Thiên Long Tranh Hùng',
            'slug' => 'thien-long-tranh-hung',
            'synopsis' => 'Tác phẩm kiếm hiệp đặc sắc...',
            'copyright_type' => 'original',
            'age_rating' => '16_plus',
            'status' => 'published',
            'approved_at' => now(),
        ]);
        $this->novel->genres()->attach($genre);
    }

    public function test_public_user_can_submit_copyright_infringement_report(): void
    {
        $response = $this->post(route('legal.reports.store'), [
            'reportable_type' => 'Novel',
            'reportable_id' => $this->novel->id,
            'reporter_email' => 'legal-team@author-rep.vn',
            'violation_category' => 'copyright_infringement',
            'description' => 'Tác phẩm này sao chép cốt truyện và nhân vật chính từ tác phẩm đã đăng ký quyền tác giả.',
            'evidence_urls' => ['https://evidence.vn/chung-cu-1.pdf'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reports', [
            'reportable_type' => 'Novel',
            'reportable_id' => $this->novel->id,
            'reporter_email' => 'legal-team@author-rep.vn',
            'violation_category' => 'copyright_infringement',
            'status' => 'received',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'report_submitted',
            'target_type' => 'Novel',
            'target_id' => $this->novel->id,
        ]);
    }

    public function test_admin_can_view_reports_and_inspect_details(): void
    {
        $report = Report::create([
            'reporter_email' => 'complaint@rights.vn',
            'reportable_type' => 'Novel',
            'reportable_id' => $this->novel->id,
            'violation_category' => 'copyright_infringement',
            'description' => 'Khiếu nại sao chép tác phẩm.',
            'evidence_urls' => ['https://rights.vn/doc.pdf'],
            'status' => 'received',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.copyright.index'));
        $response->assertOk()->assertSee('complaint@rights.vn');

        $detailResponse = $this->actingAs($this->admin)->get(route('admin.copyright.show', $report));
        $detailResponse->assertOk()->assertSee('Thiên Long Tranh Hùng');
    }

    public function test_admin_can_issue_temporary_takedown(): void
    {
        $report = Report::create([
            'reporter_email' => 'complaint@rights.vn',
            'reportable_type' => 'Novel',
            'reportable_id' => $this->novel->id,
            'violation_category' => 'copyright_infringement',
            'description' => 'Đơn khiếu nại kèm giấy chứng nhận quyền tác giả.',
            'status' => 'received',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.copyright.takedown', $report), [
            'resolution_notes' => 'Tạm ẩn tác phẩm trong 10 ngày làm việc theo Nghị định 17/2023/NĐ-CP để chờ giải trình.',
        ]);

        $response->assertRedirect(route('admin.copyright.show', $report));

        $report->refresh();
        $this->assertEquals('temporary_takedown', $report->status);

        $this->novel->refresh();
        $this->assertEquals('takedown', $this->novel->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'copyright_temporary_takedown',
            'target_type' => 'Report',
            'target_id' => $report->id,
        ]);
    }

    public function test_author_can_submit_counter_notice_within_nd17_framework(): void
    {
        $report = Report::create([
            'reporter_email' => 'complaint@rights.vn',
            'reportable_type' => 'Novel',
            'reportable_id' => $this->novel->id,
            'violation_category' => 'copyright_infringement',
            'description' => 'Khiếu nại bản quyền.',
            'status' => 'temporary_takedown',
        ]);

        $response = $this->actingAs($this->author)->get(route('author.counter-notice.create', $report));
        $response->assertOk()->assertSee('Thông Báo Phản Đối');

        $postResponse = $this->actingAs($this->author)->post(route('author.counter-notice.store', $report), [
            'counter_notice_text' => 'Tôi cam đoan đây là tác phẩm gốc tự sáng tác, đính kèm chứng nhận đăng ký quyền tác giả số 5678.',
            'counter_notice_evidence_urls' => ['https://author.vn/chung-nhan.pdf'],
        ]);

        $postResponse->assertRedirect();

        $report->refresh();
        $this->assertEquals('under_review', $report->status);
        $this->assertNotNull($report->counter_submitted_at);
        $this->assertStringContainsString('5678', $report->counter_notice_text);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'counter_notice_submitted',
            'target_type' => 'Report',
            'target_id' => $report->id,
        ]);
    }

    public function test_unrelated_user_cannot_submit_counter_notice(): void
    {
        $report = Report::create([
            'reporter_email' => 'complaint@rights.vn',
            'reportable_type' => 'Novel',
            'reportable_id' => $this->novel->id,
            'violation_category' => 'copyright_infringement',
            'description' => 'Khiếu nại bản quyền.',
            'status' => 'temporary_takedown',
        ]);

        $response = $this->actingAs($this->unrelatedUser)
            ->get(route('author.counter-notice.create', $report));
        $response->assertForbidden();

        $postResponse = $this->actingAs($this->unrelatedUser)
            ->post(route('author.counter-notice.store', $report), [
                'counter_notice_text' => 'Hacker attempt',
            ]);
        $postResponse->assertForbidden();
    }

    public function test_admin_can_restore_novel_after_reviewing_counter_notice(): void
    {
        $this->novel->update(['status' => 'takedown']);

        $report = Report::create([
            'reporter_email' => 'complaint@rights.vn',
            'reportable_type' => 'Novel',
            'reportable_id' => $this->novel->id,
            'violation_category' => 'copyright_infringement',
            'description' => 'Khiếu nại bản quyền.',
            'status' => 'under_review',
            'counter_notice_text' => 'Bằng chứng hợp lệ.',
            'counter_submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.copyright.restore', $report), [
            'resolution_notes' => 'Bên khiếu nại không xuất trình thụ lý tòa án sau 10 ngày làm việc. Phục hồi tác phẩm theo NĐ 17/2023.',
        ]);

        $response->assertRedirect(route('admin.copyright.show', $report));

        $report->refresh();
        $this->assertEquals('resolved', $report->status);

        $this->novel->refresh();
        $this->assertEquals('published', $this->novel->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'copyright_work_restored',
            'target_type' => 'Report',
            'target_id' => $report->id,
        ]);
    }

    public function test_admin_can_dismiss_frivolous_report(): void
    {
        $report = Report::create([
            'reporter_email' => 'spammer@fake.com',
            'reportable_type' => 'Novel',
            'reportable_id' => $this->novel->id,
            'violation_category' => 'copyright_infringement',
            'description' => 'Khiếu nại vu vơ không có chứng cứ.',
            'status' => 'received',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.copyright.dismiss', $report), [
            'resolution_notes' => 'Bác đơn do không cung cấp tài liệu chứng minh quyền sở hữu theo quy định.',
        ]);

        $response->assertRedirect(route('admin.copyright.show', $report));

        $report->refresh();
        $this->assertEquals('dismissed', $report->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'copyright_report_dismissed',
            'target_type' => 'Report',
            'target_id' => $report->id,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ContentFilter;
use App\Models\Novel;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegalComplianceSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_tables_have_expected_columns_and_indices(): void
    {
        $this->assertTrue(Schema::hasTable('reports'));
        $this->assertTrue(Schema::hasColumns('reports', [
            'id', 'reporter_id', 'reporter_email', 'reportable_type', 'reportable_id',
            'violation_category', 'evidence_urls', 'description', 'status',
            'resolution_notes', 'resolved_by', 'counter_notice_text',
            'counter_notice_evidence_urls', 'counter_submitted_at',
            'created_at', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('audit_logs'));
        $this->assertTrue(Schema::hasColumns('audit_logs', [
            'id', 'user_id', 'action', 'target_type', 'target_id',
            'old_values', 'new_values', 'ip_address', 'user_agent', 'created_at',
        ]));

        $this->assertTrue(Schema::hasTable('content_filters'));
        $this->assertTrue(Schema::hasColumns('content_filters', [
            'id', 'pattern', 'category', 'severity', 'created_at', 'updated_at',
        ]));
    }

    public function test_report_and_audit_log_polymorphic_relationships(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'author']);

        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Tác phẩm thử nghiệm',
            'slug' => 'tac-pham-thu-nghiem',
            'synopsis' => 'Nội dung tóm tắt',
            'status' => 'published',
        ]);

        $report = Report::create([
            'reporter_email' => 'tố-cáo@dmca.vn',
            'reportable_type' => Novel::class,
            'reportable_id' => $novel->id,
            'violation_category' => 'copyright_infringement',
            'evidence_urls' => ['https://evidence.vn/doc.pdf'],
            'description' => 'Tác phẩm sao chép nguyên văn chương 1',
            'status' => 'under_review',
            'counter_notice_text' => 'Tôi có hợp đồng ủy quyền',
            'counter_notice_evidence_urls' => ['https://author.vn/contract.pdf'],
            'counter_submitted_at' => now(),
        ]);

        $auditLog = AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'review_report',
            'target_type' => Report::class,
            'target_id' => $report->id,
            'old_values' => ['status' => 'received'],
            'new_values' => ['status' => 'under_review'],
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 AdminConsole',
        ]);

        $filter = ContentFilter::create([
            'pattern' => 'tu_khoa_cam_123',
            'category' => 'vulgarity',
            'severity' => 'flag_for_review',
        ]);

        $this->assertInstanceOf(Novel::class, $report->reportable);
        $this->assertEquals($novel->id, $report->reportable->id);
        $this->assertEquals(['https://evidence.vn/doc.pdf'], $report->evidence_urls);
        $this->assertEquals(['https://author.vn/contract.pdf'], $report->counter_notice_evidence_urls);

        $this->assertInstanceOf(Report::class, $auditLog->target);
        $this->assertEquals($report->id, $auditLog->target->id);
        $this->assertEquals($admin->id, $auditLog->user->id);
        $this->assertEquals('flag_for_review', $filter->severity);
    }
}

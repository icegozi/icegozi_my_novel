<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogExplorerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->reader = User::factory()->create(['role' => 'reader']);

        // Create varied audit logs for testing
        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'chapter_approved',
            'target_type' => 'Chapter',
            'target_id' => 10,
            'old_values' => ['status' => 'pending_approval'],
            'new_values' => ['status' => 'published'],
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 Admin',
            'created_at' => now()->subHours(2),
        ]);

        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'copyright_temporary_takedown',
            'target_type' => 'Report',
            'target_id' => 5,
            'old_values' => ['status' => 'received'],
            'new_values' => ['status' => 'temporary_takedown'],
            'ip_address' => '10.0.0.50',
            'user_agent' => 'Admin Bot/1.0',
            'created_at' => now()->subDay(),
        ]);
    }

    public function test_admin_can_access_audit_log_explorer(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

        $response->assertOk()
            ->assertSee('chapter_approved')
            ->assertSee('copyright_temporary_takedown')
            ->assertSee('192.168.1.100');
    }

    public function test_reader_cannot_access_audit_log_explorer(): void
    {
        $response = $this->actingAs($this->reader)->get(route('admin.audit-logs.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_filter_logs_by_ip_address(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index', [
            'ip_address' => '192.168.1.100',
        ]));

        $response->assertOk()
            ->assertSee('192.168.1.100')
            ->assertDontSee('10.0.0.50');
    }

    public function test_admin_can_filter_logs_by_action(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index', [
            'action' => 'copyright_temporary_takedown',
        ]));

        $response->assertOk()
            ->assertSee('copyright_temporary_takedown')
            ->assertDontSee('192.168.1.100');
    }

    public function test_admin_can_export_audit_logs_for_cybersecurity_compliance(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.export', [
            'format' => 'json',
        ]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonFragment([
                'action' => 'chapter_approved',
                'ip_address' => '192.168.1.100',
            ]);
    }
}

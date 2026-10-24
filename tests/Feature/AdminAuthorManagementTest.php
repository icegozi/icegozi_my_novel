<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;
    protected User $authorUser;
    protected AuthorProfile $authorProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);

        $this->authorUser = User::factory()->create([
            'name' => 'Nguyễn Nhật Ánh Giả Lập',
            'email' => 'tacgia.anh@example.com',
            'role' => 'author',
            'status' => 'active',
        ]);

        $this->authorProfile = AuthorProfile::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'Hắc Miêu Tiên Sinh',
            'legal_full_name' => 'Nguyễn Nhật Ánh',
            'identity_number_encrypted' => 'enc123',
            'identity_hash' => hash('sha256', '012345678000'),
            'verification_status' => 'verified',
            'tax_code' => '0312345678',
            'bank_name' => 'Vietcombank',
            'bank_account_number' => '999988887777',
            'verified_at' => now(),
        ]);

        Novel::create([
            'author_id' => $this->authorUser->id,
            'title' => 'Truyện Của Hắc Miêu',
            'slug' => 'truyen-cua-hac-mieu',
            'synopsis' => 'Một tác phẩm kỳ ảo',
            'status' => 'published',
        ]);
    }

    public function test_admin_and_moderator_can_list_and_search_authors(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/tac-gia?search=Hắc+Miêu');
        $response->assertStatus(200);
        $response->assertSee('Hắc Miêu Tiên Sinh', false);
        $response->assertSee('tacgia.anh@example.com', false);
        $response->assertSee('verified', false);

        // Moderator can also list authors
        $modResponse = $this->actingAs($this->moderator)->get('/admin/tac-gia');
        $modResponse->assertStatus(200);
        $modResponse->assertSee('Hắc Miêu Tiên Sinh', false);
    }

    public function test_admin_and_moderator_can_view_author_dossier(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/tac-gia/{$this->authorProfile->id}");
        $response->assertStatus(200);
        $response->assertSee('Hắc Miêu Tiên Sinh', false);
        $response->assertSee('Truyện Của Hắc Miêu', false);
        $response->assertSee('Vietcombank', false);
    }

    public function test_only_admin_can_update_author_profile(): void
    {
        // Moderator forbidden
        $modResponse = $this->actingAs($this->moderator)->put("/admin/tac-gia/{$this->authorProfile->id}", [
            'pen_name' => 'Bạch Miêu Tiên Sinh',
            'tax_code' => '9999999999',
        ]);
        $modResponse->assertStatus(403);

        // Admin can update
        $adminResponse = $this->actingAs($this->admin)->put("/admin/tac-gia/{$this->authorProfile->id}", [
            'pen_name' => 'Bạch Miêu Tiên Sinh',
            'tax_code' => '0399999999',
            'bank_name' => 'Techcombank',
            'bank_account_number' => '123456789',
        ]);
        $adminResponse->assertRedirect("/admin/tac-gia/{$this->authorProfile->id}");

        $this->assertDatabaseHas('author_profiles', [
            'id' => $this->authorProfile->id,
            'pen_name' => 'Bạch Miêu Tiên Sinh',
            'bank_name' => 'Techcombank',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'author_profile_updated',
            'target_type' => 'AuthorProfile',
            'target_id' => $this->authorProfile->id,
        ]);
    }

    public function test_only_admin_can_suspend_or_ban_author(): void
    {
        // Moderator cannot change status
        $modResponse = $this->actingAs($this->moderator)->post("/admin/tac-gia/{$this->authorProfile->id}/trang-thai", [
            'status' => 'suspended',
            'reason' => 'Tạm đình chỉ vi phạm quy chế',
        ]);
        $modResponse->assertStatus(403);

        // Admin can suspend author
        $adminResponse = $this->actingAs($this->admin)->post("/admin/tac-gia/{$this->authorProfile->id}/trang-thai", [
            'status' => 'suspended',
            'reason' => 'Tạm đình chỉ vi phạm quy chế',
        ]);
        $adminResponse->assertRedirect("/admin/tac-gia/{$this->authorProfile->id}");

        $this->assertDatabaseHas('users', [
            'id' => $this->authorUser->id,
            'status' => 'suspended',
        ]);

        // Existing published novels remain in DB intact
        $this->assertDatabaseHas('novels', [
            'author_id' => $this->authorUser->id,
            'title' => 'Truyện Của Hắc Miêu',
            'status' => 'published',
        ]);

        // Audit log created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'author_status_changed',
            'target_type' => 'User',
            'target_id' => $this->authorUser->id,
        ]);
    }
}

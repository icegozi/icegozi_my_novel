<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\ExportJob;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;
    protected User $reader;
    protected User $authorUser;
    protected AuthorProfile $authorProfile;
    protected Novel $novel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->reader = User::factory()->create(['role' => 'reader', 'status' => 'active']);

        $this->authorUser = User::factory()->create(['role' => 'author', 'status' => 'active', 'email' => 'tacgia.test@example.com']);
        $this->authorProfile = AuthorProfile::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => '=HYPERLINK("http://evil.com")',
            'legal_full_name' => 'Nguyễn Văn Độc',
            'identity_number_encrypted' => 'SECRET_ENCRYPTED_CCCD',
            'identity_hash' => hash('sha256', '012345678555'),
            'verification_status' => 'verified',
            'tax_code' => '0311223344',
            'bank_name' => 'Techcombank',
            'bank_account_number' => '190012345678',
            'verified_at' => now(),
        ]);

        $this->novel = Novel::create([
            'author_id' => $this->authorUser->id,
            'title' => '+cmd|/C calc!A0', // Potential CSV injection title
            'slug' => 'cmd-calc',
            'synopsis' => 'Một tác phẩm kiểm thử formula injection.',
            'status' => 'published',
            'total_words' => 15000,
            'total_chapters' => 10,
            'view_count' => 9999,
        ]);
    }

    public function test_reader_is_forbidden_from_export_desk(): void
    {
        $this->actingAs($this->reader)->get('/admin/du-lieu/xuat-du-lieu')->assertStatus(403);
        $this->actingAs($this->reader)->post('/admin/du-lieu/xuat-du-lieu', ['type' => 'novels'])->assertStatus(403);
    }

    public function test_moderator_and_admin_can_access_export_desk(): void
    {
        $response = $this->actingAs($this->moderator)->get('/admin/du-lieu/xuat-du-lieu');
        $response->assertStatus(200);
        $response->assertSee('Xuất dữ liệu', false);
        $response->assertSee('Formula Injection', false);
    }

    public function test_streaming_export_novels_sanitizes_dangerous_formulas(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/du-lieu/xuat-du-lieu', [
            'type' => 'novels',
            'status' => 'published',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        // Must start with UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Dangerous formula must be sanitized with prefix quote '
        $this->assertStringContainsString("'+cmd|/C calc!A0", $content);

        // Job recorded in database
        $this->assertDatabaseHas('export_jobs', [
            'user_id' => $this->admin->id,
            'type' => 'novels',
        ]);
    }

    public function test_streaming_export_authors_masks_sensitive_data(): void
    {
        $response = $this->actingAs($this->moderator)->post('/admin/du-lieu/xuat-du-lieu', [
            'type' => 'authors',
        ]);

        $response->assertStatus(200);
        $content = $response->streamedContent();

        // Must neutralize formula in pen name (with standard CSV quote escaping)
        $this->assertStringContainsString("'=HYPERLINK(\"\"http://evil.com\"\")", $content);

        // Sensitive ID number MUST NEVER appear in CSV export
        $this->assertStringNotContainsString('SECRET_ENCRYPTED_CCCD', $content);
        $this->assertStringNotContainsString(hash('sha256', '012345678555'), $content);
    }
}

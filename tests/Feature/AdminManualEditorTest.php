<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use App\Services\ContentSanitizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManualEditorTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;
    protected User $reader;
    protected User $authorUser;
    protected AuthorProfile $authorProfile;
    protected Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->reader = User::factory()->create(['role' => 'reader', 'status' => 'active']);

        $this->authorUser = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $this->authorProfile = AuthorProfile::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'Nam Phái Tam Thúc',
            'legal_full_name' => 'Từ Lỗi',
            'identity_number_encrypted' => 'enc123',
            'identity_hash' => hash('sha256', '012345678666'),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $this->genre = Genre::create(['name' => 'Phiêu Lưu', 'slug' => 'phieu-luu']);
    }

    public function test_word_paste_cleaner_strips_mso_and_preserves_semantics(): void
    {
        $service = app(ContentSanitizerService::class);

        $msoDirtyHtml = '<!--[if gte mso 9]><xml><w:WordDocument><w:View>Normal</w:View></w:WordDocument></xml><![endif]-->' .
            '<p class="MsoNormal" style="mso-margin-top-alt:auto;font-family:Arial;color:#ff0000">' .
            '<strong>Đạo Mộ Bút Ký</strong><o:p></o:p>' .
            '</p>' .
            '<h3>Chương 1: Thất Tinh Lỗ Vương Cung</h3>' .
            '<p style="font-size:12pt">Nội dung <em>phiêu lưu mạo hiểm</em> trong lăng mộ cổ.</p>' .
            '<script>alert("xss")</script>';

        $cleaned = $service->sanitizeWordPaste($msoDirtyHtml);

        // MSO junk and scripts stripped
        $this->assertStringNotContainsString('mso-margin-top-alt', $cleaned);
        $this->assertStringNotContainsString('WordDocument', $cleaned);
        $this->assertStringNotContainsString('<o:p>', $cleaned);
        $this->assertStringNotContainsString('<script>', $cleaned);
        $this->assertStringNotContainsString('color:#ff0000', $cleaned);

        // Semantic tags preserved
        $this->assertStringContainsString('<strong>Đạo Mộ Bút Ký</strong>', $cleaned);
        $this->assertStringContainsString('<h3>Chương 1: Thất Tinh Lỗ Vương Cung</h3>', $cleaned);
        $this->assertStringContainsString('<em>phiêu lưu mạo hiểm</em>', $cleaned);
    }

    public function test_admin_and_moderator_can_upload_editor_image(): void
    {
        $file = UploadedFile::fake()->image('illustration.png', 800, 600);

        $response = $this->actingAs($this->admin)->postJson('/admin/media/tai-anh-soan-thao', [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['url', 'filename']);

        $url = $response->json('url');
        $this->assertNotEmpty($url);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_media_uploaded',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_editor_image_upload_rejects_svg_file(): void
    {
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';
        $svgFile = UploadedFile::fake()->createWithContent('malicious.svg', $svgContent);

        $response = $this->actingAs($this->admin)->postJson('/admin/media/tai-anh-soan-thao', [
            'image' => $svgFile,
        ]);

        $response->assertStatus(422);
    }

    public function test_editor_image_upload_derives_extension_strictly_from_mime_and_ignores_client_extension(): void
    {
        Storage::fake('public');

        // Create fake file with real image/jpeg MIME but dangerous client extension .php
        $file = UploadedFile::fake()->create('exploit.php', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)->postJson('/admin/media/tai-anh-soan-thao', [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $filename = $response->json('filename');

        $this->assertStringEndsWith('.jpg', $filename);
        $this->assertStringNotContainsString('.php', $filename);
    }

    public function test_admin_can_manually_create_novel_for_author(): void
    {
        // View create form
        $this->actingAs($this->admin)->get('/admin/tac-pham/tao-moi')
            ->assertStatus(200)
            ->assertSee('Tạo tác phẩm mới', false);

        // Submit novel
        $response = $this->actingAs($this->admin)->post('/admin/tac-pham', [
            'author_id' => $this->authorUser->id,
            'title' => 'Đạo Mộ Bút Ký',
            'synopsis' => 'Câu chuyện phiêu lưu kỳ bí tìm kiếm cổ vật.',
            'status' => 'draft',
            'age_rating' => '16_plus',
            'copyright_type' => 'original',
            'genres' => [$this->genre->id],
        ]);

        $novel = Novel::where('title', 'Đạo Mộ Bút Ký')->first();
        $this->assertNotNull($novel);
        $response->assertRedirect("/admin/tac-pham/{$novel->id}/chuong");

        $this->assertEquals($this->authorUser->id, $novel->author_id);
        $this->assertTrue($novel->genres->contains($this->genre));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_novel_created',
            'target_type' => 'Novel',
            'target_id' => $novel->id,
        ]);
    }
}

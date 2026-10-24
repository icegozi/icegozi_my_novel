<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\ChapterVersion;
use App\Models\Novel;
use App\Models\User;
use App\Services\ChapterVersionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChapterVersionHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;
    protected User $authorUser;
    protected AuthorProfile $authorProfile;
    protected Novel $novel;
    protected Chapter $chapter;
    protected ChapterVersion $version1;
    protected ChapterVersion $version2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->authorUser = User::factory()->create(['role' => 'author', 'status' => 'active']);

        $this->authorProfile = AuthorProfile::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'Thương Hải',
            'legal_full_name' => 'Trần Văn Hải',
            'identity_number_encrypted' => 'enc123',
            'identity_hash' => hash('sha256', '012345678111'),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $this->novel = Novel::create([
            'author_id' => $this->authorUser->id,
            'title' => 'Thương Hải Tang Điền',
            'slug' => 'thuong-hai-tang-dien',
            'synopsis' => 'Chuyện kể về biển xanh hóa nương dâu.',
            'status' => 'published',
        ]);

        $this->chapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1.0,
            'title' => 'Chương 1: Khởi nguồn (Bản mới nhất)',
            'slug' => 'chuong-1-khoi-nguon',
            'content' => "<p>Dòng thứ nhất: Trời xanh mây trắng.</p>\n<p>Dòng thứ hai: Biển rộng mênh mông bão tố.</p>\n<p>Dòng thứ ba: Kiếm khách xuất hiện.</p>",
            'word_count' => 300,
            'revision' => 3,
            'status' => 'published',
        ]);

        $this->version1 = ChapterVersion::create([
            'chapter_id' => $this->chapter->id,
            'revision' => 1,
            'title' => 'Chương 1: Khởi nguồn (Bản sơ khởi)',
            'content' => "<p>Dòng thứ nhất: Trời xanh mây trắng.</p>\n<p>Dòng thứ hai: Biển phẳng lặng như gương.</p>",
            'word_count' => 100,
            'edited_by' => $this->authorUser->id,
            'change_summary' => 'Bản nháp tác giả tạo lần đầu',
        ]);

        $this->version2 = ChapterVersion::create([
            'chapter_id' => $this->chapter->id,
            'revision' => 2,
            'title' => 'Chương 1: Khởi nguồn (Bản biên tập 1)',
            'content' => "<p>Dòng thứ nhất: Trời xanh mây trắng.</p>\n<p>Dòng thứ hai: Biển rộng mênh mông bão tố.</p>",
            'word_count' => 200,
            'edited_by' => $this->moderator->id,
            'change_summary' => 'Kiểm duyệt viên chỉnh sửa biển thành bão tố',
        ]);
    }

    public function test_service_computes_accurate_diff_between_texts(): void
    {
        $service = app(ChapterVersionService::class);

        $oldText = "Dòng 1\nDòng 2 Cũ\nDòng 3";
        $newText = "Dòng 1\nDòng 2 Mới\nDòng 3\nDòng 4 Thêm";

        $diff = $service->diff($oldText, $newText);

        $this->assertNotEmpty($diff);
        // Find deleted and added lines
        $types = array_column($diff, 'type');
        $this->assertContains('deleted', $types);
        $this->assertContains('added', $types);
        $this->assertContains('unchanged', $types);
    }

    public function test_admin_and_moderator_can_view_revision_history_and_diff(): void
    {
        // Moderator can view history list
        $response = $this->actingAs($this->moderator)->get("/admin/chuong/{$this->chapter->id}/lich-su");
        $response->assertStatus(200);
        $response->assertSee('rev #1', false);
        $response->assertSee('rev #2', false);
        $response->assertSee('Bản nháp tác giả tạo lần đầu', false);

        // Compare rev 1 vs rev 2
        $diffResponse = $this->actingAs($this->admin)->get("/admin/chuong/{$this->chapter->id}/lich-su?compare_from={$this->version1->id}&compare_to={$this->version2->id}");
        $diffResponse->assertStatus(200);
        $diffResponse->assertSee('Biển phẳng lặng như gương', false);
        $diffResponse->assertSee('Biển rộng mênh mông bão tố', false);
    }

    public function test_only_admin_can_restore_previous_version(): void
    {
        // Moderator cannot restore
        $this->actingAs($this->moderator)->post("/admin/chuong/{$this->chapter->id}/khoi-phuc/{$this->version1->id}")
            ->assertStatus(403);

        // Admin can restore
        $response = $this->actingAs($this->admin)->post("/admin/chuong/{$this->chapter->id}/khoi-phuc/{$this->version1->id}");
        $response->assertRedirect("/admin/chuong/{$this->chapter->id}/lich-su");

        // Fresh chapter has reverted title, reverted content, and revision incremented to 4
        $freshChapter = $this->chapter->fresh();
        $this->assertEquals('Chương 1: Khởi nguồn (Bản sơ khởi)', $freshChapter->title);
        $this->assertEquals($this->version1->content, $freshChapter->content);
        $this->assertEquals(4, $freshChapter->revision);

        // Pre-restore snapshot is saved into chapter_versions as rev #3
        $this->assertDatabaseHas('chapter_versions', [
            'chapter_id' => $this->chapter->id,
            'revision' => 3,
            'title' => 'Chương 1: Khởi nguồn (Bản mới nhất)',
        ]);

        // Audit log created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_chapter_restored',
            'target_type' => 'Chapter',
            'target_id' => $this->chapter->id,
        ]);
    }
}

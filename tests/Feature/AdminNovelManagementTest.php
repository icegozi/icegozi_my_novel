<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\ChapterVersion;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNovelManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;
    protected User $reader;
    protected User $authorUser;
    protected AuthorProfile $authorProfile;
    protected Novel $novel;
    protected Chapter $chapter;
    protected Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->reader = User::factory()->create(['role' => 'reader', 'status' => 'active']);

        $this->authorUser = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $this->authorProfile = AuthorProfile::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'Bạch Miêu Tiên Sinh',
            'legal_full_name' => 'Nguyễn Văn Bạch',
            'identity_number_encrypted' => 'enc123',
            'identity_hash' => hash('sha256', '012345678999'),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $this->genre = Genre::create([
            'name' => 'Tiên Hiệp',
            'slug' => 'tien-hiep',
            'description' => 'Thể loại tu tiên',
        ]);

        $this->novel = Novel::create([
            'author_id' => $this->authorUser->id,
            'title' => 'Thiên Đạo Kiếm Tôn',
            'slug' => 'thien-dao-kiem-ton',
            'synopsis' => 'Kiếm tu vô địch thiên hạ.',
            'status' => 'published',
            'meta_title' => 'Thiên Đạo Kiếm Tôn Full',
            'meta_description' => 'Đọc truyện Thiên Đạo Kiếm Tôn.',
        ]);
        $this->novel->genres()->attach($this->genre->id);

        $this->chapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1.0,
            'title' => 'Chương 1: Xuất Thế',
            'slug' => 'chuong-1-xuat-the',
            'content' => '<p>Một tia kiếm quang rọi sáng muôn dặm...</p>',
            'word_count' => 150,
            'revision' => 1,
            'status' => 'published',
        ]);
    }

    public function test_admin_and_moderator_can_list_and_filter_novels(): void
    {
        // Reader forbidden
        $this->actingAs($this->reader)->get('/admin/tac-pham')->assertStatus(403);

        // Admin can list & search
        $response = $this->actingAs($this->admin)->get('/admin/tac-pham?search=Thiên+Đạo');
        $response->assertStatus(200);
        $response->assertSee('Thiên Đạo Kiếm Tôn', false);
        $response->assertSee('Bạch Miêu Tiên Sinh', false);
        $response->assertSee('published', false);

        // Moderator can list
        $modResponse = $this->actingAs($this->moderator)->get('/admin/tac-pham?status=published');
        $modResponse->assertStatus(200);
        $modResponse->assertSee('Thiên Đạo Kiếm Tôn', false);
    }

    public function test_admin_and_moderator_can_view_edit_and_update_novel_and_seo(): void
    {
        // View edit page
        $this->actingAs($this->moderator)->get("/admin/tac-pham/{$this->novel->id}/chinh-sua")
            ->assertStatus(200)
            ->assertSee('Thiên Đạo Kiếm Tôn', false);

        $tag = Tag::create(['name' => 'Nhiệt Huyết', 'slug' => 'nhiet-huyet']);

        // Update novel
        $updateData = [
            'title' => 'Thiên Đạo Kiếm Tôn (Bản Hiệu Đính)',
            'synopsis' => 'Cốt truyện được chỉnh sửa sắc sảo hơn.',
            'status' => 'completed',
            'age_rating' => '16_plus',
            'copyright_type' => 'original',
            'meta_title' => 'Thiên Đạo Kiếm Tôn Hoàn Thành - Đọc Full',
            'meta_description' => 'Trọn bộ Thiên Đạo Kiếm Tôn chất lượng cao.',
            'canonical_url' => 'https://example.com/truyen/thien-dao-kiem-ton',
            'og_image' => 'https://example.com/covers/thien-dao.webp',
            'tags' => [$tag->id],
        ];

        $response = $this->actingAs($this->moderator)->put("/admin/tac-pham/{$this->novel->id}", $updateData);
        $response->assertRedirect("/admin/tac-pham/{$this->novel->id}/chinh-sua");

        $this->assertDatabaseHas('novels', [
            'id' => $this->novel->id,
            'title' => 'Thiên Đạo Kiếm Tôn (Bản Hiệu Đính)',
            'status' => 'completed',
            'meta_title' => 'Thiên Đạo Kiếm Tôn Hoàn Thành - Đọc Full',
            'canonical_url' => 'https://example.com/truyen/thien-dao-kiem-ton',
        ]);

        $this->assertTrue($this->novel->fresh()->tags->contains($tag));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_novel_updated',
            'target_type' => 'Novel',
            'target_id' => $this->novel->id,
        ]);
    }

    public function test_only_admin_can_soft_delete_novel(): void
    {
        // Moderator cannot delete
        $this->actingAs($this->moderator)->delete("/admin/tac-pham/{$this->novel->id}")
            ->assertStatus(403);

        // Admin can delete
        $response = $this->actingAs($this->admin)->delete("/admin/tac-pham/{$this->novel->id}");
        $response->assertRedirect('/admin/tac-pham');

        // Novel is soft-deleted
        $this->assertNull(Novel::find($this->novel->id));
        $this->assertNotNull(Novel::withTrashed()->find($this->novel->id)->deleted_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_novel_soft_deleted',
            'target_type' => 'Novel',
            'target_id' => $this->novel->id,
        ]);
    }

    public function test_admin_and_moderator_can_list_chapters_of_novel(): void
    {
        $response = $this->actingAs($this->moderator)->get("/admin/tac-pham/{$this->novel->id}/chuong");
        $response->assertStatus(200);
        $response->assertSee('Chương 1: Xuất Thế', false);
        $response->assertSee('150', false); // word count
        $response->assertSee('published', false);
    }

    public function test_admin_and_moderator_can_update_chapter_and_record_version(): void
    {
        // View chapter edit
        $this->actingAs($this->admin)->get("/admin/chuong/{$this->chapter->id}/chinh-sua")
            ->assertStatus(200)
            ->assertSee('Chương 1: Xuất Thế', false);

        // Update chapter
        $updateData = [
            'title' => 'Chương 1: Xuất Thế (Bản Chỉnh Lý)',
            'content' => '<p>Một tia kiếm quang rọi sáng muôn dặm vòm trời tây bắc...</p>',
            'status' => 'published',
            'change_summary' => 'Biên tập lại ngữ pháp và thêm chi tiết',
        ];

        $response = $this->actingAs($this->admin)->put("/admin/chuong/{$this->chapter->id}", $updateData);
        $response->assertRedirect("/admin/tac-pham/{$this->novel->id}/chuong");

        // Chapter updated
        $updatedChapter = $this->chapter->fresh();
        $this->assertEquals('Chương 1: Xuất Thế (Bản Chỉnh Lý)', $updatedChapter->title);
        $this->assertEquals(2, $updatedChapter->revision);

        // ChapterVersion snapshot created for previous version
        $this->assertDatabaseHas('chapter_versions', [
            'chapter_id' => $this->chapter->id,
            'revision' => 1,
            'title' => 'Chương 1: Xuất Thế',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_chapter_updated',
            'target_type' => 'Chapter',
            'target_id' => $this->chapter->id,
        ]);
    }

    public function test_admin_can_filter_trashed_novels_and_restore_soft_deleted_novel(): void
    {
        // 1. Soft delete novel
        $this->actingAs($this->admin)->delete("/admin/tac-pham/{$this->novel->id}")
            ->assertRedirect('/admin/tac-pham');

        $this->assertSoftDeleted('novels', ['id' => $this->novel->id]);

        // 2. Normal list does not show it in table
        session()->flush();
        $this->actingAs($this->admin)->get('/admin/tac-pham')
            ->assertDontSee($this->novel->title);

        // 3. Filter with status=trashed shows it with restore button
        $this->actingAs($this->admin)->get('/admin/tac-pham?status=trashed')
            ->assertStatus(200)
            ->assertSee($this->novel->title)
            ->assertSee('Khôi phục tác phẩm');

        // 4. Restore novel
        $restoreResponse = $this->actingAs($this->admin)->post("/admin/tac-pham/{$this->novel->id}/khoi-phuc");
        $restoreResponse->assertRedirect('/admin/tac-pham');

        $this->assertNotSoftDeleted('novels', ['id' => $this->novel->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_novel_restored',
            'target_type' => 'Novel',
            'target_id' => $this->novel->id,
        ]);
    }
}

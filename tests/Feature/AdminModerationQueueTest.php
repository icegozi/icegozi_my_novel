<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModerationQueueTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;
    protected User $author;
    protected User $reader;
    protected Novel $pendingNovel;
    protected Chapter $pendingChapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->moderator = User::factory()->create(['role' => 'moderator']);
        $this->author = User::factory()->create(['role' => 'author']);
        $this->reader = User::factory()->create(['role' => 'reader']);

        AuthorProfile::create([
            'user_id' => $this->author->id,
            'pen_name' => 'Tác Giả Chờ Duyệt',
            'legal_full_name' => 'Ngô Văn L',
            'identity_number_encrypted' => 'encL',
            'identity_hash' => 'hash_l',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $genre = Genre::create(['name' => 'Huyền Huyễn', 'slug' => 'huyen-huyen']);

        $this->pendingNovel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Tác Phẩm Cần Duyệt',
            'slug' => 'tac-pham-can-duyet',
            'synopsis' => 'Mô tả tác phẩm...',
            'copyright_type' => 'original',
            'age_rating' => '16_plus',
            'status' => 'pending_approval',
        ]);
        $this->pendingNovel->genres()->attach($genre);

        $this->pendingChapter = Chapter::create([
            'novel_id' => $this->pendingNovel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Chờ Phê Duyệt',
            'slug' => 'chuong-1-cho-phe-duyet',
            'content' => '<p>Nội dung đang chờ kiểm duyệt viên phê duyệt.</p>',
            'word_count' => 350,
            'status' => 'pending_approval',
            'revision' => 1,
        ]);
    }

    public function test_moderator_can_view_moderation_queue(): void
    {
        $response = $this->actingAs($this->moderator)
            ->get(route('admin.moderation.queue'));

        $response->assertOk()
            ->assertSee('Tác Phẩm Cần Duyệt')
            ->assertSee('Chương 1: Chờ Phê Duyệt');
    }

    public function test_regular_user_cannot_view_moderation_queue(): void
    {
        $response = $this->actingAs($this->reader)
            ->get(route('admin.moderation.queue'));

        $response->assertForbidden();
    }

    public function test_moderator_can_1_click_approve_chapter(): void
    {
        $response = $this->actingAs($this->moderator)
            ->post(route('admin.moderation.chapters.approve', $this->pendingChapter));

        $response->assertRedirect(route('admin.moderation.queue'));

        $this->pendingChapter->refresh();
        $this->assertEquals('published', $this->pendingChapter->status);
        $this->assertNotNull($this->pendingChapter->published_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'chapter_approved',
            'target_type' => 'Chapter',
            'target_id' => $this->pendingChapter->id,
        ]);
    }

    public function test_moderator_can_reject_chapter_with_reason(): void
    {
        $response = $this->actingAs($this->moderator)
            ->post(route('admin.moderation.chapters.reject', $this->pendingChapter), [
                'rejection_reason' => 'Vi phạm thuần phong mỹ tục và chứa từ ngữ kích động.',
            ]);

        $response->assertRedirect(route('admin.moderation.queue'));

        $this->pendingChapter->refresh();
        $this->assertEquals('rejected', $this->pendingChapter->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'chapter_rejected',
            'target_type' => 'Chapter',
            'target_id' => $this->pendingChapter->id,
        ]);
    }

    public function test_moderator_can_approve_novel(): void
    {
        $response = $this->actingAs($this->moderator)
            ->post(route('admin.moderation.novels.approve', $this->pendingNovel));

        $response->assertRedirect(route('admin.moderation.queue'));

        $this->pendingNovel->refresh();
        $this->assertEquals('published', $this->pendingNovel->status);
        $this->assertNotNull($this->pendingNovel->approved_at);
        $this->assertEquals($this->moderator->id, $this->pendingNovel->approved_by);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'novel_approved',
            'target_type' => 'Novel',
            'target_id' => $this->pendingNovel->id,
        ]);
    }
}

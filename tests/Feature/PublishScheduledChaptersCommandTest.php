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

class PublishScheduledChaptersCommandTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected Novel $novel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $this->author->id,
            'pen_name' => 'Tác Giả Đúng Hẹn',
            'legal_full_name' => 'Ngô Văn F',
            'identity_number_encrypted' => 'encF',
            'identity_hash' => 'hash_f',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $genre = Genre::create(['name' => 'Đô Thị', 'slug' => 'do-thi']);
        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Đô Thị Trọng Sinh Hào Môn',
            'slug' => 'do-thi-trong-sinh-hao-mon',
            'synopsis' => 'Mô tả đô thị...',
            'copyright_type' => 'original',
            'age_rating' => '16_plus',
            'status' => 'draft',
        ]);
    }

    public function test_command_publishes_chapters_whose_scheduled_time_has_passed(): void
    {
        // 1. Due for publishing
        $dueChapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Trọng sinh trở lại',
            'slug' => 'chuong-1-trong-sinh-tro-lai',
            'content' => 'Nội dung chương 1...',
            'word_count' => 100,
            'status' => 'scheduled',
            'scheduled_at' => now()->subMinutes(5),
            'revision' => 1,
        ]);

        // 2. Future chapter (not due yet)
        $futureChapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 2,
            'title' => 'Chương 2: Tái ngộ tình nhân',
            'slug' => 'chuong-2-tai-ngo-tinh-nhan',
            'content' => 'Nội dung chương 2...',
            'word_count' => 150,
            'status' => 'scheduled',
            'scheduled_at' => now()->addHours(2),
            'revision' => 1,
        ]);

        // 3. Draft chapter (should not be touched)
        $draftChapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 3,
            'title' => 'Chương 3: Bản thảo nháp',
            'slug' => 'chuong-3-ban-thao-nhap',
            'content' => 'Nội dung nháp...',
            'word_count' => 80,
            'status' => 'draft',
            'revision' => 1,
        ]);

        // Execute Artisan command
        $this->artisan('app:publish-scheduled-chapters')
            ->expectsOutputToContain('1')
            ->assertSuccessful();

        // Assertions
        $dueChapter->refresh();
        $this->assertEquals('published', $dueChapter->status);
        $this->assertNotNull($dueChapter->published_at);

        $futureChapter->refresh();
        $this->assertEquals('scheduled', $futureChapter->status);
        $this->assertNull($futureChapter->published_at);

        $draftChapter->refresh();
        $this->assertEquals('draft', $draftChapter->status);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'chapter_auto_published_by_schedule',
            'target_type' => 'Chapter',
            'target_id' => $dueChapter->id,
        ]);
    }
}

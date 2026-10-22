<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\ChapterReadingProgress;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingHistorySyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $reader;
    protected Novel $novel;
    protected Chapter $chapter1;
    protected Chapter $chapter2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reader = User::factory()->create();

        $author = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $author->id,
            'pen_name' => 'Tác Giả Vô Song',
            'legal_full_name' => 'Dương Văn K',
            'identity_number_encrypted' => 'encK',
            'identity_hash' => 'hash_k',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $genre = Genre::create(['name' => 'Dị Giới', 'slug' => 'di-gioi']);

        $this->novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Vạn Giới Độc Tôn',
            'slug' => 'van-gioi-doc-ton',
            'synopsis' => 'Mô tả dị giới...',
            'copyright_type' => 'original',
            'age_rating' => '16_plus',
            'status' => 'published',
        ]);
        $this->novel->genres()->attach($genre);

        $this->chapter1 = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Trùng sinh',
            'slug' => 'chuong-1-trung-sinh',
            'content' => '<p>Đoạn 0</p><p>Đoạn 1</p><p>Đoạn 2</p>',
            'word_count' => 100,
            'status' => 'published',
            'published_at' => now(),
            'revision' => 1,
        ]);

        $this->chapter2 = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 2,
            'title' => 'Chương 2: Quyết đấu',
            'slug' => 'chuong-2-quyet-dau',
            'content' => '<p>Đoạn 0</p><p>Đoạn 1</p>',
            'word_count' => 150,
            'status' => 'published',
            'published_at' => now(),
            'revision' => 1,
        ]);
    }

    public function test_reader_can_save_independent_reading_progress_for_chapter(): void
    {
        $response = $this->actingAs($this->reader)
            ->postJson(route('reader.progress.sync'), [
                'chapter_id' => $this->chapter1->id,
                'paragraph_index' => 2,
                'scroll_percentage' => 75.5,
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'saved')
            ->assertJsonPath('paragraph_index', 2);

        // Verify independent chapter reading progress saved
        $this->assertDatabaseHas('chapter_reading_progress', [
            'user_id' => $this->reader->id,
            'chapter_id' => $this->chapter1->id,
            'last_read_paragraph' => 2,
            'scroll_percentage' => 75.50,
        ]);

        // Verify novel reading history updated to last read chapter
        $this->assertDatabaseHas('reading_history', [
            'user_id' => $this->reader->id,
            'novel_id' => $this->novel->id,
            'last_chapter_id' => $this->chapter1->id,
        ]);
    }

    public function test_reading_progress_is_independent_across_different_chapters(): void
    {
        // Save progress for Chapter 1
        $this->actingAs($this->reader)
            ->postJson(route('reader.progress.sync'), [
                'chapter_id' => $this->chapter1->id,
                'paragraph_index' => 2,
                'scroll_percentage' => 80.0,
            ]);

        // Save progress for Chapter 2
        $this->actingAs($this->reader)
            ->postJson(route('reader.progress.sync'), [
                'chapter_id' => $this->chapter2->id,
                'paragraph_index' => 1,
                'scroll_percentage' => 50.0,
            ]);

        // Ensure Chapter 1 progress was NOT overwritten
        $this->assertDatabaseHas('chapter_reading_progress', [
            'user_id' => $this->reader->id,
            'chapter_id' => $this->chapter1->id,
            'last_read_paragraph' => 2,
        ]);

        // Ensure Chapter 2 has its own distinct progress
        $this->assertDatabaseHas('chapter_reading_progress', [
            'user_id' => $this->reader->id,
            'chapter_id' => $this->chapter2->id,
            'last_read_paragraph' => 1,
        ]);
    }

    public function test_reader_can_fetch_saved_progress_for_chapter(): void
    {
        ChapterReadingProgress::create([
            'user_id' => $this->reader->id,
            'chapter_id' => $this->chapter1->id,
            'last_read_paragraph' => 1,
            'scroll_percentage' => 45.00,
        ]);

        $response = $this->actingAs($this->reader)
            ->getJson(route('reader.progress.show', $this->chapter1));

        $response->assertOk()
            ->assertJsonPath('last_read_paragraph', 1)
            ->assertJsonPath('scroll_percentage', 45);
    }
}

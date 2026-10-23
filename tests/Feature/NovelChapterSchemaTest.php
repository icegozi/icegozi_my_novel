<?php

namespace Tests\Feature;

use App\Models\Bookmark;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\ChapterReadingProgress;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NovelChapterSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_novels_and_genres_tables_have_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('genres'));
        $this->assertTrue(Schema::hasColumns('genres', [
            'id', 'name', 'slug', 'description', 'created_at', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('novels'));
        $this->assertTrue(Schema::hasColumns('novels', [
            'id', 'author_id', 'title', 'slug', 'synopsis', 'cover_image',
            'status', 'age_rating', 'copyright_type', 'copyright_license_notes',
            'copyright_certificate_file', 'content_warnings', 'total_words',
            'total_chapters', 'view_count', 'bookmark_count', 'rating_avg',
            'rejection_notes', 'approved_at', 'approved_by', 'created_at', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('novel_genre'));
        $this->assertTrue(Schema::hasColumns('novel_genre', [
            'novel_id', 'genre_id',
        ]));
    }

    public function test_chapters_and_interactions_tables_have_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('chapters'));
        $this->assertTrue(Schema::hasColumns('chapters', [
            'id', 'novel_id', 'chapter_number', 'title', 'slug', 'content',
            'word_count', 'status', 'scheduled_at', 'published_at',
            'view_count', 'created_at', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('reading_history'));
        $this->assertTrue(Schema::hasColumns('reading_history', [
            'id', 'user_id', 'novel_id', 'last_chapter_id', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('chapter_reading_progress'));
        $this->assertTrue(Schema::hasColumns('chapter_reading_progress', [
            'id', 'user_id', 'chapter_id', 'last_read_paragraph', 'scroll_percentage', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('bookmarks'));
        $this->assertTrue(Schema::hasColumns('bookmarks', [
            'id', 'user_id', 'novel_id', 'created_at',
        ]));

        $this->assertTrue(Schema::hasTable('chapter_comments'));
        $this->assertTrue(Schema::hasColumns('chapter_comments', [
            'id', 'chapter_id', 'user_id', 'paragraph_index', 'content', 'likes_count', 'is_flagged', 'created_at', 'updated_at',
        ]));
    }

    public function test_novel_and_chapter_model_relationships(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $reader = User::factory()->create(['role' => 'reader']);

        $genre = Genre::create([
            'name' => 'Tiên Hiệp',
            'slug' => 'tien-hiep',
        ]);

        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Đại Phụng Đả Canh Nhân',
            'slug' => 'dai-phung-da-canh-nhan',
            'synopsis' => 'Một câu chuyện tu tiên phá án kịch tính...',
            'cover_image' => 'covers/daiphung.webp',
            'status' => 'published',
            'age_rating' => '16_plus',
            'copyright_type' => 'original',
            'content_warnings' => ['violence', 'sensitive_elements'],
        ]);

        $novel->genres()->attach($genre->id);

        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Bắt Đầu Trong Ngục Tối',
            'slug' => 'chuong-1-bat-dau-trong-nguc-toi',
            'content' => 'Hứa Thất An tỉnh dậy trong nhà giam...',
            'word_count' => 3500,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $readingHistory = ReadingHistory::create([
            'user_id' => $reader->id,
            'novel_id' => $novel->id,
            'last_chapter_id' => $chapter->id,
            'updated_at' => now(),
        ]);

        $progress = ChapterReadingProgress::create([
            'user_id' => $reader->id,
            'chapter_id' => $chapter->id,
            'last_read_paragraph' => 5,
            'scroll_percentage' => 45.50,
            'updated_at' => now(),
        ]);

        $bookmark = Bookmark::create([
            'user_id' => $reader->id,
            'novel_id' => $novel->id,
            'created_at' => now(),
        ]);

        $comment = ChapterComment::create([
            'chapter_id' => $chapter->id,
            'user_id' => $reader->id,
            'paragraph_index' => 5,
            'content' => 'Đoạn này miêu tả cuốn quá!',
        ]);

        $this->assertEquals($author->id, $novel->author->id);
        $this->assertTrue($novel->genres->contains($genre));
        $this->assertTrue($novel->chapters->contains($chapter));
        $this->assertEquals($novel->id, $chapter->novel->id);
        $this->assertEquals($chapter->id, $readingHistory->lastChapter->id);
        $this->assertEquals($chapter->id, $progress->chapter->id);
        $this->assertEquals($novel->id, $bookmark->novel->id);
        $this->assertEquals($chapter->id, $comment->chapter->id);
    }
}

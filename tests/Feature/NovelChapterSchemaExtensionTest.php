<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\ChapterVersion;
use App\Models\Novel;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NovelChapterSchemaExtensionTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected Novel $novel;
    protected Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create(['role' => 'author']);

        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Vĩnh Dạ Chi Chủ',
            'slug' => 'vinh-da-chi-chu',
            'synopsis' => 'Một tác phẩm huyễn tưởng kỳ bí.',
            'status' => 'published',
            'meta_title' => 'Đọc truyện Vĩnh Dạ Chi Chủ Online - Bản Chuẩn',
            'meta_description' => 'Khám phá thế giới Vĩnh Dạ Chi Chủ đầy hấp dẫn.',
            'canonical_url' => 'https://example.com/truyen/vinh-da-chi-chu',
            'og_image' => 'https://example.com/covers/vinh-da.jpg',
        ]);

        $this->chapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1.0,
            'title' => 'Chương 1: Mở Đầu Hắc Ám',
            'slug' => 'chuong-1-mo-dau-hac-am',
            'content' => '<p>Đêm đen như mực bao trùm lấy chân trời...</p>',
            'word_count' => 120,
            'status' => 'published',
        ]);
    }

    public function test_novel_and_chapter_support_soft_deletes(): void
    {
        // Delete novel
        $this->novel->delete();

        // Should not appear in standard query
        $this->assertNull(Novel::find($this->novel->id));

        // Should appear in withTrashed query
        $trashedNovel = Novel::withTrashed()->find($this->novel->id);
        $this->assertNotNull($trashedNovel);
        $this->assertNotNull($trashedNovel->deleted_at);

        // Delete chapter
        $this->chapter->delete();

        // Should not appear in standard query
        $this->assertNull(Chapter::find($this->chapter->id));

        // Should appear in withTrashed query
        $trashedChapter = Chapter::withTrashed()->find($this->chapter->id);
        $this->assertNotNull($trashedChapter);
        $this->assertNotNull($trashedChapter->deleted_at);
    }

    public function test_novel_stores_and_retrieves_seo_metadata(): void
    {
        $novel = Novel::find($this->novel->id);

        $this->assertEquals('Đọc truyện Vĩnh Dạ Chi Chủ Online - Bản Chuẩn', $novel->meta_title);
        $this->assertEquals('Khám phá thế giới Vĩnh Dạ Chi Chủ đầy hấp dẫn.', $novel->meta_description);
        $this->assertEquals('https://example.com/truyen/vinh-da-chi-chu', $novel->canonical_url);
        $this->assertEquals('https://example.com/covers/vinh-da.jpg', $novel->og_image);
    }

    public function test_chapter_has_many_versions_relationship(): void
    {
        $version = ChapterVersion::create([
            'chapter_id' => $this->chapter->id,
            'revision' => 1,
            'title' => 'Chương 1: Bản nháp sơ khởi',
            'content' => '<p>Bản nháp sơ khởi chưa biên tập...</p>',
            'word_count' => 80,
            'edited_by' => $this->author->id,
            'change_summary' => 'Khởi tạo chương ban đầu',
        ]);

        $this->assertTrue($this->chapter->versions->contains($version));
        $this->assertEquals('Chương 1: Bản nháp sơ khởi', $this->chapter->versions->first()->title);
        $this->assertEquals($this->author->id, $this->chapter->versions->first()->editor->id);
    }

    public function test_novel_has_many_tags_relationship(): void
    {
        $tagHuyenHuyen = Tag::create([
            'name' => 'Huyền Huyễn',
            'slug' => 'huyen-huyen',
        ]);

        $tagTuChan = Tag::create([
            'name' => 'Tu Chân',
            'slug' => 'tu-chan',
        ]);

        $this->novel->tags()->attach([$tagHuyenHuyen->id, $tagTuChan->id]);

        $this->assertCount(2, $this->novel->fresh()->tags);
        $this->assertTrue($this->novel->tags->contains($tagHuyenHuyen));
        $this->assertTrue($this->novel->tags->contains($tagTuChan));
    }
}

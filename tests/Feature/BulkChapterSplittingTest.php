<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkChapterSplittingTest extends TestCase
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
            'pen_name' => 'Bút Danh Thần Thoại',
            'legal_full_name' => 'Nguyễn Văn A',
            'identity_number_encrypted' => 'enc',
            'identity_hash' => 'hash',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $genre = Genre::create(['name' => 'Huyền Huyễn', 'slug' => 'huyen-huyen']);
        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Chúa Tể Dị Giới',
            'slug' => 'chua-te-di-gioi',
            'synopsis' => 'Mô tả truyện...',
            'copyright_type' => 'original',
            'age_rating' => '16_plus',
            'status' => 'draft',
        ]);
    }

    public function test_author_can_preview_bulk_chapters(): void
    {
        $payload = <<<TEXT
Chương 1: Khởi nguyên
Đoạn văn chương 1.

Chương 2: Đột phá
Đoạn văn chương 2.
TEXT;

        $response = $this->actingAs($this->author)
            ->postJson(route('author.novels.bulk-chapters.preview', $this->novel), [
                'raw_text' => $payload,
            ]);

        $response->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('chapters.0.title', 'Chương 1: Khởi nguyên')
            ->assertJsonPath('chapters.1.title', 'Chương 2: Đột phá');
    }

    public function test_author_can_save_bulk_chapters(): void
    {
        $payload = <<<TEXT
Chương 1: Khởi nguyên
Nội dung chương một rất hấp dẫn.

Chương 2: Đột phá
Nội dung chương hai còn hấp dẫn hơn.
TEXT;

        $response = $this->actingAs($this->author)
            ->post(route('author.novels.bulk-chapters.store', $this->novel), [
                'raw_text' => $payload,
            ]);

        $response->assertRedirect(route('author.novels.index'));
        $this->assertDatabaseHas('chapters', [
            'novel_id' => $this->novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Khởi nguyên',
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('chapters', [
            'novel_id' => $this->novel->id,
            'chapter_number' => 2,
            'title' => 'Chương 2: Đột phá',
            'status' => 'draft',
        ]);

        $this->novel->refresh();
        $this->assertEquals(2, $this->novel->total_chapters);
    }
}

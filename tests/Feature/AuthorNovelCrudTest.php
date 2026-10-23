<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorNovelCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $author;
    private Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $this->author->id,
            'pen_name' => 'Bút Danh Tác Giả',
            'legal_full_name' => 'NGUYỄN VĂN A',
            'identity_number_encrypted' => 'enc',
            'identity_hash' => hash('sha256', '001099009999'),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $this->genre = Genre::create([
            'name' => 'Tiên Hiệp',
            'slug' => 'tien-hiep',
        ]);
    }

    public function test_author_can_view_novels_index(): void
    {
        Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Truyện Độc Quyền',
            'slug' => 'truyen-doc-quyen',
            'synopsis' => 'Tóm tắt truyện độc quyền của tác giả',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->author)->get('/tac-gia/truyen');
        $response->assertStatus(200);
        $response->assertSee('Truyện Độc Quyền');
        $response->assertSee('Tạo Tác Phẩm Mới');
    }

    public function test_author_can_create_novel_with_copyright_declaration(): void
    {
        Storage::fake('public');

        $cover = UploadedFile::fake()->image('cover.jpg', 600, 800);

        $response = $this->actingAs($this->author)->post('/tac-gia/truyen', [
            'title' => 'Vạn Giới Độc Tôn',
            'synopsis' => 'Một bộ tiểu thuyết tu chân huyền bí đầy kịch tính...',
            'genres' => [$this->genre->id],
            'age_rating' => '16_plus',
            'copyright_type' => 'original',
            'copyright_license_notes' => 'Tác phẩm sáng tạo nguyên tác.',
            'content_warnings' => ['violence', 'blood'],
            'cover_image' => $cover,
        ]);

        $response->assertRedirect('/tac-gia/truyen');
        $response->assertSessionHas('success');

        $novel = Novel::where('title', 'Vạn Giới Độc Tôn')->first();
        $this->assertNotNull($novel);
        $this->assertEquals($this->author->id, $novel->author_id);
        $this->assertEquals('16_plus', $novel->age_rating);
        $this->assertEquals('original', $novel->copyright_type);
        $this->assertTrue($novel->genres->contains($this->genre));
        $this->assertNotNull($novel->cover_image);
        Storage::disk('public')->assertExists($novel->cover_image);
    }

    public function test_author_cannot_edit_novel_owned_by_another_author(): void
    {
        $otherAuthor = User::factory()->create(['role' => 'author']);
        $otherNovel = Novel::create([
            'author_id' => $otherAuthor->id,
            'title' => 'Truyện Người Khác',
            'slug' => 'truyen-nguoi-khac',
            'synopsis' => 'Tóm tắt truyện người khác',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->author)->get("/tac-gia/truyen/{$otherNovel->id}/edit");
        $response->assertStatus(403);

        $responseUpdate = $this->actingAs($this->author)->put("/tac-gia/truyen/{$otherNovel->id}", [
            'title' => 'Cố Tình Đổi Tên',
            'synopsis' => 'Mô tả mới dài hơn 20 ký tự bắt buộc',
            'genres' => [$this->genre->id],
            'age_rating' => 'all',
            'copyright_type' => 'original',
        ]);
        $responseUpdate->assertStatus(403);
    }
}

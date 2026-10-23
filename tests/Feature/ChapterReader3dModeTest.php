<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Chapter3dProjection;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\Novel3dAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChapterReader3dModeTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected Novel $novel;
    protected Chapter $chapter1;
    protected Chapter $chapter2;
    protected Novel3dAsset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $this->author->id,
            'pen_name' => 'Bút Danh Thần Thoại 3D',
            'legal_full_name' => 'Đặng Văn H',
            'identity_number_encrypted' => 'encH',
            'identity_hash' => 'hash_h',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $genre = Genre::create(['name' => 'Tiên Hiệp 3D', 'slug' => 'tien-hiep-3d']);

        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Cửu Tinh Bá Thể Quyết',
            'slug' => 'cuu-tinh-ba-the-quyet',
            'synopsis' => 'Tiểu thuyết tiên hiệp 3D...',
            'copyright_type' => 'original',
            'age_rating' => '16_plus',
            'status' => 'published',
        ]);
        $this->novel->genres()->attach($genre);

        $this->chapter1 = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Đan Điền Phục Sinh',
            'slug' => 'chuong-1-dan-dien-phuc-sinh',
            'content' => '<p>Đoạn văn 0: Long Trần mở mắt thức tỉnh.</p><p>Đoạn văn 1: Tay cầm Lôi Đình Chiến Đao chém phá thương khung.</p>',
            'word_count' => 500,
            'status' => 'published',
            'published_at' => now(),
            'revision' => 1,
        ]);

        $this->chapter2 = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 2,
            'title' => 'Chương 2: Thần Lôi Luyện Thể',
            'slug' => 'chuong-2-than-loi-luyen-the',
            'content' => '<p>Đoạn văn 0: Lôi đình giáng thế.</p>',
            'word_count' => 600,
            'status' => 'published',
            'published_at' => now(),
            'revision' => 1,
        ]);

        $this->asset = Novel3dAsset::create([
            'novel_id' => $this->novel->id,
            'name' => 'Lôi Đình Chiến Đao',
            'asset_type' => 'weapon',
            'model_file_url' => '/storage/3d-models/saber.glb',
            'scale_factor' => 1.25,
            'particle_effect' => 'divine_sparks',
        ]);

        Chapter3dProjection::create([
            'chapter_id' => $this->chapter1->id,
            'asset_3d_id' => $this->asset->id,
            'paragraph_index' => 1,
            'trigger_keyword' => 'Lôi Đình Chiến Đao',
            'hologram_title' => 'Chiến Đao Xuất Thế',
            'auto_rotate' => true,
            'display_mode' => 'split_stage',
        ]);
    }

    public function test_guest_can_access_chapter_reader_with_3d_projections(): void
    {
        $response = $this->get(route('reader.chapters.read', [
            'slug' => $this->novel->slug,
            'chapterNumber' => 1,
        ]));

        $response->assertOk()
            ->assertSee('Chương 1: Đan Điền Phục Sinh')
            ->assertSee('Long Trần mở mắt thức tỉnh')
            ->assertSee('Chiến Đao Xuất Thế')
            ->assertSee('/storage/3d-models/saber.glb')
            ->assertSee('Tương tác 360°');
    }

    public function test_reader_provides_next_and_previous_chapter_links(): void
    {
        $response = $this->get(route('reader.chapters.read', [
            'slug' => $this->novel->slug,
            'chapterNumber' => 1,
        ]));

        $response->assertOk()
            ->assertSee(route('reader.chapters.read', [$this->novel->slug, 2]));
    }

    public function test_guest_cannot_read_unpublished_chapter(): void
    {
        $unpublishedChapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 3,
            'title' => 'Chương 3: Bản nháp bí mật',
            'slug' => 'chuong-3-ban-nhap-bi-mat',
            'content' => '<p>Chưa đăng...</p>',
            'word_count' => 100,
            'status' => 'draft',
            'revision' => 1,
        ]);

        $response = $this->get(route('reader.chapters.read', [
            'slug' => $this->novel->slug,
            'chapterNumber' => 3,
        ]));

        $response->assertNotFound();
    }
}

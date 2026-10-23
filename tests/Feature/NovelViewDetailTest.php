<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\Novel3dAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NovelViewDetailTest extends TestCase
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
            'pen_name' => 'Thiên Đạo Tác Giả',
            'legal_full_name' => 'Vũ Văn G',
            'identity_number_encrypted' => 'encG',
            'identity_hash' => 'hash_g',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $genre = Genre::create(['name' => 'Tiên Hiệp', 'slug' => 'tien-hiep']);

        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Thần Ma Biến',
            'slug' => 'than-ma-bien',
            'synopsis' => 'Một bộ tiểu thuyết tiên hiệp kỳ ảo hoành tráng.',
            'copyright_type' => 'original',
            'age_rating' => '16_plus',
            'status' => 'published',
        ]);
        $this->novel->genres()->attach($genre);

        Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Khởi đầu vận mệnh',
            'slug' => 'chuong-1-khoi-dau-van-menh',
            'content' => 'Nội dung chương 1...',
            'word_count' => 1200,
            'status' => 'published',
            'published_at' => now(),
            'revision' => 1,
        ]);

        Novel3dAsset::create([
            'novel_id' => $this->novel->id,
            'name' => 'Long Uyên Bảo Kiếm',
            'asset_type' => 'weapon',
            'model_file_url' => '/storage/3d-models/sword.glb',
            'scale_factor' => 1.5,
            'particle_effect' => 'divine_sparks',
            'description' => 'Bảo kiếm viễn cổ.',
        ]);
    }

    public function test_guest_can_view_published_novel_detail_page(): void
    {
        $response = $this->get(route('reader.novels.show', $this->novel->slug));

        $response->assertOk()
            ->assertSee('Thần Ma Biến')
            ->assertSee('Thiên Đạo Tác Giả')
            ->assertSee('Tiên Hiệp')
            ->assertSee('16+')
            ->assertSee('Chương 1: Khởi đầu vận mệnh')
            ->assertSee('Long Uyên Bảo Kiếm');
    }

    public function test_guest_can_view_3d_exhibition_stage(): void
    {
        $response = $this->get(route('reader.novels.exhibition', $this->novel->slug));

        $response->assertOk()
            ->assertSee('Phòng Trưng Bày 3D')
            ->assertSee('Long Uyên Bảo Kiếm')
            ->assertSee('weapon');
    }

    public function test_guest_cannot_view_unpublished_novel_detail(): void
    {
        $draftNovel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Bản Thảo Chưa Đăng',
            'slug' => 'ban-thao-chua-dang',
            'synopsis' => 'Bản nháp...',
            'copyright_type' => 'original',
            'age_rating' => 'all',
            'status' => 'draft',
        ]);

        $response = $this->get(route('reader.novels.show', $draftNovel->slug));
        $response->assertNotFound();
    }
}

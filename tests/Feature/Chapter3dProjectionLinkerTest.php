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

class Chapter3dProjectionLinkerTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected User $otherAuthor;
    protected Novel $novel;
    protected Chapter $chapter;
    protected Novel3dAsset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $this->author->id,
            'pen_name' => 'Bút Danh Cụ Hóa 3D',
            'legal_full_name' => 'Hoàng Văn D',
            'identity_number_encrypted' => 'enc1',
            'identity_hash' => 'hash_d',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $this->otherAuthor = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $this->otherAuthor->id,
            'pen_name' => 'Kẻ Xâm Nhập',
            'legal_full_name' => 'Lý Văn E',
            'identity_number_encrypted' => 'enc2',
            'identity_hash' => 'hash_e',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $genre = Genre::create(['name' => 'Tu Chân', 'slug' => 'tu-chan']);
        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Đại Đạo Phong Ma',
            'slug' => 'dai-dao-phong-ma',
            'synopsis' => 'Mô tả tu chân...',
            'copyright_type' => 'original',
            'age_rating' => '16_plus',
            'status' => 'draft',
        ]);

        $this->chapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Xuất thế',
            'slug' => 'chuong-1-xuat-the',
            'content' => '<p>Đoạn văn 0: Bầu trời giông bão.</p><p>Đoạn văn 1: Kiếm quang lóe sáng rực rỡ.</p>',
            'word_count' => 10,
            'status' => 'draft',
            'revision' => 1,
        ]);

        $this->asset = Novel3dAsset::create([
            'novel_id' => $this->novel->id,
            'name' => 'Cổ Kiếm Thanh Sương',
            'asset_type' => 'weapon',
            'model_file_url' => '/storage/3d-models/sword.glb',
            'scale_factor' => 1.20,
            'particle_effect' => 'divine_sparks',
        ]);
    }

    public function test_author_can_link_3d_asset_to_paragraph(): void
    {
        $response = $this->actingAs($this->author)
            ->postJson(route('author.chapters.projections.store', $this->chapter), [
                'asset_3d_id' => $this->asset->id,
                'paragraph_index' => 1,
                'trigger_keyword' => 'Kiếm quang',
                'hologram_title' => 'Thanh Sương Kiếm Cụ Hóa',
                'auto_rotate' => true,
                'display_mode' => 'split_stage',
            ]);

        $response->assertCreated()
            ->assertJsonPath('projection.paragraph_index', 1)
            ->assertJsonPath('projection.hologram_title', 'Thanh Sương Kiếm Cụ Hóa');

        $this->assertDatabaseHas('chapter_3d_projections', [
            'chapter_id' => $this->chapter->id,
            'asset_3d_id' => $this->asset->id,
            'paragraph_index' => 1,
            'hologram_title' => 'Thanh Sương Kiếm Cụ Hóa',
        ]);
    }

    public function test_author_can_fetch_all_projections_for_chapter(): void
    {
        Chapter3dProjection::create([
            'chapter_id' => $this->chapter->id,
            'asset_3d_id' => $this->asset->id,
            'paragraph_index' => 0,
            'trigger_keyword' => 'Bầu trời',
            'hologram_title' => 'Bầu trời u ám',
            'auto_rotate' => false,
            'display_mode' => 'hologram_overlay',
        ]);

        $response = $this->actingAs($this->author)
            ->getJson(route('author.chapters.projections.index', $this->chapter));

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.hologram_title', 'Bầu trời u ám');
    }

    public function test_author_can_delete_projection(): void
    {
        $projection = Chapter3dProjection::create([
            'chapter_id' => $this->chapter->id,
            'asset_3d_id' => $this->asset->id,
            'paragraph_index' => 1,
            'trigger_keyword' => 'Kiếm quang',
            'hologram_title' => 'Thanh Sương Kiếm Cụ Hóa',
            'auto_rotate' => true,
            'display_mode' => 'split_stage',
        ]);

        $response = $this->actingAs($this->author)
            ->deleteJson(route('author.chapters.projections.destroy', [$this->chapter, $projection]));

        $response->assertOk();
        $this->assertDatabaseMissing('chapter_3d_projections', [
            'id' => $projection->id,
        ]);
    }

    public function test_unauthorized_author_cannot_link_or_delete_projections(): void
    {
        $response = $this->actingAs($this->otherAuthor)
            ->postJson(route('author.chapters.projections.store', $this->chapter), [
                'asset_3d_id' => $this->asset->id,
                'paragraph_index' => 0,
                'hologram_title' => 'Hack title',
            ]);

        $response->assertForbidden();
    }
}

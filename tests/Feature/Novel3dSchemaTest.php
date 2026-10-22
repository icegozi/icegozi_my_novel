<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Chapter3dProjection;
use App\Models\Novel;
use App\Models\Novel3dAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Novel3dSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_novel_3d_assets_and_chapter_3d_projections_tables_have_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('novel_3d_assets'));
        $this->assertTrue(Schema::hasColumns('novel_3d_assets', [
            'id', 'novel_id', 'name', 'asset_type', 'model_file_url', 'thumbnail_url',
            'scale_factor', 'camera_init_position', 'ambient_light_color',
            'particle_effect', 'sound_ambient_url', 'description', 'is_private',
            'created_at', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('chapter_3d_projections'));
        $this->assertTrue(Schema::hasColumns('chapter_3d_projections', [
            'id', 'chapter_id', 'asset_3d_id', 'paragraph_index',
            'trigger_keyword', 'hologram_title', 'auto_rotate', 'display_mode',
            'created_at', 'updated_at',
        ]));
    }

    public function test_3d_asset_and_projection_model_relationships_and_casts(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Thần Điêu Hiệp Lữ 3D',
            'slug' => 'than-dieu-hiep-lu-3d',
            'synopsis' => 'Kiếm hiệp 3D',
            'cover_image' => 'covers/tdhl.webp',
            'status' => 'published',
        ]);

        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Trọng Kiếm Vô Phong',
            'slug' => 'chuong-1-trong-kiem-vo-phong',
            'content' => 'Đoạn 0. Đoạn 1 Huyền Thiết Trọng Kiếm tỏa ra hắc quang.',
            'word_count' => 1200,
            'status' => 'published',
        ]);

        $asset = Novel3dAsset::create([
            'novel_id' => $novel->id,
            'name' => 'Huyền Thiết Trọng Kiếm',
            'asset_type' => 'weapon',
            'model_file_url' => 'assets3d/huyen_thiet_kiem.glb',
            'scale_factor' => 1.50,
            'camera_init_position' => ['x' => 0, 'y' => 2, 'z' => 5],
            'ambient_light_color' => '#1a1a2e',
            'particle_effect' => 'divine_sparks',
            'is_private' => false,
        ]);

        $projection = Chapter3dProjection::create([
            'chapter_id' => $chapter->id,
            'asset_3d_id' => $asset->id,
            'paragraph_index' => 1,
            'trigger_keyword' => 'Huyền Thiết Trọng Kiếm',
            'hologram_title' => 'Cổ Vật: Huyền Thiết Kiếm (Nặng 64 cân)',
            'auto_rotate' => true,
            'display_mode' => 'split_stage',
        ]);

        $this->assertEquals($novel->id, $asset->novel->id);
        $this->assertEquals(['x' => 0, 'y' => 2, 'z' => 5], $asset->camera_init_position);
        $this->assertEquals(1.50, (float)$asset->scale_factor);
        $this->assertFalse($asset->is_private);

        $this->assertEquals($chapter->id, $projection->chapter->id);
        $this->assertEquals($asset->id, $projection->asset3d->id);
        $this->assertTrue($projection->auto_rotate);
        $this->assertTrue($novel->assets3d->contains($asset));
        $this->assertTrue($chapter->projections3d->contains($projection));
    }
}

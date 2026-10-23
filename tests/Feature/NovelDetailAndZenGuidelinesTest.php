<?php

namespace Tests\Feature;

use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NovelDetailAndZenGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_novel_show_has_correct_heading_order_and_hidden_artifact_icon(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Long Thần Quyết',
            'slug' => 'long-than-quyet',
            'synopsis' => 'Truyện dài tu chân huyền ảo.',
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 1,
        ]);

        $novel->assets3d()->create([
            'name' => 'Thần Binh Trảm Long',
            'model_file_url' => 'assets/sword.glb',
            'asset_type' => 'weapon',
        ]);

        $response = $this->get(route('reader.novels.show', $novel->slug));

        $response->assertStatus(200);
        $response->assertSee('<h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tóm Tắt Tác Phẩm</h2>', false);
        $response->assertDontSee('<h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tóm Tắt Tác Phẩm</h3>', false);
        $response->assertSee('aria-hidden="true">🗡️</span>', false);
    }
}

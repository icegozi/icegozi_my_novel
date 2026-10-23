<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReaderAssetsBundlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_3d_view_does_not_contain_broken_resource_links(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Thần Ma Biến',
            'slug' => 'than-ma-bien',
            'synopsis' => 'Tiểu thuyết kiểm thử',
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 1,
        ]);

        Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1',
            'slug' => 'chuong-1',
            'content' => '<p>Đoạn văn kiểm thử nội dung đọc.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('reader.chapters.read', [$novel->slug, 1]));

        $response->assertStatus(200);
        $response->assertDontSee('/resources/css/reader-stage.css', false);
        $response->assertDontSee('/resources/js/reader-controls.js', false);
        $response->assertDontSee("import { ProjectionStage } from '/resources/", false);
    }

    public function test_app_css_imports_reader_stage_css(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('reader-stage.css', $appCss);
    }
}

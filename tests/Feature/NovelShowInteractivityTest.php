<?php

namespace Tests\Feature;

use App\Models\Bookmark;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NovelShowInteractivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_novel_show_reflects_bookmarked_state_for_logged_in_user(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $reader = User::factory()->create(['role' => 'reader', 'status' => 'active']);
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
            'content' => '<p>Nội dung</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Bookmark::create([
            'user_id' => $reader->id,
            'novel_id' => $novel->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($reader)->get(route('reader.novels.show', $novel->slug));

        $response->assertStatus(200);
        $response->assertSee('Đã Lưu Vào Tủ Sách');
    }

    public function test_novel_show_contains_sort_and_share_controls(): void
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

        $response = $this->get(route('reader.novels.show', $novel->slug));

        $response->assertStatus(200);
        $response->assertSee('id="btn-sort-chapters"', false);
        $response->assertSee('id="btn-share-novel"', false);
    }
}

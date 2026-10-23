<?php

namespace Tests\Feature;

use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NovelShowGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_novel_show_has_accessible_filter_and_star_labels(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Võ Thần Tái Sinh',
            'slug' => 'vo-than-tai-sinh',
            'synopsis' => 'Tiểu thuyết tu chân',
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 1,
        ]);

        $response = $this->get(route('reader.novels.show', $novel->slug));

        $response->assertStatus(200);
        $response->assertSee('aria-label="Lọc danh sách chương"', false);
        $response->assertSee(':aria-label="\'Đánh giá \' + star + \' sao\'"', false);
        $response->assertSee('placeholder="Viết vài dòng chia sẻ về truyện…"', false);
    }
}

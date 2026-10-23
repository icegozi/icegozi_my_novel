<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLibraryGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_and_history_views_include_image_dimensions_and_focus_opacity(): void
    {
        $user = User::factory()->create(['status' => 'active']);
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
        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1',
            'slug' => 'chuong-1',
            'content' => '<p>Chương một</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $user->bookmarks()->create(['novel_id' => $novel->id]);
        ReadingHistory::create([
            'user_id' => $user->id,
            'novel_id' => $novel->id,
            'last_chapter_id' => $chapter->id,
        ]);

        $libResponse = $this->actingAs($user)->get(route('reader.library'));
        $libResponse->assertStatus(200);
        $libResponse->assertSee('width="300"', false);
        $libResponse->assertSee('height="400"', false);
        $libResponse->assertSee('focus-visible:opacity-100', false);

        $histResponse = $this->actingAs($user)->get(route('reader.history'));
        $histResponse->assertStatus(200);
        $histResponse->assertSee('width="56"', false);
        $histResponse->assertSee('height="80"', false);
    }
}

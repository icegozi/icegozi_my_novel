<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeAndAuthGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_has_image_dimensions_and_high_contrast_chapter_feed(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $user = User::factory()->create(['status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Chúa Tể Chi Vương',
            'slug' => 'chua-te-chi-vuong',
            'synopsis' => 'Tu luyện huyền ảo.',
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 1,
        ]);
        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Thần Đồng',
            'slug' => 'chuong-1-than-dong',
            'content' => '<p>Chương một</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        ReadingHistory::create([
            'user_id' => $user->id,
            'novel_id' => $novel->id,
            'last_chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('width="56" height="80"', false);
        $response->assertSee('width="48" height="64"', false);
        $response->assertSee('text-slate-500 dark:text-slate-400 sepia:text-sepia-muted', false);
    }

    public function test_library_view_has_explicit_transitions_and_hidden_icon(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $libResponse = $this->actingAs($user)->get('/library');
        $libResponse->assertStatus(200);
        $libResponse->assertSee('aria-hidden="true">📚</span>', false);
        $libResponse->assertDontSee('transition-all duration-200', false);
    }

    public function test_login_view_has_explicit_transitions_and_focus_ring(): void
    {
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('focus-visible:ring-2 focus-visible:ring-indigo-500', false);
        $loginResponse->assertSee('transition-transform transition-colors', false);
    }
}

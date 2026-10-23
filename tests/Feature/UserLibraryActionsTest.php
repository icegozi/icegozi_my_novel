<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLibraryActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_delete_reading_history_record(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Cửu Châu Thần Ký',
            'slug' => 'cuu-chau-than-ky',
            'synopsis' => 'Tiểu thuyết lịch sử',
            'status' => 'published',
            'total_words' => 1000,
            'total_chapters' => 1,
        ]);
        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Khởi đầu',
            'slug' => 'khoi-dau',
            'content' => '<p>Chương một</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $history = ReadingHistory::create([
            'user_id' => $user->id,
            'novel_id' => $novel->id,
            'last_chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/history/{$history->id}");

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
        $this->assertDatabaseMissing('reading_history', ['id' => $history->id]);
    }

    public function test_user_cannot_delete_another_users_reading_history(): void
    {
        $user1 = User::factory()->create(['status' => 'active']);
        $user2 = User::factory()->create(['status' => 'active']);
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Cửu Châu Thần Ký',
            'slug' => 'cuu-chau-than-ky',
            'synopsis' => 'Tiểu thuyết lịch sử',
            'status' => 'published',
            'total_words' => 1000,
            'total_chapters' => 1,
        ]);
        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Khởi đầu',
            'slug' => 'khoi-dau',
            'content' => '<p>Chương một</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $history = ReadingHistory::create([
            'user_id' => $user1->id,
            'novel_id' => $novel->id,
            'last_chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($user2)->deleteJson("/api/history/{$history->id}");
        $response->assertStatus(403);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Bookmark;
use App\Models\Novel;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAreaRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_library_history_and_settings(): void
    {
        $this->seed();
        $user = User::first();
        $novel = Novel::where('status', 'published')->first();
        $chapter = $novel->chapters()->first();

        Bookmark::firstOrCreate([
            'user_id' => $user->id,
            'novel_id' => $novel->id,
        ]);

        ReadingHistory::firstOrCreate([
            'user_id' => $user->id,
            'novel_id' => $novel->id,
            'last_chapter_id' => $chapter->id,
        ]);

        $this->actingAs($user)->get('/library')->assertStatus(200)->assertSee('Tủ Sách Cá Nhân');
        $this->actingAs($user)->get('/history')->assertStatus(200)->assertSee('Lịch Sử Đọc');
        $this->actingAs($user)->get('/bookmarks')->assertStatus(200)->assertSee('Đánh Dấu');
        $this->actingAs($user)->get('/notifications')->assertStatus(200)->assertSee('Thông Báo');
        $this->actingAs($user)->get('/profile')->assertStatus(200)->assertSee('Hồ Sơ');
        $this->actingAs($user)->get('/settings')->assertStatus(200)->assertSee('Cài Đặt');
        $this->actingAs($user)->get('/settings/security')->assertStatus(200)->assertSee('Bảo Mật');
    }

    public function test_bookmark_toggle_api_works(): void
    {
        $this->seed();
        $user = User::first();
        $novel = Novel::where('status', 'published')->first();

        $response = $this->actingAs($user)->postJson('/api/bookmarks/toggle', [
            'novel_id' => $novel->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'bookmarked', 'message']);
    }
}

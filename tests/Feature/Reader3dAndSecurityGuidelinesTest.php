<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Reader3dAndSecurityGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_3d_has_accessible_buttons_and_modal_drawer(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Đại Lục Khải Huyền',
            'slug' => 'dai-luc-khai-huyen',
            'synopsis' => 'Thế giới mở 3D',
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 1,
        ]);
        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Khởi Đầu Mới',
            'slug' => 'khoi-dau-moi',
            'content' => '<p>Chương một</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('reader.chapters.read', [$novel->slug, 1]));

        $response->assertStatus(200);
        $response->assertSee('aria-label="Aa Cỡ Chữ - Cài đặt giao diện đọc"', false);
        $response->assertSee('aria-hidden="true">📑</span>', false);
        $response->assertSee('aria-modal="true"', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('overscroll-contain', false);
    }

    public function test_security_form_includes_hidden_username_field(): void
    {
        $user = User::factory()->create(['status' => 'active', 'email' => 'reader@novel3d.vn']);

        $response = $this->actingAs($user)->get('/settings/security');

        $response->assertStatus(200);
        $response->assertSee('name="username"', false);
        $response->assertSee('autocomplete="username"', false);
    }
}

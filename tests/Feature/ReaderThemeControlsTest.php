<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReaderThemeControlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_3d_view_defaults_to_sepia_and_has_no_dark_button(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $author->id,
            'pen_name' => 'Tác Giả Vàng',
            'legal_full_name' => 'Nguyễn Văn Vàng',
            'identity_number_encrypted' => 'enc123',
            'identity_hash' => 'hash123',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Chủ Đề Vàng',
            'slug' => 'chu-de-vang',
            'synopsis' => 'Mô tả truyện...',
            'copyright_type' => 'original',
            'age_rating' => 'all',
            'status' => 'published',
        ]);

        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1',
            'slug' => 'chuong-1',
            'content' => '<p>Nội dung trên nền vàng.</p>',
            'word_count' => 100,
            'status' => 'published',
            'published_at' => now(),
            'revision' => 1,
        ]);

        $response = $this->get(route('reader.chapters.read', ['slug' => $novel->slug, 'chapterNumber' => 1]));
        $response->assertOk();
        $response->assertSee('theme-sepia sepia', false);
        $response->assertDontSee('theme-dark', false);
        $response->assertDontSee("window.setReaderTheme('dark')", false);
        $response->assertSee('Màu Vàng (Giấy Cổ)', false);
    }

    public function test_user_settings_page_does_not_contain_dark_mode_option(): void
    {
        $user = User::factory()->create(['role' => 'reader']);

        $response = $this->actingAs($user)->get(route('reader.settings'));
        $response->assertOk();
        $response->assertSee('Màu Vàng (Giấy Cổ)', false);
        $response->assertDontSee('value="dark"', false);
        $response->assertDontSee('Tối (Dark)', false);
    }

    public function test_theme_controls_comply_with_web_interface_guidelines(): void
    {
        $settingsView = view('reader.user.settings', [
            'user' => User::factory()->make(),
            'preferences' => ['theme' => 'sepia'],
        ])->render();

        $this->assertMatchesRegularExpression('/name="theme"[^>]*focus-visible:ring-indigo-500/', $settingsView);
        $this->assertStringNotContainsString('transition-all', $settingsView);

        $zenToolbarView = view('reader.chapters.partials.zen_toolbar')->render();
        $this->assertStringContainsString('focus-visible:ring-2', $zenToolbarView);
        $this->assertStringNotContainsString('transition-all', $zenToolbarView);
    }
}

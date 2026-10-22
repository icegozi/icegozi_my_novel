<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReaderPreferencesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Novel $novel;
    protected Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $author = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $author->id,
            'pen_name' => 'Bút Danh Tinh Gọn',
            'legal_full_name' => 'Võ Văn I',
            'identity_number_encrypted' => 'encI',
            'identity_hash' => 'hash_i',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $genre = Genre::create(['name' => 'Văn Học', 'slug' => 'van-hoc']);

        $this->novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Tĩnh Tâm Ký',
            'slug' => 'tinh-tam-ky',
            'synopsis' => 'Mô tả tĩnh tâm...',
            'copyright_type' => 'original',
            'age_rating' => 'all',
            'status' => 'published',
        ]);
        $this->novel->genres()->attach($genre);

        $this->chapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Trà Đạo',
            'slug' => 'chuong-1-tra-dao',
            'content' => '<p>Một tách trà thơm giữa buổi sớm mai.</p>',
            'word_count' => 300,
            'status' => 'published',
            'published_at' => now(),
            'revision' => 1,
        ]);
    }

    public function test_reader_preferences_api_saves_valid_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('reader.preferences.update'), [
                'theme' => 'sepia',
                'font_size' => 'lg',
                'mode' => 'zen',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('preferences.theme', 'sepia')
            ->assertJsonPath('preferences.font_size', 'lg')
            ->assertJsonPath('preferences.mode', 'zen');

        $this->assertEquals('sepia', session('reader_preferences.theme'));
    }

    public function test_reader_preferences_api_validates_input(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('reader.preferences.update'), [
                'theme' => 'neon-invalid',
                'font_size' => 'huge',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme', 'font_size']);
    }

    public function test_reader_view_includes_zen_toolbar_components(): void
    {
        $response = $this->get(route('reader.chapters.read', [
            'slug' => $this->novel->slug,
            'chapterNumber' => 1,
        ]));

        $response->assertOk()
            ->assertSee('zen-toolbar')
            ->assertSee('Chế Độ Tinh Gọn');
    }
}

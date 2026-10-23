<?php

namespace Tests\Feature;

use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorNovelsGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_novels_index_renders_compliant_markup(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        \App\Models\AuthorProfile::create([
            'user_id' => $author->id,
            'pen_name' => 'Bút Danh Tác Giả',
            'legal_full_name' => 'NGUYỄN VĂN A',
            'identity_number_encrypted' => 'enc',
            'identity_hash' => hash('sha256', '001099009999'),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);
        $novelWithCover = Novel::create([
            'author_id' => $author->id,
            'title' => 'Kiếm Đạo Độc Tôn',
            'slug' => 'kiem-dao-doc-ton',
            'synopsis' => 'Một tác phẩm kiếm hiệp đặc sắc.',
            'cover_image' => '/images/covers/tien-hiep.webp',
            'status' => 'published',
            'total_words' => 10000,
            'total_chapters' => 1,
            'age_rating' => 'all',
            'copyright_type' => 'original',
        ]);

        $novelWithoutCover = Novel::create([
            'author_id' => $author->id,
            'title' => 'Thiên Đạo Đồ',
            'slug' => 'thien-dao-do',
            'synopsis' => 'Một tác phẩm tu chân.',
            'cover_image' => null,
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 0,
            'age_rating' => 'all',
            'copyright_type' => 'original',
        ]);

        $response = $this->actingAs($author)->get('/tac-gia/truyen');

        $response->assertStatus(200);
        $response->assertDontSee('/storage//images/covers/', false);
        $response->assertSee('src="/images/covers/tien-hiep.webp"', false);
        $response->assertSee('width="64" height="88"', false);
        $response->assertSee('aria-label="Xem trên giao diện người đọc"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
        $response->assertSee('aria-hidden="true">✍️</span>', false);
        $response->assertSee('aria-hidden="true">📖</span>', false);
        $response->assertDontSee('transition-all', false);
    }
}

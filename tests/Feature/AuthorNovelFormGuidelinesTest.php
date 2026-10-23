<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorNovelFormGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_novel_form_has_labels_ids_and_typographic_ellipsis(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        AuthorProfile::create([
            'user_id' => $author->id,
            'pen_name' => 'Bút Danh Tác Giả',
            'legal_full_name' => 'NGUYỄN VĂN A',
            'identity_number_encrypted' => 'enc',
            'identity_hash' => hash('sha256', '001099009999'),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);
        Genre::create(['name' => 'Tiên Hiệp', 'slug' => 'tien-hiep']);

        $response = $this->actingAs($author)->get('/tac-gia/truyen/create');

        $response->assertStatus(200);
        $response->assertSee('<label for="title"', false);
        $response->assertSee('id="title"', false);
        $response->assertSee('<label for="synopsis"', false);
        $response->assertSee('id="synopsis"', false);
        $response->assertSee('<label for="age_rating"', false);
        $response->assertSee('id="age_rating"', false);
        $response->assertSee('<label for="copyright_type"', false);
        $response->assertSee('id="copyright_type"', false);
        $response->assertSee('<label for="cover_image"', false);
        $response->assertSee('id="cover_image"', false);
        $response->assertSee('<label for="copyright_license_notes"', false);
        $response->assertSee('id="copyright_license_notes"', false);
        $response->assertSee('placeholder="VD: Vũ Động Càn Khôn 3D…"', false);
        $response->assertSee('placeholder="Giới thiệu cốt truyện lôi cuốn, thế giới quan, nhân vật chính…"', false);
        $response->assertSee('placeholder="Ghi rõ nguồn, số hợp đồng chuyển nhượng quyền tác giả…"', false);
        $response->assertDontSee('transition-all', false);
    }
}

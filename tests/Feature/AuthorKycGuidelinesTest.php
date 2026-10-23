<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorKycGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_kyc_registration_view_has_complete_labels_ids_and_aria_hidden(): void
    {
        $user = User::factory()->create(['role' => 'reader', 'status' => 'active']);

        $response = $this->actingAs($user)->get('/tac-gia/dang-ky-dinh-danh');

        $response->assertStatus(200);
        $response->assertSee('<label for="pen_name"', false);
        $response->assertSee('id="pen_name"', false);
        $response->assertSee('<label for="legal_full_name"', false);
        $response->assertSee('id="legal_full_name"', false);
        $response->assertSee('<label for="identity_number"', false);
        $response->assertSee('id="identity_number"', false);
        $response->assertSee('<label for="phone"', false);
        $response->assertSee('id="phone"', false);
        $response->assertSee('<label for="identity_issued_date"', false);
        $response->assertSee('id="identity_issued_date"', false);
        $response->assertSee('<label for="identity_front_image"', false);
        $response->assertSee('id="identity_front_image"', false);
        $response->assertSee('<label for="identity_back_image"', false);
        $response->assertSee('id="identity_back_image"', false);
        $response->assertSee('<label for="tax_code"', false);
        $response->assertSee('id="tax_code"', false);
        $response->assertSee('<label for="bank_account_number"', false);
        $response->assertSee('id="bank_account_number"', false);
        $response->assertSee('<label for="bank_name"', false);
        $response->assertSee('id="bank_name"', false);
        $response->assertSee('aria-hidden="true">🔒</span>', false);
        $response->assertDontSee('transition-all', false);
    }
}

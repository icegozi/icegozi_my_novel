<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\LegalConsent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_kyc_registration(): void
    {
        $response = $this->get('/tac-gia/dang-ky-dinh-danh');
        // Guest will be redirected or return 401/302
        $this->assertTrue(in_array($response->status(), [302, 401, 403]));
    }

    public function test_author_can_view_kyc_registration_page(): void
    {
        $user = User::factory()->create(['role' => 'reader']);

        $response = $this->actingAs($user)->get('/tac-gia/dang-ky-dinh-danh');
        $response->assertStatus(200);
        $response->assertSee('Cam Kết Bản Quyền');
        $response->assertSee('Căn Cước Công Dân');
    }

    public function test_validation_rejects_invalid_vietnamese_phone_and_missing_agreements(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tac-gia/dang-ky-dinh-danh', [
            'pen_name' => 'Bút Danh',
            'legal_full_name' => 'Nguyễn Văn A',
            'identity_number' => '12345', // invalid length
            'phone' => '1234567890', // invalid VN prefix
            // missing front/back image
            // missing agree_copyright
        ]);

        $response->assertSessionHasErrors(['identity_number', 'phone', 'identity_front_image', 'identity_back_image', 'agree_copyright']);
    }

    public function test_author_successfully_submits_kyc_with_images_and_legal_consents(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => 'reader']);

        $frontImage = UploadedFile::fake()->image('cccd_front.jpg', 800, 600);
        $backImage = UploadedFile::fake()->image('cccd_back.jpg', 800, 600);

        $response = $this->actingAs($user)->post('/tac-gia/dang-ky-dinh-danh', [
            'pen_name' => 'Hàn Băng Thần Điêu',
            'legal_full_name' => 'Trần Văn C',
            'identity_number' => '079199000123',
            'phone' => '0912345678',
            'identity_issued_date' => '2022-01-15',
            'identity_front_image' => $frontImage,
            'identity_back_image' => $backImage,
            'tax_code' => '8012345678',
            'bank_account_number' => '19030012345678',
            'bank_name' => 'Techcombank',
            'agree_copyright' => '1',
            'agree_terms' => '1',
        ]);

        $response->assertRedirect('/tac-gia/dang-ky-dinh-danh');
        $response->assertSessionHas('success');

        // Check author_profiles record created
        $this->assertDatabaseHas('author_profiles', [
            'user_id' => $user->id,
            'pen_name' => 'Hàn Băng Thần Điêu',
            'legal_full_name' => 'Trần Văn C',
            'verification_status' => 'pending',
        ]);

        $profile = AuthorProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertNotEmpty($profile->identity_number_encrypted);
        $this->assertNotEmpty($profile->identity_hash);

        // Check files stored in private disk
        Storage::disk('local')->assertExists($profile->identity_front_image);
        Storage::disk('local')->assertExists($profile->identity_back_image);

        // Check legal consents recorded
        $this->assertDatabaseHas('legal_consents', [
            'user_id' => $user->id,
            'policy_type' => 'author_copyright_agreement',
            'policy_version' => 'v1.0-2026',
        ]);

        $this->assertDatabaseHas('legal_consents', [
            'user_id' => $user->id,
            'policy_type' => 'terms_of_service',
            'policy_version' => 'v1.0-2026',
        ]);

        // Check user role upgraded to author
        $this->assertEquals('author', $user->fresh()->role);
        $this->assertEquals('0912345678', $user->fresh()->phone);
    }

    public function test_ensure_author_verified_middleware_blocks_unverified_access(): void
    {
        $unverifiedAuthor = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $unverifiedAuthor->id,
            'pen_name' => 'Chưa Duyệt',
            'legal_full_name' => 'Lê Văn B',
            'identity_number_encrypted' => 'enc',
            'identity_hash' => hash('sha256', '000000000001'),
            'verification_status' => 'pending',
        ]);

        // Testing protected author studio route
        $response = $this->actingAs($unverifiedAuthor)->getJson('/tac-gia/studio-check');
        $response->assertStatus(403);

        // Now test verified author
        $verifiedAuthor = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $verifiedAuthor->id,
            'pen_name' => 'Đã Duyệt',
            'legal_full_name' => 'Hoàng Văn D',
            'identity_number_encrypted' => 'enc',
            'identity_hash' => hash('sha256', '000000000002'),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $responseVerified = $this->actingAs($verifiedAuthor)->getJson('/tac-gia/studio-check');
        $responseVerified->assertStatus(200);
        $responseVerified->assertJson(['status' => 'ok']);
    }
}

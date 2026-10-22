<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Models\User;
use App\Services\IdentityEncryptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthorKycReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $applicant;
    protected User $reader;
    protected AuthorProfile $profile;
    protected string $frontPath;
    protected string $backPath;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.identity_pepper' => 'test-pepper-secret-32-chars-long!']);
        Storage::fake('local');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->applicant = User::factory()->create(['role' => 'reader']);
        $this->reader = User::factory()->create(['role' => 'reader']);

        $frontFile = UploadedFile::fake()->image('cccd_front.jpg');
        $backFile = UploadedFile::fake()->image('cccd_back.jpg');

        $this->frontPath = Storage::disk('local')->putFile('private/identity_documents', $frontFile);
        $this->backPath = Storage::disk('local')->putFile('private/identity_documents', $backFile);

        $crypto = new IdentityEncryptionService();
        $cryptoData = $crypto->encryptAndHash('012345678901');

        $this->profile = AuthorProfile::create([
            'user_id' => $this->applicant->id,
            'pen_name' => 'Ngọa Hổ Tàng Long',
            'legal_full_name' => 'Nguyễn Văn Đạt',
            'identity_number_encrypted' => $cryptoData['encrypted'],
            'identity_hash' => $cryptoData['hash'],
            'identity_issued_date' => '2022-01-15',
            'identity_front_image' => $this->frontPath,
            'identity_back_image' => $this->backPath,
            'tax_code' => '849201948',
            'bank_account_number' => '1903482910',
            'bank_name' => 'Techcombank',
            'verification_status' => 'pending',
        ]);
    }

    public function test_admin_can_view_pending_kyc_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kyc.index'));

        $response->assertOk()
            ->assertSee('Ngọa Hổ Tàng Long')
            ->assertSee('Nguyễn Văn Đạt');
    }

    public function test_non_admin_cannot_view_kyc_list(): void
    {
        $response = $this->actingAs($this->reader)->get(route('admin.kyc.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_inspect_kyc_application_and_view_signed_image(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kyc.show', $this->profile));

        $response->assertOk()
            ->assertSee('Ngọa Hổ Tàng Long')
            ->assertSee('012345678901'); // Decrypted CCCD number visible to reviewer

        // Test temporary signed document stream
        $signedUrl = URL::temporarySignedRoute('admin.kyc.document', now()->addMinutes(10), [
            'profile' => $this->profile->id,
            'side' => 'front',
        ]);

        $imageResponse = $this->actingAs($this->admin)->get($signedUrl);
        $imageResponse->assertOk();
    }

    public function test_admin_approval_promotes_user_and_permanently_deletes_cccd_images(): void
    {
        Storage::disk('local')->assertExists($this->frontPath);
        Storage::disk('local')->assertExists($this->backPath);

        $response = $this->actingAs($this->admin)->post(route('admin.kyc.approve', $this->profile));

        $response->assertRedirect(route('admin.kyc.index'));

        $this->profile->refresh();
        $this->assertEquals('verified', $this->profile->verification_status);
        $this->assertNotNull($this->profile->verified_at);
        $this->assertNull($this->profile->identity_front_image);
        $this->assertNull($this->profile->identity_back_image);

        $this->applicant->refresh();
        $this->assertEquals('author', $this->applicant->role);

        // Retention policy: physical files must be permanently deleted under NĐ 13/2023
        Storage::disk('local')->assertMissing($this->frontPath);
        Storage::disk('local')->assertMissing($this->backPath);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'author_kyc_approved',
            'target_type' => 'AuthorProfile',
            'target_id' => $this->profile->id,
        ]);
    }

    public function test_admin_rejection_deletes_cccd_images_and_stores_reason(): void
    {
        Storage::disk('local')->assertExists($this->frontPath);
        Storage::disk('local')->assertExists($this->backPath);

        $response = $this->actingAs($this->admin)->post(route('admin.kyc.reject', $this->profile), [
            'rejection_reason' => 'Ảnh chụp mặt trước CCCD bị mất góc và mờ thông tin ngày cấp.',
        ]);

        $response->assertRedirect(route('admin.kyc.index'));

        $this->profile->refresh();
        $this->assertEquals('rejected', $this->profile->verification_status);
        $this->assertEquals('Ảnh chụp mặt trước CCCD bị mất góc và mờ thông tin ngày cấp.', $this->profile->rejection_reason);
        $this->assertNull($this->profile->identity_front_image);
        $this->assertNull($this->profile->identity_back_image);

        // Retention policy: physical files deleted even on rejection
        Storage::disk('local')->assertMissing($this->frontPath);
        Storage::disk('local')->assertMissing($this->backPath);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'author_kyc_rejected',
            'target_type' => 'AuthorProfile',
            'target_id' => $this->profile->id,
        ]);
    }
}

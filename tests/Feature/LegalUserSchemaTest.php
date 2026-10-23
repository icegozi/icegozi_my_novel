<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\LegalConsent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegalUserSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('users', [
            'id', 'name', 'email', 'phone', 'phone_verified_at',
            'password', 'role', 'status', 'avatar_url', 'remember_token',
            'created_at', 'updated_at',
        ]));
    }

    public function test_author_profiles_table_has_expected_columns_and_indices(): void
    {
        $this->assertTrue(Schema::hasTable('author_profiles'));
        $this->assertTrue(Schema::hasColumns('author_profiles', [
            'id', 'user_id', 'pen_name', 'legal_full_name',
            'identity_number_encrypted', 'identity_hash', 'identity_issued_date',
            'identity_front_image', 'identity_back_image', 'tax_code',
            'bank_account_number', 'bank_name', 'verification_status',
            'rejection_reason', 'verified_at', 'created_at', 'updated_at',
        ]));
    }

    public function test_legal_consents_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('legal_consents'));
        $this->assertTrue(Schema::hasColumns('legal_consents', [
            'id', 'user_id', 'policy_type', 'policy_version',
            'ip_address', 'user_agent', 'signed_at',
        ]));
    }

    public function test_user_relationships_with_author_profile_and_legal_consents(): void
    {
        $user = User::factory()->create([
            'phone' => '0987654321',
            'role' => 'author',
            'status' => 'active',
        ]);

        $profile = AuthorProfile::create([
            'user_id' => $user->id,
            'pen_name' => 'Ngọa Hổ',
            'legal_full_name' => 'Nguyễn Văn A',
            'identity_number_encrypted' => 'encrypted_string',
            'identity_hash' => hash('sha256', '012345678901'),
            'verification_status' => 'pending',
        ]);

        $consent = LegalConsent::create([
            'user_id' => $user->id,
            'policy_type' => 'author_copyright_agreement',
            'policy_version' => 'v1.0-2026',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'signed_at' => now(),
        ]);

        $this->assertInstanceOf(AuthorProfile::class, $user->authorProfile);
        $this->assertEquals($profile->id, $user->authorProfile->id);
        $this->assertTrue($user->legalConsents->contains($consent));
        $this->assertEquals($user->id, $profile->user->id);
        $this->assertEquals($user->id, $consent->user->id);
    }
}

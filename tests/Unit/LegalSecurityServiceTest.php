<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\IdentityEncryptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class LegalSecurityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_identity_encryption_service_encrypts_decrypts_and_computes_hmac_with_pepper(): void
    {
        Config::set('app.identity_pepper', 'custom-test-pepper-12345');

        $service = new IdentityEncryptionService();
        $idNumber = '001234567890';

        $result = $service->encryptAndHash($idNumber);

        $this->assertArrayHasKey('encrypted', $result);
        $this->assertArrayHasKey('hash', $result);
        $this->assertNotEquals($idNumber, $result['encrypted']);

        // Check hash is HMAC-SHA256 with pepper, not plain SHA256
        $expectedHash = hash_hmac('sha256', $idNumber, 'custom-test-pepper-12345');
        $this->assertEquals($expectedHash, $result['hash']);
        $this->assertNotEquals(hash('sha256', $idNumber), $result['hash']);

        // Check decryption
        $decrypted = $service->decrypt($result['encrypted']);
        $this->assertEquals($idNumber, $decrypted);
    }

    public function test_audit_log_service_records_action_and_request_metadata(): void
    {
        $user = User::factory()->create();
        $service = new AuditLogService();

        $log = $service->log(
            user: $user,
            action: 'update_author_kyc',
            type: 'author_profile',
            id: 42,
            old: ['status' => 'pending'],
            new: ['status' => 'verified']
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('update_author_kyc', $log->action);
        $this->assertEquals('author_profile', $log->target_type);
        $this->assertEquals(42, $log->target_id);
        $this->assertEquals(['status' => 'pending'], $log->old_values);
        $this->assertEquals(['status' => 'verified'], $log->new_values);
        $this->assertNotEmpty($log->ip_address);
    }
}

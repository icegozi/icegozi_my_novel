<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class IdentityEncryptionService
{
    /**
     * Encrypt identity number with AES-256 and generate HMAC-SHA256 hash using secret pepper.
     *
     * @param string $idNumber
     * @return array{encrypted: string, hash: string}
     */
    public function encryptAndHash(string $idNumber): array
    {
        $pepper = config('app.identity_pepper');
        if (empty($pepper)) {
            throw new \RuntimeException('Identity pepper is not configured in app.identity_pepper.');
        }

        $encrypted = Crypt::encryptString($idNumber);
        $hash = hash_hmac('sha256', $idNumber, $pepper);

        return [
            'encrypted' => $encrypted,
            'hash' => $hash,
        ];
    }

    /**
     * Decrypt AES-256 encrypted identity number.
     *
     * @param string $encrypted
     * @return string
     */
    public function decrypt(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }
}

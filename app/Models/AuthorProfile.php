<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pen_name',
        'legal_full_name',
        'identity_number_encrypted',
        'identity_hash',
        'identity_issued_date',
        'identity_front_image',
        'identity_back_image',
        'tax_code',
        'bank_account_number',
        'bank_name',
        'verification_status',
        'rejection_reason',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'identity_issued_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

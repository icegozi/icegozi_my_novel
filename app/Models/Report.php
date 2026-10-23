<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reporter_email',
        'reportable_type',
        'reportable_id',
        'violation_category',
        'evidence_urls',
        'description',
        'status',
        'resolution_notes',
        'resolved_by',
        'counter_notice_text',
        'counter_notice_evidence_urls',
        'counter_submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'evidence_urls' => 'array',
            'counter_notice_evidence_urls' => 'array',
            'counter_submitted_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }
}

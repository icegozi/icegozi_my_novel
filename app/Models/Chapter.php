<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'novel_id',
        'chapter_number',
        'title',
        'slug',
        'content',
        'word_count',
        'status',
        'scheduled_at',
        'published_at',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'chapter_number' => 'decimal:2',
            'word_count' => 'integer',
            'view_count' => 'integer',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    public function readingProgresses(): HasMany
    {
        return $this->hasMany(ChapterReadingProgress::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ChapterComment::class);
    }

    public function projections3d(): HasMany
    {
        return $this->hasMany(Chapter3dProjection::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterReadingProgress extends Model
{
    use HasFactory;

    protected $table = 'chapter_reading_progress';

    protected $fillable = [
        'user_id',
        'chapter_id',
        'last_read_paragraph',
        'scroll_percentage',
    ];

    protected function casts(): array
    {
        return [
            'last_read_paragraph' => 'integer',
            'scroll_percentage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}

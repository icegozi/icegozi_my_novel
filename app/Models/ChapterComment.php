<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'user_id',
        'paragraph_index',
        'content',
        'likes_count',
        'is_flagged',
    ];

    protected function casts(): array
    {
        return [
            'paragraph_index' => 'integer',
            'likes_count' => 'integer',
            'is_flagged' => 'boolean',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

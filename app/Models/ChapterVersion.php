<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'revision',
        'title',
        'content',
        'word_count',
        'edited_by',
        'change_summary',
    ];

    protected function casts(): array
    {
        return [
            'revision' => 'integer',
            'word_count' => 'integer',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}

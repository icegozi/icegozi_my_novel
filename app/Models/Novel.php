<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Novel extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'synopsis',
        'cover_image',
        'status',
        'age_rating',
        'copyright_type',
        'copyright_license_notes',
        'copyright_certificate_file',
        'content_warnings',
        'total_words',
        'total_chapters',
        'view_count',
        'bookmark_count',
        'rating_avg',
        'rejection_notes',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'content_warnings' => 'array',
            'approved_at' => 'datetime',
            'rating_avg' => 'decimal:2',
            'total_words' => 'integer',
            'total_chapters' => 'integer',
            'view_count' => 'integer',
            'bookmark_count' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'novel_genre');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function readingHistories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function assets3d(): HasMany
    {
        return $this->hasMany(Novel3dAsset::class);
    }
}

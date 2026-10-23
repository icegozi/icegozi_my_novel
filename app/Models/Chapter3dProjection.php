<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter3dProjection extends Model
{
    use HasFactory;

    protected $table = 'chapter_3d_projections';

    protected $fillable = [
        'chapter_id',
        'asset_3d_id',
        'paragraph_index',
        'trigger_keyword',
        'hologram_title',
        'auto_rotate',
        'display_mode',
    ];

    protected function casts(): array
    {
        return [
            'paragraph_index' => 'integer',
            'auto_rotate' => 'boolean',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function asset3d(): BelongsTo
    {
        return $this->belongsTo(Novel3dAsset::class, 'asset_3d_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Novel3dAsset extends Model
{
    use HasFactory;

    protected $table = 'novel_3d_assets';

    protected $fillable = [
        'novel_id',
        'name',
        'asset_type',
        'model_file_url',
        'thumbnail_url',
        'scale_factor',
        'camera_init_position',
        'ambient_light_color',
        'particle_effect',
        'sound_ambient_url',
        'description',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'camera_init_position' => 'array',
            'scale_factor' => 'decimal:2',
            'is_private' => 'boolean',
        ];
    }

    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    public function projections(): HasMany
    {
        return $this->hasMany(Chapter3dProjection::class, 'asset_3d_id');
    }
}

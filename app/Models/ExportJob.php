<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'filters',
        'columns',
        'status',
        'total_rows',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'columns' => 'array',
            'total_rows' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

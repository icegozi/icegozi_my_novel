<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentFilter extends Model
{
    use HasFactory;

    protected $fillable = [
        'pattern',
        'category',
        'severity',
    ];
}

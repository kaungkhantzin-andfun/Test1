<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable = [
        'name',
        'number',
        'photo',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Astrologer extends Model
{
    protected $fillable = [
        'name',
        'dob',
        'description',
        'type',
        'status',
        'profile_image'
    ];

    protected $casts = [
        'dob' => 'date'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class, 'choice_astrologer', 'id');
    }
}

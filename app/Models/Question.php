<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'category_id',
        'user_id',
        'category_name',
        'name',
        'choice_astrologer',
        'multi_image',
        'dob',
        'textarea',
        'status'
    ];

    protected $casts = [
        'multi_image' => 'array',
        'dob' => 'date'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function astrologer()
    {
        return $this->belongsTo(Astrologer::class, 'choice_astrologer', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'dob',
        'address',
        'image',
        'hour',
        'minute',           
        'profile',
        'password',
        'role',
        'otp',
        'expires_at',
        'is_verified'
    ];

    protected $hidden = [
        'password',     
        'remember_token',
    ];  

    protected $casts = [
        'otp' => 'string',
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'password' => 'hashed',
         'image' => 'array'
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAstrology()
    {
        return $this->role === 'astrology';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function inMoney()
    {
        return $this->hasMany(InMoney::class);
    }

    public function userMoney()
    {
        return $this->hasOne(UserMoney::class);
    }

    public function moneyHistories()
    {
        return $this->hasMany(MoneyHistory::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // <-- Use Authenticatable
use Tymon\JWTAuth\Contracts\JWTSubject; // <-- Use JWTSubject

class Admin extends Authenticatable implements JWTSubject // <-- Implement JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // --- IMPORTANT: Tell Eloquent not to expect created_at/updated_at for Admin table ---
    public $timestamps = false;

    // JWTSubject methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
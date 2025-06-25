<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class CustomerDetail extends Authenticatable
{
    protected $table = 'customer_details';

    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false; // set to true if you do have created_at/updated_at

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email' => 'string',
        'phone_number' => 'integer',
    ];
}

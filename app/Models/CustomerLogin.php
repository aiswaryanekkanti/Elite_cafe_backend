<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLogin extends Model
{
    protected $table = 'customer_logins';

    protected $fillable = [
        'customer_email',
        'login_at',
        'logout_at',
        'ip_address',
        'device',
    ];

    // Optional: If you're using timestamps like created_at/updated_at
    public $timestamps = true;

    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_email', 'email');
    }
}

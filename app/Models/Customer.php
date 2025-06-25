<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer_details'; // change to your actual table name

    protected $fillable = [
        'first_name', 'last_name', 'phone_number', 'email', 'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_email', 'email');
    }
}

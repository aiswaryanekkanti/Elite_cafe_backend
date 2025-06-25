<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
 protected $table = 'orders';

 protected $fillable = [
 'customer_email', 'name', 'phone', 'type', 'status',
 'total', 'payment_method', 'pincode', 'locality', 'address'
 ];
 public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_email', 'email');
    }
}

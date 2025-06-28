<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class OrderInfo extends Model
{
    protected $table = 'order_items';
 
    public $timestamps = false; // Disable timestamps if the table doesn't have created_at and updated_at
 
    protected $fillable = [
        'cust_id',
        'order_id',
        'qty',
    ];
 
   
 
   
}
 
 
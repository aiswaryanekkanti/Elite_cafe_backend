<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'item_name',
        'item_price',
        'quantity',
    ];

    public $timestamps = false; // Assuming no created_at/updated_at for this table either

    // Define relationship with Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Define relationship with MenuItem (optional, can be null if original menu item deleted)
    // public function menuItem()
    // {
    //     return $this->belongsTo(MenuItem::class);
    // }
    public function menuItem()
{
    return $this->belongsTo(MenuItem::class, 'item_id', 'id');
}

}
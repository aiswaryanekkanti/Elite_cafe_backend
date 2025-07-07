<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class CustOrderInfo extends Model
{
    protected $table = 'orders'; // Optional if table name matches plural of model name
 
    protected $fillable = [
        'id',
        'customer_email',
        'name',
        'phone',
        'type',
        'status',
        'total',
        'payment_method',
        'pincode',
        'locality',
        'address',
    ];
 
    // If you want Laravel to treat `created_at` and `updated_at` as Carbon instances
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    public function orderItems()
{
    return $this->hasMany(OrderItem::class, 'order_id', 'id');
}
}
 
 
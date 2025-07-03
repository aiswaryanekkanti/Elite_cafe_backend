<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class TableInfo extends Model
{
   
    protected $table = 'reservations';
 
   
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'persons',
        'reservation_date',
        'reservation_time',
        'message',
    ];
 
    // Optional: timestamps if not using created_at / updated_at
    public $timestamps = false;
}
 
 
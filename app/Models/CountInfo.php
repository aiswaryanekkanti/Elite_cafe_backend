<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class CountInfo extends Model
{
     protected $table = 'table_reservation_count';
 
   
    protected $fillable = [
     'seats',
     'time_slot',
     'count'
    ];
 
    // Optional: timestamps if not using created_at / updated_at
    public $timestamps = false;
}
 
 
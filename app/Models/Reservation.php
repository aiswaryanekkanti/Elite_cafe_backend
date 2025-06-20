<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $fillable = [
        'table_id', 'guest_name', 'guest_email', 'guest_phone', 
        'guest_count', 'reservation_date', 'time_slot'
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}

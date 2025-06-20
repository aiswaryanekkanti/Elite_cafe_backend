<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = ['table_name', 'capacity', 'status'];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}

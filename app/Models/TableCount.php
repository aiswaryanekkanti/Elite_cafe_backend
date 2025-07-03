<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Often useful, even for simple models
use Illuminate\Database\Eloquent\Model;

/**
 * Class TableCount
 * Represents aggregated reservation counts per seat type and time slot.
 * Corresponds to the 'table_reservation_count' table in your database schema.
 */
class TableCount extends Model
{
    use HasFactory; // Add this if you plan to use model factories for testing/seeding

    // Define the table name if it's not the plural form of the model name
    protected $table = 'table_reservation_count';

    // Define the fillable attributes if you plan to create/update records using mass assignment
    // Based on your screenshot, the columns are 'seats', 'time_slot', 'count'
    protected $fillable = [
        'seats',
        'time_slot',
        'count',
    ];

    // If your 'table_reservation_count' table does NOT have 'created_at' and 'updated_at' columns,
    // you must set timestamps to false.
    public $timestamps = false;

    // You might also want to define casts for 'time_slot' if it's a specific time format
    // protected $casts = [
    //     'time_slot' => 'datetime:H:i:s', // Example: if stored as '11:00:00'
    // ];
}
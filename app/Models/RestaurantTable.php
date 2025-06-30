<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
/**
 * Class RestaurantTable
 * Represents a physical table in the restaurant.
 * Corresponds to the 'tables' table in your database schema.
 */
class RestaurantTable extends Model
{
    use HasFactory;
 
    // Define the table name if it's not the plural form of the model name
    protected $table = 'tables';
 
    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'name',     // e.g., 'Table Two 1', 'Table Four 5'
        'capacity', // Integer, e.g., 2, 4, 5
    ];
 
    /**
     * Get the reservations associated with this table.
     * A RestaurantTable can have many Reservations over time.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'assigned_table_id');
    }
}
 
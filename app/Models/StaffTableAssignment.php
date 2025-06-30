<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
class StaffTableAssignment extends Model
{
    use HasFactory;
 
    protected $table = 'staff_table_assignments';
 
    protected $fillable = [
        'table_id',
        'assignment_date',
        'assignment_time',
        'waiter_id',
        'manager_id',
        'cleaner_id'
 
 
    ];
 
    protected $casts = [
        'assignment_date' => 'date',
        'assignment_time' => 'datetime',
    ];
 
    /**
     * Get the staff member associated with the assignment.
     */
    public function waiter()
{
    return $this->belongsTo(Staff::class, 'waiter_id');
}
 
public function manager()
{
    return $this->belongsTo(Staff::class, 'manager_id');
}
 
public function cleaner()
{
    return $this->belongsTo(Staff::class, 'cleaner_id');
}
 
 
    /**
     * Get the restaurant table associated with the assignment.
     */
    public function restaurantTable()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id', 'id');
    }
}
 
 
<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Add this trait for deleted_at
 
class Staff extends Model
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait
 
    protected $table = 'staff';
 
    // Specify the primary key if it's not 'id'
    protected $primaryKey = 'staff_id';
 
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone', // Add phone
        'address', // Add address
        'role', // 'waiter', 'manager', 'cleaner', 'chef'
    ];
 
    /**
     * Get the table assignments for the staff member.
     */
    public function tableAssignments()
    {
        // Use 'staff_id' as the foreign key in the pivot table
        return $this->hasMany(StaffTableAssignment::class, 'staff_id', 'staff_id');
    }
 
    /**
     * Get the tables assigned to this staff member through assignments.
     */
    public function assignedTables()
    {
        // Adjust foreign keys for the pivot table if 'staff_id' is used
        return $this->belongsToMany(RestaurantTable::class, 'staff_table_assignments', 'staff_id', 'restaurant_table_id')
                    ->withPivot('assignment_date', 'shift_type')
                    ->withTimestamps();
    }
}
 
 
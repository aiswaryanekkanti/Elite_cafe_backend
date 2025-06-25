<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff';

    protected $primaryKey = 'staff_id';

    public $incrementing = true;

    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'role',
    ];

    /**
     * The attributes that should be mutated to dates.
     * (Optional in Laravel 8+ as SoftDeletes handles this)
     *
     * @var array<int, string>
     */
    protected $dates = ['deleted_at'];
}

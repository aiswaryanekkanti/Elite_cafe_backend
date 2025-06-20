<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menu_items'; // Explicitly set the table name

    protected $fillable = [
        'name',
        'price',
        'img',
        'category',
        'subcategory',
        'veg',
        'description',
    ];

    // Cast the 'veg' attribute to a boolean
    protected $casts = [
        'veg' => 'boolean',
    ];
}
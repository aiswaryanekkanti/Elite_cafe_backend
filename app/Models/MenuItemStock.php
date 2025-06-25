<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemStock extends Model
{
    protected $table = 'menu_items_stocks';

    protected $fillable = [
        'menu_item_id',
        'is_available',
        'quantity',
        'updated_at',
    ];

    public $timestamps = false;

    public function menuItem()
    {
        return $this->belongsTo(MenuInfo::class, 'menu_item_id');
    }
}

<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class MenuInfo extends Model
{
    // If your table name is not the plural of model, specify:
     protected $table = 'menu_items';
 
    // Primary key is 'id' by default, no need to specify unless different
 
    // If your primary key is not auto-incrementing integer, specify:
    // public $incrementing = true;
    // protected $keyType = 'int';
 
    // Allow mass assignment on these columns
    protected $fillable = [
        'name',
        'price',
        'img',
        'category',
        'subcategory',
        'veg',
        'description',
        'is_deleted',
    ];
 
    // If you want to disable timestamps (created_at, updated_at)
     public $timestamps = false;

//      public function stock()
// {
//     return $this->hasOne(MenuItemStock::class, 'menu_item_id');
// }
public function menuItem()
{
    return $this->belongsTo(MenuItem::class, 'item_id', 'id');
}

}
 
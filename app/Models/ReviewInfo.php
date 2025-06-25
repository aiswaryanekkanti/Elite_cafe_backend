<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class ReviewInfo extends Model
{
    public $timestamps = false;
    protected $table = 'review';
    protected $fillable = ['first_name', 'last_name', 'review'];
}
 

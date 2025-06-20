<?php
 
namespace App\Models;
 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
 
class UserInfo extends Authenticatable implements JWTSubject
{
    public $timestamps = false;
    protected $table = 'customer_details';
    protected $primaryKey = 'email';
    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'password',
    ];
 
    protected $hidden = [
        'password',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();  // usually the primary key (id)
    }
 
    /**
     * Return a key value array, containing any custom claims to be added to JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
 
 
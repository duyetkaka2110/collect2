<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'm_users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $dateFormat = 'Y/m/d H:i:s';
    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function mmst() 
    { 
       return $this->belongsTo('App\Models\pldtcustmmst', 'CUSTCD'); 
    } 
}

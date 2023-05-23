<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserAsU extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'm_users as u';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    // use Compoships;
    // const CREATED_AT = 'AddDate';
    // const UPDATED_AT = 'UpdateDate';
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
}

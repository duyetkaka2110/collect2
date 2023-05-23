<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class LoginLog extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 't_login_logs';
    protected $primaryKey = 'login_log_id';
    public $incrementing = true;

    protected $dateFormat = 'Y/m/d H:i:s';
    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';
}

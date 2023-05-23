<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Status extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'm_statuses';
    protected $primaryKey = 'status_id';
    public $incrementing = false;

    protected $dateFormat = 'Y/m/d H:i:s';
}

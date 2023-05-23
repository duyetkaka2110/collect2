<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class t_dispatches extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 't_dispatches';
    protected $primaryKey = 'dispatch_id';

    protected $dateFormat = 'Y/m/d H:i:s';
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class t_dispatch_detailsAsDD extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 't_dispatch_details AS DD';
    protected $primaryKey = 'dispatch_detail_id';

    protected $dateFormat = 'Y/m/d H:i:s';
}

<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class RequestsDetail extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 't_request_details';
    protected $primaryKey = 'request_detail_id';
    public $incrementing = true;

    protected $dateFormat = 'Y/m/d H:i:s';
}

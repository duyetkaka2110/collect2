<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class t_carry_ins extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 't_carry_ins';
    public $incrementing = false;
}

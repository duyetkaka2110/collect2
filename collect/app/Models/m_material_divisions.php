<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class m_material_divisions extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'm_material_divisions AS MD';
    public $incrementing = false;

    protected $dateFormat = 'Y/m/d H:i:s';
}

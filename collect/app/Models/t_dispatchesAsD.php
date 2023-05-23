<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class t_dispatchesAsD extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 't_dispatches AS D';
    protected $primaryKey = 'dispatch_id';

    protected $dateFormat = 'Y/m/d H:i:s';
    
    public function detail() 
    { 
       return $this->belongsTo('App\Models\t_dispatch_details', 'dispatch_id'); 
    } 
}

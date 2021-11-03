<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'name', 'email', 'password', 'id_unidade','status'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'id_unidade');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motorista extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'nome', 'telefone', 'id_unidade', 'access_key',
    ];

    public function viagens()
    {
        return $this->hasMany(Viagem::class, 'id_motorista');
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'id_unidade');
    }
}

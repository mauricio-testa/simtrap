<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $primaryKey = 'codigo';

    public function viagens()
    {
        return $this->hasMany(Viagem::class, 'cod_destino');
    }
}

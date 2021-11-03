<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'descricao', 'placa', 'id_unidade', 'lotacao', 'tipo', 'ano_modelo', 'marca_modelo'
    ];

    public function viagens()
    {
        return $this->hasMany(Viagem::class, 'id_veiculo');
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'id_unidade');
    }
}

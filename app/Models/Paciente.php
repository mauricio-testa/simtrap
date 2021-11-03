<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'rg', 'nome', 'telefone', 'endereco', 'id_unidade', 'codigo_municipio', 'sus', 'status'
    ];

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'id_unidade');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'codigo_municipio', 'codigo');
    }

    public function listas()
    {
        return $this->hasMany(Lista::class, 'id_paciente');
    }

    public function viagens()
    {
        return $this->belongsToMany(Viagem::class, 'lista', 'id_paciente', 'id_viagem');
    }

    public function scopeActives($query)
    {
        return $query->where('status', 1);
    }
}

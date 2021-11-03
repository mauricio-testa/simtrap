<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\ViewLista;

class Lista extends Model
{
    protected $table = 'lista';

    public $timestamps = false;

    protected $fillable = [
        'id_paciente','id_viagem', 'acompanhante_rg', 'acompanhante_nome', 'consulta_local', 'consulta_observacao', 'consulta_hora'
    ];

    public function viagem()
    {
        return $this->belongsTo(Viagem::class, 'id_viagem', 'id');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id');
    }

    public static function getViagemList($id_viagem) {
        return self::select ('lista.*', 'pa.nome as paciente_nome')
            ->leftJoin('pacientes as pa', 'pa.id', '=','lista.id_paciente')
            ->where('lista.id_viagem', $id_viagem)
            ->get();
    }
}

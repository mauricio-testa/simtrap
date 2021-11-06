<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Unidade extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'descricao', 'id_municipio', 'status', 'responsavel'
    ];

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class, 'id_unidade');
    }

    public function motoristas()
    {
        return $this->hasMany(Motorista::class, 'id_unidade');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_unidade');
    }

    public function viagens()
    {
        return $this->hasMany(Viagem::class, 'id_unidade');
    }

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'id_unidade');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'id_municipio');
    }

    public static function UnidadeIsActiveByUserEmail($email) {

        $select = DB::table('unidades')
            ->select('unidades.status')
            ->join('users', 'users.id_unidade', '=','unidades.id')
            ->where('users.email', $email)
            ->first();

        return $select->status;
    }

    public static function GetUnidadeByUserEmail($email) {
        $select = DB::table('unidades')
            ->select('unidades.descricao', 'municipios.nome', 'municipios.uf')
            ->join('users', 'users.id_unidade', '=','unidades.id')
            ->join('municipios','unidades.id_municipio', '=', 'municipios.codigo')
            ->where('users.email', $email)
            ->first();

        return $select;
    }
}

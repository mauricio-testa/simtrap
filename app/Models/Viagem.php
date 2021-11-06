<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ViewViagem;
use App\Observers\ViagemObserver;
use Illuminate\Support\Facades\DB;

class Viagem extends Model
{
    protected $table = 'viagens';

    public $timestamps = false;

    protected $fillable = [
        'id_veiculo', 'id_motorista', 'cod_destino', 'data_viagem', 'hora_saida', 'observacao', 'id_unidade'
    ];

    protected $casts = [
        'data_viagem' => 'datetime:d-m-Y',
    ];

    public function lista()
    {
        return $this->hasMany(Lista::class, 'id_viagem', 'id');
    }

    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'lista', 'id_viagem', 'id_paciente');
    }

    public function motorista()
    {
        return $this->belongsTo(Motorista::class, 'id_motorista', 'id');
    }

    public function destino()
    {
        return $this->belongsTo(Municipio::class, 'cod_destino', 'codigo');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id');
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'id_unidade', 'id');
    }

    public static function boot() {
        parent::boot();

        Viagem::observe(ViagemObserver::class);
    }

    public static function getViagem ($id_unidade = null, $wheres = [], $onlyFirst = false) {

        // initialize query
        $query = ViewViagem::whereNotNull('id');

        // parametro id unidade
        if (!is_null($id_unidade)) {
            $query->where('id_unidade', $id_unidade);
        }

        // query dinamica and
        foreach ($wheres as $where) {
            $query->where($where[0], $where[1], $where[2]);
        }

        // retorna objeto ou array
        if ($onlyFirst) {
            return $query->first();
        } else {
            return $query->orderBy('data_viagem', 'desc')->paginate(config('constants.PAGINATION_SIZE'));
        }
    }


    public static function canUpdateVeiculoTo($newVeiculo, $idViagem) {

        $passageiros = Lista::where('id_viagem', $idViagem)->count();
        $acompanhantes = Lista::where('id_viagem', $idViagem)->whereNotNull('acompanhante_nome')->count();
        $newLotacao = Veiculo::find($newVeiculo)->lotacao;

        return $newLotacao >= ($passageiros + $acompanhantes);

    }
}

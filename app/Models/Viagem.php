<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ViewViagem;
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

        $query = self::select('viagens.id',
                DB::raw("(SELECT count(*) FROM lista WHERE id_viagem = $idViagem) AS passageiros"),
                DB::raw("(SELECT count(*) FROM lista WHERE id_viagem = $idViagem AND acompanhante_nome IS NOT NULL) AS acompanhantes"),
                DB::raw("(SELECT lotacao FROM veiculos WHERE id = $newVeiculo) AS lotacao"));

        $result = $query->first();

        return $result->lotacao >= ($result->passageiros + $result->acompanhantes);

    }
}

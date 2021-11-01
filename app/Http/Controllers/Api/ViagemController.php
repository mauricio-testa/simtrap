<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Viagem;
use App\Http\Controllers\Helpers\ErrorInterpreter;
use Illuminate\Support\Facades\Auth;
use App\Models\ViewViagem;
use App\Http\Controllers\Helpers\Log;

class ViagemController extends Controller
{
    protected $table = 'viagens';

    public function index(Request $request)
    {
        try {

            if (empty($request->search)) {
                return Viagem::getViagem(Auth::user()->id_unidade);

            } else {
                // aqui é google papai \o/
                $like = '%'.$request->search.'%';
                $query = ViewViagem::where('municipio_nome', 'LIKE', $like)
                            ->orWhere('veiculo', 'LIKE', $like)
                            ->orWhere('motorista_nome', 'LIKE', $like)
                            ->orWhere('observacao', 'LIKE', $like)
                            ->orWhere('data_formated', '=' , $request->search);
                return $query->paginate(config('constants.PAGINATION_SIZE'));
            }

        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    public function store(Request $request)
    {
        try {

            $viagem = $request->all();
            $viagem['id_unidade'] = Auth::user()->id_unidade;
            $last_id = Viagem::insertGetId($viagem);
            Log::CRUDInsert($this->table, $last_id, "Viagem inserida", $viagem);

            // retorna o id inserido para abrir automaticamente modal de lista
            return response()->json(['id' => $last_id]);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }


    public function update(Request $request, $id)
    {
        try {

            $viagem = Viagem::findOrFail($id);

            // se mudar de veiculo, verifica se possui vagas suficientes
            if (!Viagem::canUpdateVeiculoTo($request->id_veiculo, $id))
            throw new \Exception("Este veículo não tem vagas suficientes para o número de passageiros cadastrados nesta lista!");

            $viagem->update($request->all());
            Log::CRUDUpdate($this->table, $id, "Viagem alterada", $viagem);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $viagem = Viagem::findOrFail($id);
            Log::CRUDDelete($this->table, $id, "Viagem deletada", $viagem->toArray());
            $viagem->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }
}

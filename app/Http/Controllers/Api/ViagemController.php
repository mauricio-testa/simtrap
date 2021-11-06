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

            $data = $request->all();
            $data['id_unidade'] = Auth::user()->id_unidade;

            $viagem = Viagem::create($data);

            // retorna o id inserido para abrir automaticamente modal de lista
            return response()->json(['id' => $viagem->id]);

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
            $viagem->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Veiculo;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Helpers\ErrorInterpreter;


class VeiculoController extends Controller
{

    public function index(Request $request)
    {
        try {
            $query = Veiculo::whereBelongsTo(Auth::user()->unidade);

            $limit = config('constants.PAGINATION_SIZE');

            if(!empty($request->search))
            $query->where('descricao', 'like', '%'.$request->search.'%');

            if(!empty($request->limit))
            $limit = $request->limit;

            return $query->paginate($limit);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }


    public function store(Request $request)
    {
        try {
            $veiculo = $request->all();
            $veiculo['id_unidade'] = Auth::user()->id_unidade;
            Veiculo::create($veiculo);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th, ['23000' => ['1062' => 'Já existe um veículo cadastrado com essa placa!']])
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $veiculo = Veiculo::findOrFail($id);
            $veiculo->update($request->all());
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th, ['23000' => ['1062' => 'Já existe um veículo cadastrado com essa placa!']])
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $veiculo = Veiculo::findOrFail($id);
            $veiculo->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th, ['23000' => ['1451' => 'Você não pode deletar este veiculo pois existem viagens cadastradas para ela!']])
            ]);
        }
    }
}

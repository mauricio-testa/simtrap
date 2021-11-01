<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Unidade;
use App\Http\Controllers\Helpers\ErrorInterpreter;


class UnidadeController extends Controller
{

    public function index()
    {
        try {
            $query = Unidade::select('*','mu.nome as municipio_nome')
            ->leftJoin('municipios as mu', 'unidades.id_municipio','=','mu.codigo');
            return $query->get();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }


    public function store(Request $request)
    {
        try {
            $unidade = $request->all();
            Unidade::create($unidade);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $unidade = Unidade::findOrFail($id);
            $unidade->update($request->all());
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $unidade = Unidade::findOrFail($id);
            $unidade->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Lista;
use App\Http\Controllers\Helpers\ErrorInterpreter;
use App\Http\Controllers\Helpers\Log;


class ListaController extends Controller
{
    public function index(Request $request)
    {
        try {
            return Lista::where('id_viagem', $request->viagem)->with('paciente')->get();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }


    public function store(Request $request)
    {
        try {
            $passageiro = $request->all();
            Lista::create($passageiro);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th, ['23000' => ['1062' => 'Este passageiro já está nessa lista']])
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            unset($data['paciente_nome']);
            $passageiro = Lista::find($id);
            $passageiro->update($data);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $passageiro = Lista::find($id);
            $passageiro->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }
}

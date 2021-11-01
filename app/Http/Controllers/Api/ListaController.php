<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Lista;
use App\Http\Controllers\Helpers\ErrorInterpreter;
use App\Http\Controllers\Helpers\Log;


class ListaController extends Controller
{
    protected $table = 'lista';

    public function index(Request $request)
    {
        try {
            return Lista::getViagemList($request->viagem);
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
            Log::CRUDInsert($this->table, $passageiro['id_viagem'], "Passageiro inserido na lista", $passageiro);
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
            $passageiro = Lista::where('id_viagem', $id)->where('id_paciente', $request->id_paciente);
            $passageiro->update($data);
            Log::CRUDUpdate($this->table, $data['id_viagem'], "Atualização de passageiro na lista", $data);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $passageiro = Lista::where('id_viagem', $request->viagem)->where('id_paciente', $id);
            Log::CRUDDelete($this->table, $id, "Passageiro removido da lista", $passageiro->first()->toArray());
            $passageiro->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }
}

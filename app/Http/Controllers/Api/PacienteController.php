<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\Unidade;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Helpers\ErrorInterpreter;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Paciente::whereBelongsTo(Auth::user()->unidade);

            if(!empty($request->search))
            $query->where('nome', 'like', '%'.$request->search.'%');

            if($request->only_active)
            $query->actives();

            return $query->paginate(config('constants.PAGINATION_SIZE'));

        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }


    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $paciente = $request->all();
            $paciente['id_unidade'] = $user->id_unidade;
            $paciente['codigo_municipio'] = $user->unidade->id_municipio;
            Paciente::create($paciente);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th, ['23000' => ['1062' => 'Já existe um paciente com este RG!']])
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->update($request->all());
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th, ['23000' => ['1062' => 'Já existe um paciente com este RG!']])
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th, ['23000' => ['1451' => 'Você não pode deletar este paciente pois existem viagens cadastradas para ele!']])
            ]);
        }
    }
}

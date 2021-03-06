<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Motorista;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Helpers\ErrorInterpreter;


class MotoristaController extends Controller
{

    public function index(Request $request)
    {
        try {

            $query = Motorista::whereBelongsTo(Auth::user()->unidade);
            $limit = config('constants.PAGINATION_SIZE');

            if(!empty($request->search))
            $query->where('nome', 'like', '%'.$request->search.'%');

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
            $motorista = $request->all();
            $motorista['access_key'] = $this->validateAccessKey($motorista['access_key']);
            Auth::user()->unidade->motoristas()->create($motorista);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $data['access_key'] = $this->validateAccessKey($data['access_key']);
            $motorista = Motorista::findOrFail($id);
            $motorista->update($data);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $motorista = Motorista::findOrFail($id);
            $motorista->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th, ['23000' => ['1451' => 'Voc?? n??o pode deletar este motorista pois existem viagens cadastradas para ele']])
            ]);
        }
    }

    // deve ter pelo menos 4 caracteres
    private function validateAccessKey ($ak) {
        return $ak > 1000 ? $ak : rand(1000, 9999);
    }
}

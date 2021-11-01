<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ErrorInterpreter;
use App\Models\Municipio;

class MunicipioController extends Controller
{

    public function index(Request $request)
    {
        try {

            $limit = config('constants.PAGINATION_SIZE');

            if(!empty($request->limit))
            $limit = $request->limit;

            return Municipio::where('uf', 'RS')->paginate($request->limit);

            // 500 é o número máximo de município que os 3 estados do sul tem
            // Se o sistema for pra fora do RS, basta alterar RS pela UF da unidade

        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }
}

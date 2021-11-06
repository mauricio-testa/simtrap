<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ErrorInterpreter;
use App\Models\Municipio;
use Illuminate\Support\Facades\Auth;

class MunicipioController extends Controller
{

    public function index(Request $request)
    {
        try {
            return Municipio::where('uf', Auth::user()->unidade->municipio->uf)->paginate($request->limit);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers\Helpers\ErrorInterpreter;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\Admin;


class UsuarioController extends Controller
{
    public function __construct()
    {
        // todos usuarios podem update senha, mas só os admins podem fazer o resto
        $this->middleware(Admin::class)->except(['update']);
    }

    public function index(Request $request)
    {
        try {
            return User::where('id_unidade', $request->unidade)->get();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }


    public function store(Request $request)
    {
        try {
            $usuario = $request->all();
            $usuario['password'] = bcrypt($usuario['password']);
            User::create($usuario);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $usuario = $request->all();
            if (isset($request->password)) {
                $usuario['password'] = bcrypt($request['password']);
            }
            if (isset($request->status)) {
                $usuario['status'] = $request['status'] ? 1 : 0;
            }
            $user = User::findOrFail($id);
            $user->update($usuario);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            if ($id == Auth::user()->id_unidade){
                throw new \Exception("Você não pode se deletar né");
            }
            $unidade = User::findOrFail($id);
            $unidade->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }
}

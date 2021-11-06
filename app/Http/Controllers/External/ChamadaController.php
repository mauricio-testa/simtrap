<?php

namespace App\Http\Controllers\External;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Lista;
use App\Models\Motorista;
use App\Models\Viagem;
use Illuminate\Support\Facades\Auth;

class ChamadaController extends Controller
{
    public function index (Request $request) {
        if ($request->isMethod('post')) {
            $this->updateAbsenteism($request->viagem, $request->lista);
        } else {
            $passageiros = Lista::where('id_viagem', $request->viagem)->with('paciente')->get();
            return view('external.chamada', [
                'lista'         => $passageiros,
                'viagem'        => Viagem::where('id', $request->viagem)->first(),
                'authenticated' => (int) Auth::check()
            ]);
        }
    }

    public function autenticar (Request $request) {

        $motorista = Viagem::find($request->viagem)->motorista;

        if ($motorista->access_key == $request->access_key) {
            return response()->json(
                ['success' => 'true']
            );
        } else {
            return response()->json([
                'success' => 'false',
                'message' => 'Esta chave de acesso está incorreta ou não pertence ao motorista dessa viagem'
            ]);
        }
    }

    private function updateAbsenteism ($viagem, $compareceram) {

        // atualiza tudo para nao
        Lista::where('id_viagem', $viagem)->update(['compareceu' => 'NAO']);

        // os que vieram no array é porque compareceram
        Lista::whereIn('id', $compareceram)->update(['compareceu' => 'SIM']);

        // atualizar viagem como concluida
        Viagem::where('id', $viagem)->update(['status' => 'CONCLUIDA']);
    }
}

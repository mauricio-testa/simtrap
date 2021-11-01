<?php

namespace App\Http\Controllers\External;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Lista;
use App\Models\Viagem;
use Illuminate\Support\Facades\Auth;

class ChamadaController extends Controller
{
    public function index (Request $request) {
        if ($request->isMethod('post')) {
            $this->updateAbsenteism($request->viagem, $request->lista);
        } else {
            $passageiros = Lista::getViagemList($request->viagem);
            return view('external.chamada', [
                'lista'         => $passageiros,
                'viagem'        => Viagem::where('id', $request->viagem)->first(),
                'authenticated' => (int) Auth::check()
            ]);
        }
    }

    public function authenticate (Request $request) {

        $query = Viagem::select()
            ->leftJoin('motoristas', 'motoristas.id','=','viagens.id_motorista')
            ->where('motoristas.access_key', $request->access_key)
            ->where('viagens.id', $request->viagem)->count();

        if ($query == 1) {
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

        $lista = json_decode($compareceram);

        // atualiza tudo para nao
        Lista::where('id_viagem', $viagem)->update(['compareceu' => 'NAO']);

        // os que vieram no array é porque compareceram
        foreach ($lista as $key => $value) {
            Lista::where('id_viagem', $value->id_viagem)
                ->where('id_paciente', $value->id_paciente)
                ->update(['compareceu' => 'SIM']);;
        }

        // atualizar viagem como concluida
        Viagem::where('id', $viagem)->update(['status' => 'CONCLUIDA']);
    }
}

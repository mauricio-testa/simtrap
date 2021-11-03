<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Lista;
use App\Models\Motorista;
use App\Models\Municipio;
use App\Models\Paciente;
use App\Models\Unidade;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;

class TestController extends Controller
{
    public function relacoes(){
        $viagem = Viagem::with(
            'lista',
            'lista.paciente',
            'pacientes',
            'motorista',
            'destino',
            'veiculo',
            'unidade'
        )->find(1);

        dd(
            ' ****** VIAGEM COM SUAS RELACOES ******',
            $viagem->toArray(),
            ' ****** MOTORISTA ******',
            Motorista::with('viagens', 'unidade')->first()->toArray(),
            ' ****** Veiculo ******',
            Veiculo::with('viagens', 'unidade')->first()->toArray(),
            ' ****** Municipio ******',
            Municipio::with('viagens')->first()->toArray(),
            ' ****** Unidade ******',
            Unidade::with('motoristas', 'veiculos', 'usuarios', 'pacientes', 'viagens')->first()->toArray(),
            ' ****** User ******',
            User::with('unidade')->first()->toArray(),
            ' ****** Paciente ******',
            Paciente::with('unidade', 'municipio', 'listas', 'viagens')->first()->toArray(),
            ' ****** Lista ******',
            Lista::with('viagem', 'paciente')->first()->toArray(),
        );
    }
}

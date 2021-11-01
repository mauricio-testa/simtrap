@extends('layouts.report')

@section('title', "LISTA DE VIAGEM #  $viagem->id")

@section('content')

<div class="report report__lista">

    <div class="infos">
        <ul>
            <li><span>Data: </span>{{ $viagem->data_formated }}</li>
            <li><span>Horário de saída: </span>{{ date('H:i', strtotime($viagem->hora_saida)) }}</li>
            <li><span>Destino: </span>{{ $viagem->municipio_nome }}</li>
            <li><span>Motorista: </span>{{ $viagem->motorista_nome }}</li>
            <li><span>Veículo: </span>{{ $viagem->veiculo }}</li>
            <li><span>Passageiros: </span>{{ sizeof($lista)}} ({{ $viagem->lotacao }} lugares)</li>
        </ul>
        {!! QrCode::size(100)->generate($chamada); !!}
    </div>

    <table border="1" width="100%" cellspacing=0 cellpadding=8>
        <thead>
            <th colspan="3">Detalhes do Passageiro</th>
            <th colspan="4">Detalhes da Consulta</th>
        </thead>
        <thead>
            <td>Nome</td>
            <td>RG</td>
            <td>Telefone</td>
            <td>Local</td>
            <td>Horário</td>
            <td>Obs</td>
            <td>X</td>
        </thead>
        <tbody>
        @foreach ($lista as $passageiro)
            @if ($passageiro->acompanhante)
                <tr>
                    <td>{{ $passageiro->nome }}</td>
                    <td>{{ $passageiro->rg }}</td>
                    <td colspan="4" class="acompanhante"><span>{{ $passageiro->acompanhante_desc }}</span></td>
                    <td></td>
                </tr>
            @else
                <tr>
                    <td>{{ $passageiro->nome }}</td>
                    <td>{{ $passageiro->rg }}</td>
                    <td>{{ $passageiro->telefone }}</td>
                    <td>{{ $passageiro->consulta_local }}</td>
                    <td>{{ $passageiro->consulta_hora }}</td>
                    <td>{{ $passageiro->consulta_observacao }}</td>
                    <td></td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
    <div class="info">
        <b>Chamada:</b> <span style="font-weight: bold">{{ $chamada }}</span>
    </div>
    @if ($viagem->observacao)
        <div class="info observacoes">
            <b>Observações:</b><br>
            {!! nl2br(e($viagem->observacao)) !!}
        </div>
    @endif
</div>
@endsection
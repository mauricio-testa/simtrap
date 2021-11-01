@extends('layouts.public')

@section('content')

<chamada
    :lista="{{ json_encode($lista) }}"
    :viagem="{{ json_encode($viagem) }}"
    :authenticated="{{$authenticated}}"
></chamada>

@endsection

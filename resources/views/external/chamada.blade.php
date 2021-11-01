@extends('layouts.public')

@section('content')

<chamada 
    :lista="{{ htmlspecialchars(json_encode($lista))}}" 
    :viagem="{{ htmlspecialchars(json_encode($viagem))}}"
    :authenticated="{{$authenticated}}"
></chamada>

@endsection

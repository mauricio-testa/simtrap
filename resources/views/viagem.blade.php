@extends('layouts.app')

@section('content')

<viagens :default_date="{{ json_encode(date('Y-m-d'))}}"  ></viagens>

@endsection
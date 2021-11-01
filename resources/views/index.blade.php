@extends('layouts.app')

@section('content')

<dashboard :data="{{ json_encode($data) }}"></dashboard>

@endsection

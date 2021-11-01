@extends('layouts.public')

@section('content')

<auth-login error="{{ session('login_error') }}"></auth-login>

@endsection

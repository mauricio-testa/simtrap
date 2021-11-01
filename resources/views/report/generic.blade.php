@extends('layouts.report')

@section('title', $title)

@section('content')

<div class="report">

    <div class="infos">
        <ul>
            @foreach ($infos as $info)
            <li><span>{{ $info['label'] }}: </span>{{ $info['value'] }}</li>
            @endforeach
        </ul>
    </div>

    <table border="1" width="100%" cellspacing=0 cellpadding=8>
        <thead>
            @foreach ($headers as $header)
                <td>{{$header}}</td>
            @endforeach
        </thead>
        <tbody>
        @foreach ($data as $item)
            <tr>
                @foreach ($headers as $key => $header)
                    <td>{{$item[$key]}}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

@endsection
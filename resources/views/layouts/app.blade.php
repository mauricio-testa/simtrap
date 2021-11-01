<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

</head>
<body>

    <?php
        $data = [
            ['text' => 'Dashboard', 'url' => url('/'), 'icon' => 'mdi-monitor-dashboard'],
            ['text' => 'Viagens', 'url' => url('/viagens'), 'icon' => 'mdi-ambulance'],
            // ['heading' => 'Cadastros'],
            ['text' => 'Motoristas', 'url' => url('/cadastros/motoristas'), 'icon' => 'mdi-account-tie'],
            ['text' => 'Veículos', 'url' => url('/cadastros/veiculos'), 'icon' => 'mdi-car'],
            ['text' => 'Pacientes', 'url' => url('/cadastros/pacientes'), 'icon' => 'mdi-account-group'],
            ['heading' => 'Administração'],
            ['text' => 'Relatórios', 'url' => url('/relatorios'), 'icon' => 'mdi-file-chart'],
            ['text' => 'Logs', 'url' => url('/admin/logs'), 'icon' => 'mdi-format-list-bulleted-triangle', 'level' => -1],
            ['text' => 'Unidades', 'url' =>  url('/admin/unidades'), 'icon' => 'mdi-hospital-building', 'level' => -1],
        ];

        // remove menus administrativos se não for admin
        // -1 = adm geral
        // 0 = adm da unidade
        // 1 = normal

        if (Auth::user()->level != config('constants.LEVEL_ROOT')) {
            $data = array_values(array_filter($data, function($item) {
                return empty($item['level']) || $item['level'] != config('constants.LEVEL_ROOT');
            }));
        }

        $routes = [
            'api' => [
                'motorista' => url('/api/motoristas'),
                'veiculo'   => url('/api/veiculos'),
                'paciente'  => url('/api/pacientes'),
                'viagem'    => url('/api/viagens'),
                'lista'     => url('/api/lista'),
                'municipio' => url('/api/municipios'),
                'unidade'   => url('/api/unidades'),
                'usuario'   => url('/api/usuarios'),
                'log'       => url('/api/logs')
            ]
        ];
    ?>

    <script>
        window.__routes = <?= json_encode($routes)?>
    </script>

    <div id="app">

        <v-app>
            <navigation
                :menus="{{ json_encode($data) }}"
                :user="{{ json_encode(['nome' => Auth::user()->name, 'id' => Auth::user()->id]) }}"
                :unidade="{{ json_encode(session('unidade')) }}"
            ></navigation>
            <v-content>
                <v-container fluid>
                    @yield('content')
                </v-container>
            </v-content>
        </v-app>

        <!-- logout -->
        <form id="logout-form" action="{{ route('logout') }}" method="GET" style="display: none;">
            {{ csrf_field() }}
        </form>

    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

</body>
</html>

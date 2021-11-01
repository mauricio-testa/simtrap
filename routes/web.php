<?php

use App\Http\Controllers\Api\ListaController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\MotoristaController;
use App\Http\Controllers\Api\MunicipioController;
use App\Http\Controllers\Api\PacienteController;
use App\Http\Controllers\Api\UnidadeController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VeiculoController;
use App\Http\Controllers\Api\ViagemController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\Report\ReportController;

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Admin;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/', [IndexController::class, 'index'])->name('index');
    Route::view('/viagens', 'viagem');

    Route::group(['prefix' => 'cadastros'], function () {
        Route::view('/motoristas', 'motorista');
        Route::view('/veiculos', 'veiculo');
        Route::view('/pacientes', 'paciente');
    });

    Route::prefix('api')->group(function () {
        Route::apiResource('motoristas', MotoristaController::class);
        Route::apiResource('veiculos', VeiculoController::class);
        Route::apiResource('pacientes', PacienteController::class);
        Route::apiResource('viagens', ViagemController::class);
        Route::apiResource('lista', ListaController::class);
        Route::apiResource('municipios', MunicipioController::class);
        Route::apiResource('unidades', UnidadeController::class)->middleware(Admin::class);
        Route::apiResource('usuarios', UsuarioController::class);
        Route::apiResource('logs', LogController::class);
    });

    Route::prefix('relatorios')->group(function () {
        Route::view('/','report.index');
        Route::get('lista/{viagem}', [ReportController::class, 'lista'])->where('viagem', '[0-9]+');
        Route::get('paciente/{paciente}', [ReportController::class, 'paciente'])->where('paciente', '[0-9]+');
        Route::get('viagens/{qs}', [ReportController::class, 'viagens']);
        Route::get('faltas/{qs}', [ReportController::class, 'faltas']);
    });

    Route::view('admin/unidades', 'admin.unidade')->middleware(Admin::class);
    Route::view('admin/logs', 'admin.log')->middleware(Admin::class);

});

//Route::any('chamada/{viagem}', 'External\ChamadaController@index')->where('viagem', '[0-9]+');
//Route::post('chamada/authenticate/{viagem}', 'External\ChamadaController@authenticate')->where('viagem', '[0-9]+');

//Auth::routes();


Route::view('/login', 'auth/login')->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class], 'logout')->name('logout');

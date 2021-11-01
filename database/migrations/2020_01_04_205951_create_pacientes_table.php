<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePacientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rg', 10)->nullable()->unique();
            $table->string('nome', 240)->nullable(false);
            $table->string('telefone', 15)->nullable();
            $table->bigInteger('sus')->nullable();
            $table->string('endereco', 240)->nullable();
            $table->unsignedInteger('id_unidade');
            $table->integer('codigo_municipio')->nullable(false);
            $table->integer('status')->nullable(false)->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_unidade')->references('id')->on('unidades');
            $table->foreign('codigo_municipio')->references('codigo')->on('municipios');

            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pacientes');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lista', function (Blueprint $table) {
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_viagem');
            $table->decimal('acompanhante_rg', 10, 0)->nullable();
            $table->string('acompanhante_nome', 240)->nullable();
            $table->string('consulta_local', 240);
            $table->time('consulta_hora')->nullable();
            $table->string('consulta_observacao', 240)->nullable();
            $table->enum('compareceu', ['SIM', 'NAO', 'PENDENTE'])->default('PENDENTE');

            $table->foreign('id_paciente')->references('id')->on('pacientes');
            $table->foreign('id_viagem')->references('id')->on('viagens')->onDelete('cascade');;

            $table->primary(['id_paciente', 'id_viagem']);

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
        Schema::dropIfExists('listas');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViagemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viagens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_unidade');
            $table->unsignedInteger('id_veiculo');
            $table->unsignedInteger('id_motorista');
            $table->integer('cod_destino');
            $table->date('data_viagem')->nullable(false);
            $table->time('hora_saida')->nullable();
            $table->string('observacao', 240)->nullable();
            $table->enum('status', ['NOVA', 'EXPORTADA', 'CONCLUIDA'])->default('NOVA');

            $table->foreign('id_unidade')->references('id')->on('unidades');
            $table->foreign('cod_destino')->references('codigo')->on('municipios');
            $table->foreign('id_motorista')->references('id')->on('motoristas');
            $table->foreign('id_veiculo')->references('id')->on('veiculos');

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
        Schema::dropIfExists('viagems');
    }
}

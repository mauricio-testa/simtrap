<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVeiculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_unidade')->nullable(false);
            $table->string('descricao', 240)->nullable(false);
            $table->string('placa', 10)->nullable(false)->unique();
            $table->integer('lotacao')->nullable(false);
            $table->enum('tipo', ['PROPRIO', 'TERCEIRIZADO'])->default('PROPRIO');
            $table->string('ano_modelo', 9)->nullable();
            $table->string('marca_modelo', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_unidade')->references('id')->on('unidades');

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
        Schema::dropIfExists('veiculos');
    }
}

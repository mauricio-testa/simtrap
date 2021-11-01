<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMotoristasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('motoristas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 240)->nullable(false);
            $table->string('telefone', 15)->nullable();
            $table->unsignedInteger('id_unidade')->nullable(false);
            $table->integer('access_key')->nullable();
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
        Schema::dropIfExists('motoristas');
    }
}

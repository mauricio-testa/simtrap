<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_municipio')->nullable(false);
            $table->string('descricao', 240)->nullable(false);
            $table->integer('status')->nullable(false)->default(1);
            $table->string('responsavel', 240)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_municipio')->references('codigo')->on('municipios');

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
        Schema::dropIfExists('unidades');
    }
}

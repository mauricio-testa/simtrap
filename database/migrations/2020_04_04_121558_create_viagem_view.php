<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateViagemView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS vw_viagem");
        DB::statement("
            CREATE VIEW vw_viagem AS
            SELECT 
                viagens.*,
                mu.nome AS municipio_nome,
                mo.nome AS motorista_nome,
                ve.lotacao AS lotacao,
                ve.descricao AS veiculo_nome,
                DATE_FORMAT(viagens.data_viagem, '%d/%m/%Y') AS data_formated,
                CONCAT(ve.descricao, ' (', ve.placa, ')') AS veiculo
            FROM
                viagens
                    LEFT JOIN
                municipios AS mu ON viagens.cod_destino = mu.codigo
                    LEFT JOIN
                unidades AS un ON viagens.id_unidade = un.id
                    LEFT JOIN
                motoristas AS mo ON viagens.id_motorista = mo.id
                    LEFT JOIN
                veiculos AS ve ON viagens.id_veiculo = ve.id
                order by id desc
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW vw_viagem");
    }
}

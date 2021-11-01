<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateListaView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS vw_lista");
        DB::statement("
            CREATE VIEW vw_lista AS
            SELECT 
                id_viagem,
                nome,
                rg,
                telefone,
                consulta_hora,
                consulta_observacao,
                consulta_local,
                acompanhante,
                acompanhante_desc
            FROM
                (SELECT 
                    l.id_viagem,
                        p.id AS paciente_id,
                        p.nome,
                        p.rg,
                        p.telefone,
                        l.consulta_hora,
                        l.consulta_observacao,
                        l.consulta_local,
                        0 AS acompanhante,
                        NULL AS acompanhante_desc
                FROM
                    lista l
                LEFT JOIN pacientes p ON p.id = l.id_paciente UNION SELECT 
                    l.id_viagem,
                        p.id AS paciente_id,
                        l.acompanhante_nome AS nome,
                        l.acompanhante_rg AS rg,
                        NULL AS telefone,
                        NULL AS consulta_hora,
                        NULL AS consulta_observacao,
                        NULL AS consulta_local,
                        1 AS acompanhante,
                        CONCAT('Acompanhante de ', p.nome) AS acompanhante_desc
                FROM
                    lista l
                LEFT JOIN pacientes p ON p.id = l.id_paciente
                WHERE
                    l.acompanhante_nome IS NOT NULL) results
            ORDER BY id_viagem , paciente_id , acompanhante
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW vw_lista");
    }
}

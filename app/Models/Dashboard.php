<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Helpers\ErrorInterpreter;

class Dashboard extends Model
{
    protected $unidade = NULL;

    public function getAll() {

        $this->unidade = Auth::user()->id_unidade;

        try {
            return [
                'statistics' => $this->getStatistics(),
                'absenteismo' => $this->getAbsenteism(),
                'top_veiculos' => $this->getTopVeiculos(),
                'top_destinos' => $this->getTopDestinos(),
                'top_motoristas' => $this->getTopMotoristas(),
                'proximas_viagens' => $this->getProximasViagens(),
                'total_viagens_mes' => $this->getTotalViagensPorMes(),
                'total_passageiros_mes' => $this->getTotalPassageirosPorMes()
            ];
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ErrorInterpreter::getMessage($th)
            ]);
        }
    }

    private function getTopMotoristas () {
        $results = DB::select(DB::raw("
            SELECT nome AS descricao, count(viagens.id) AS total_viagens
            FROM motoristas
            LEFT JOIN viagens ON viagens.id_motorista = motoristas.id
            WHERE viagens.id_unidade = $this->unidade
            GROUP BY motoristas.id ORDER BY total_viagens DESC limit 5;
        "));
        return $results;
    }

    private function getTopVeiculos () {
        $results = DB::select(DB::raw("
            SELECT CONCAT(descricao,' - ',placa) AS descricao, COUNT(viagens.id) AS total_viagens
            FROM veiculos
            LEFT JOIN viagens ON viagens.id_veiculo = veiculos.id
            WHERE viagens.id_unidade = $this->unidade
            GROUP BY veiculos.id ORDER BY total_viagens DESC limit 5;
        "));
        return $results;
    }

    private function getTopDestinos () {
        $results = DB::select(DB::raw("
            SELECT nome AS descricao, count(viagens.id) AS total_viagens
            FROM municipios
            LEFT JOIN viagens ON viagens.cod_destino = municipios.codigo
            WHERE viagens.id_unidade = $this->unidade
            GROUP BY municipios.codigo ORDER BY total_viagens DESC limit 5;
        "));
        return $results;
    }

    private function getTotalViagensPorMes () {
        $results = DB::select(DB::raw("
            SELECT month_num, SUM(total) as viagens FROM (
                SELECT
                    MONTH(data_viagem) AS month_num, COUNT(*) AS total
                    FROM viagens
                    WHERE YEAR(data_viagem) = YEAR(CURDATE())
                    AND id_unidade = $this->unidade
                    GROUP BY month_num
                UNION SELECT 1, 0
                UNION SELECT 2, 0
                UNION SELECT 3, 0
                UNION SELECT 4, 0
                UNION SELECT 5, 0
                UNION SELECT 6, 0
                UNION SELECT 7, 0
                UNION SELECT 8, 0
                UNION SELECT 9, 0
                UNION SELECT 10, 0
                UNION SELECT 11, 0
                UNION SELECT 12, 0
            ) AS sub GROUP BY month_num ORDER BY month_num;
        "));

        $labels = [];
        $data = [];

        foreach ($results as $key => $value) {
            array_push($labels, $this->monthName($value->month_num));
            array_push($data, (int) $value->viagens);
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getTotalPassageirosPorMes () {
        $results = DB::select(DB::raw("
            SELECT viagem_mes,
            SUM(total_pacientes) as total_pacientes,
            sum(acompanhantes) as total_acompanhantes,
            sum(compareceu) as compareceu,
            sum(nao_compareceu) as nao_compareceu,
            sum(pendente) as pendente
            FROM (
                SELECT MONTH(vi.data_viagem) as viagem_mes,
                    (SELECT COUNT(*) FROM lista WHERE id_viagem = vi.id) AS total_pacientes,
                    (SELECT COUNT(*) FROM lista WHERE id_viagem = vi.id
                    AND acompanhante_nome IS NOT NULL AND acompanhante_nome <> '') as acompanhantes,
                    (SELECT COUNT(*) FROM lista WHERE id_viagem = vi.id
                    AND compareceu = 'SIM') AS compareceu,
                    (SELECT COUNT(*) FROM lista WHERE id_viagem = vi.id
                    AND compareceu = 'NAO') AS nao_compareceu,
                    (SELECT COUNT(*) FROM lista WHERE id_viagem = vi.id
                    AND compareceu = 'PENDENTE') AS pendente
                FROM viagens AS vi
                WHERE YEAR(vi.data_viagem) = YEAR(CURDATE())
                AND vi.id_unidade = $this->unidade
                UNION SELECT 1, 0, 0, 0, 0, 0
                UNION SELECT 2, 0, 0, 0, 0, 0
                UNION SELECT 3, 0, 0, 0, 0, 0
                UNION SELECT 4, 0, 0, 0, 0, 0
                UNION SELECT 5, 0, 0, 0, 0, 0
                UNION SELECT 6, 0, 0, 0, 0, 0
                UNION SELECT 7, 0, 0, 0, 0, 0
                UNION SELECT 8, 0, 0, 0, 0, 0
                UNION SELECT 9, 0, 0, 0, 0, 0
                UNION SELECT 10, 0, 0, 0, 0, 0
                UNION SELECT 11, 0, 0, 0, 0, 0
                UNION SELECT 12, 0, 0, 0, 0, 0
            ) AS sub
            GROUP BY viagem_mes
        "));

        $mes_atual = date('n');
        $mes_inicial = 1;
        $mes_final = 6;
        $labels = [];
        $data = [
            'pacientes'     => [],
            'acompanhantes' => [],
            'passageiros'   => [],
            'compareceu'    => [],
            'nao_compareceu'=> [],
            'pendente'      => [],
        ];

        /*
         * REGRA: se o mes atual for menor que junho, mostra de janeiro até junho (6 meses)
         * senão, mostra de 6 meses atrás, até o mes atual.
         */

        if ($mes_atual > 6) {
            $mes_final = $mes_atual;
            $mes_inicial = $mes_atual - 6;
        }

        foreach ($results as $key => $value) {

            if ($value->viagem_mes < $mes_inicial || $value->viagem_mes > $mes_final)
            continue;

            array_push($labels, $this->monthName($value->viagem_mes));
            array_push($data['pacientes'], $value->total_pacientes);
            array_push($data['acompanhantes'], $value->total_acompanhantes);
            array_push($data['compareceu'], $value->compareceu);
            array_push($data['nao_compareceu'], $value->nao_compareceu);
            array_push($data['pendente'], $value->pendente);
            array_push($data['passageiros'], $value->total_pacientes + $value->total_acompanhantes);
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getProximasViagens () {
        $results = DB::select(DB::raw("
            SELECT id, data_formated, municipio_nome, veiculo,
            (
                COALESCE((SELECT COUNT(*) FROM lista WHERE id_viagem = id), 0) +
                COALESCE((SELECT COUNT(*) FROM lista WHERE id_viagem = id AND acompanhante_nome IS NOT NULL AND acompanhante_nome <> ''), 0)
            ) AS passageiros
            FROM vw_viagem
            WHERE id_unidade = $this->unidade AND data_viagem >= NOW()
            ORDER BY data_viagem
            LIMIT 10;
        "));
        return $results;
    }

    private function getStatistics () {

        $results = DB::select(DB::raw("
            SELECT
                * ,
                (total_pacientes_transportados + total_acompanhantes_transportados) as total_passageiros_transportados,
                CONCAT(ROUND(COALESCE(total_acompanhantes_transportados * 100 / total_pacientes_transportados, 0), 0), '%')
                    as indice_pacientes_precisa_acompanhante,
                ROUND(COALESCE((total_pacientes_transportados + total_acompanhantes_transportados) / total_viagens, 0), 1)
                    as media_passageiro_por_viagem
            FROM
            (
                SELECT
                COALESCE((SELECT COUNT(*) AS total_viagens FROM viagens WHERE id_unidade = $this->unidade), 0) AS total_viagens,
                COALESCE((
                    SELECT COUNT(*) FROM lista JOIN viagens ON viagens.id = lista.id_viagem
                    WHERE viagens.id_unidade = $this->unidade), 0
                ) AS total_pacientes_transportados,
                COALESCE((
                    SELECT COUNT(*) FROM lista JOIN viagens ON viagens.id = lista.id_viagem
                    WHERE viagens.id_unidade = $this->unidade
                    AND acompanhante_nome IS NOT NULL AND acompanhante_nome <> ''), 0
                ) as total_acompanhantes_transportados
            ) sub;
        "));

        return $results[0];
    }

    private function getAbsenteism () {
        $results = DB::select(DB::raw("
            SELECT
                compareceu_ano, faltou_ano, (compareceu_ano + faltou_ano) as total_ano,
                CONCAT(ROUND(COALESCE(faltou_ano * 100 / (compareceu_ano + faltou_ano), 0), 0), '%') as absenteismo_ano,
                compareceu_mes, faltou_mes, (compareceu_mes + faltou_mes) as total_mes,
                CONCAT(ROUND(COALESCE(faltou_mes * 100 / (compareceu_mes + faltou_mes), 0), 0), '%') as absenteismo_mes
            FROM
                (SELECT count(*) AS compareceu_ano FROM lista
                    LEFT JOIN viagens ON viagens.id = lista.id_viagem
                    WHERE viagens.id_unidade = $this->unidade
                    AND viagens.status = 'CONCLUIDA'
                    AND compareceu = 'SIM'
                    AND YEAR(viagens.data_viagem) = YEAR(CURDATE())) AS compareceu_ano,
                (SELECT count(*) AS faltou_ano FROM lista
                    LEFT JOIN viagens ON viagens.id = lista.id_viagem
                    WHERE viagens.id_unidade = $this->unidade
                    AND viagens.status = 'CONCLUIDA'
                    AND compareceu = 'NAO'
                    AND YEAR(viagens.data_viagem) = YEAR(CURDATE())) AS faltou_ano,
                (SELECT count(*) AS compareceu_mes FROM lista
                    LEFT JOIN viagens ON viagens.id = lista.id_viagem
                    WHERE viagens.id_unidade = $this->unidade
                    AND viagens.status = 'CONCLUIDA'
                    AND compareceu = 'SIM'
                    AND MONTH(viagens.data_viagem) = MONTH(CURDATE())) AS compareceu_mes,
                (SELECT count(*) AS faltou_mes FROM lista
                    LEFT JOIN viagens ON viagens.id = lista.id_viagem
                    WHERE viagens.id_unidade = $this->unidade
                    AND viagens.status = 'CONCLUIDA'
                    AND compareceu = 'NAO'
                AND MONTH(viagens.data_viagem) = MONTH(CURDATE())) AS faltou_mes;
        "));

        return $results[0];
    }

    private function monthName ($month) {
        return ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'][$month-1];
    }
}

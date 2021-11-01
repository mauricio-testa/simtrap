<?php

use Illuminate\Database\Seeder;

class UnidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $unidades = [
            ['id' => 1, 'id_municipio' => 4040, 'descricao' => 'Unidade Teste']
        ];
        \App\Unidade::insert($unidades);
    }
}

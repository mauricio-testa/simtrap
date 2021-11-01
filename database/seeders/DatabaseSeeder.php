<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(MunicipiosSeeder::class);
        $this->call(UnidadesSeeder::class);
        $this->call(UsersSeeder::class);
    }
}

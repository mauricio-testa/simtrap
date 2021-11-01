<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'id' => 1, 
                'name' => 'King', 
                'email' => 'mauriciotesta97@gmail.com', 
                'password' => '$2y$10$BMy8cxW4iW1BRkpTy/H4puczglrC3YusDv4PUua9ljdxlCMYA8xS2', // 123456
                'id_unidade' => 1, 
                'remember_token' => null,
                'level' => -1
            ] 
        ];
        \App\User::insert($users);
    }
}

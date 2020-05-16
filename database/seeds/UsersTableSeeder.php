<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder {

    public function run() {

        if(env('APP_ENV') != 'production')
        {
            $password = Hash::make('admin123');

            for ($i = 0; $i < 1; $i++)
            {
                $users[] = [
                    'name' => 'admin',
                    'email' => 'jaya@gmail.com',
                    'password' => $password,
                    'created_at' => date('Y-m-d H:m:s'),
                    'updated_at' => date('Y-m-d H:m:s')
                ];
            }

            User::insert($users);
        }
    }
}

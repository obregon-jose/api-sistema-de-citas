<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository as PassportClientRepository;

class UserSedeer extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientRepository = new PassportClientRepository();
        $client = $clientRepository->createPersonalAccessClient(
            null, 'barbearía', 'http://your-callback-url'
        );
        //
        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'a0@gmail.com',
                'password' => bcrypt('12345678')
            ],
        ]);

        DB::table('profiles')->insert([
            [
                'user_id' => 1,
                'role_id' => 1,
            ],
            [
                'user_id' => 1,
                'role_id' => 2,
            ],
            [
                'user_id' => 1,
                'role_id' => 3,
            ],
        ]);
    }
}

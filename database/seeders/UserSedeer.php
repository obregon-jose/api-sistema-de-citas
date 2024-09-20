<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
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
        
        // Crear el usuario root
        $rootUser = User::updateOrCreate(
            ['email' => 'sc@sc.com'], 
            [
                'name' => 'Root SC',
                'password' => bcrypt('12345678'), 
            ]
        );

        // Crear el perfil para el usuario root
        Profile::updateOrCreate(
            ['user_id' => $rootUser->id],
            [
                'role_id' => 5, 
                // 'status' => 'active'
            ]
        );
    }
}

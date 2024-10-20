<?php

namespace Database\Seeders;

use App\Mail\WelcomeEmail;
use App\Models\Profile;
use App\Models\User;
use App\Http\Controllers\RandomGeneratorController as RandomPasswordGenerator;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $clientRepository = new PassportClientRepository();
        $client = $clientRepository->createPersonalAccessClient(
            null, 'barbearía', 'http://your-callback-url'
        );

        $GeneratorController = new RandomPasswordGenerator();
        $passwordGenerado = $GeneratorController->generateRandomPassword();
        // Crear el usuario root
        $rootUser = User::updateOrCreate(
            ['email' => 'obregonjose812@gmail.com'], 
            [
                'name' => 'JOSE OBREGON',
                'password' => bcrypt($passwordGenerado), 
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
        UserDetail::updateOrCreate(
            ['user_id' => $rootUser->id],
            [
                'nickname' => 'root', 
                'phone' => '1234567890', 
                'photo' => 'https://ui-avatars.com/api/?name=JOSE+OBREGON&color=7F9CF5&background=EBF4FF', 
                'note' => 'Usuario root del sistema'
            ]
        );
        Mail::to($rootUser->email)->send(new WelcomeEmail($rootUser, 'root', $passwordGenerado));
    }
}

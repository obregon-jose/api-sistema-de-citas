<?php

namespace Database\Seeders;

use App\Mail\WelcomeEmail;
use App\Models\Profile;
use App\Models\User;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;
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
        $authController = new AuthController();
        $passwordGenerado = $authController->generateRandomPassword();
        // Crear el usuario root
        $rootUser = User::updateOrCreate(
            ['email' => 'obregonjose812@gmail.com'], 
            [
                'name' => 'Root SC',
                'password' => bcrypt($passwordGenerado), 
            ]
            // Actualizacion: Genear contraseñas aleatorias para root y peluquero y enviar las por correo, personalizar mensaje de Bienvenida
        );

        // Crear el perfil para el usuario root
        Profile::updateOrCreate(
            ['user_id' => $rootUser->id],
            [
                'role_id' => 5, 
                // 'status' => 'active'
            ]
        );
        Mail::to($rootUser->email)->send(new WelcomeEmail($rootUser, 'root', $passwordGenerado));
    }
}

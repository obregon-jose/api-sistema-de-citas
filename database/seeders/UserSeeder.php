<?php

namespace Database\Seeders;

use App\Mail\WelcomeEmail;
use App\Models\Profile;
use App\Models\User;
use App\Http\Controllers\RandomGeneratorController as RandomPasswordGenerator;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        //$GeneratorController = new RandomPasswordGenerator();
        //$passwordGenerado = $GeneratorController->generateRandomPassword();
        // Crear el usuario root
        $rootUser = User::updateOrCreate(
            ['email' => 'obregonjose812@gmail.com'], 
            [
                'name' => 'JOSE OBREGON',
                'password' => bcrypt('obregonjose812@gmail.com'), 
            ]
        );

        // Crear el perfil para el usuario root
        Profile::updateOrCreate(
            ['user_id' => $rootUser->id],
            [
                'role_id' => 5, 
                // 'status' => 'active'
            ]
        ); //
        UserDetail::updateOrCreate(
            ['user_id' => $rootUser->id],
            [
                'nickname' => 'root', 
                'phone' => '1234567890', 
                'photo' => 'https://avatars.githubusercontent.com/u/145609553?s=48&v=4', 
                'note' => 'Usuario root del sistema'
            ]
        );
        Mail::to($rootUser->email)->send(new WelcomeEmail($rootUser, 'root', $passwordGenerado = 'obregonjose812@gmail.com'));

    }
}

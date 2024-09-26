<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class RandomGeneratorController extends Controller
{
    //Generar contraseñas aleatorias
    public function generateRandomPassword($length = 12) {
        $characters = implode('', array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'), str_split('!@#$%^&*()-_=+[]{}|;:,.<>?')));
        $charactersLength = strlen($characters);
        $randomPassword = '';
        for ($i = 0; $i < $length; $i++) {
            $randomPassword .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomPassword;
    }

    //Generar códigos aleatorios
    public function generateRandomCode($length = 6) {
        $characters = implode('', range('0', '9'));
        $charactersLength = strlen($characters);
        $randomCode = '';
        for ($i = 0; $i < $length; $i++) {
            $randomCode .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomCode;
    }
}

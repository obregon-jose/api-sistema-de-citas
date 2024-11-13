<?php

namespace Database\Seeders;

use App\Models\AvailabilityDay;
use App\Models\BarberAvailability;
use App\Models\TimeSlot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Borrar datos existentes para evitar duplicados
        // DB::table('availability_days')->delete();
        DB::table('time_slots')->delete();

        // Crear los días de la semana
        // $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        // foreach ($days as $day) {
        //     AvailabilityDay::create(['day_of_week' => $day]);
        // }

        // Crear franjas horarias (por ejemplo, cada hora de 8am a 6pm)
        $timeSlots = [
            ['hour_start' => '07:00', 'hour_end' => '07:30'],
            ['hour_start' => '07:30', 'hour_end' => '08:00'],
            ['hour_start' => '08:00', 'hour_end' => '08:30'],
            ['hour_start' => '08:30', 'hour_end' => '09:00'],
            ['hour_start' => '09:00', 'hour_end' => '09:30'],
            ['hour_start' => '09:30', 'hour_end' => '10:00'],
            ['hour_start' => '10:00', 'hour_end' => '10:30'],
            ['hour_start' => '10:30', 'hour_end' => '11:00'],
            ['hour_start' => '11:00', 'hour_end' => '11:30'],
            ['hour_start' => '11:30', 'hour_end' => '12:00'],
            ['hour_start' => '12:00', 'hour_end' => '12:30'],
            ['hour_start' => '12:30', 'hour_end' => '13:00'],
            ['hour_start' => '13:00', 'hour_end' => '13:30'],
            ['hour_start' => '13:30', 'hour_end' => '14:00'],
            ['hour_start' => '14:00', 'hour_end' => '14:30'],
            ['hour_start' => '14:30', 'hour_end' => '15:00'],
            ['hour_start' => '15:00', 'hour_end' => '15:30'],
            ['hour_start' => '15:30', 'hour_end' => '16:00'],
            ['hour_start' => '16:00', 'hour_end' => '16:30'],
            ['hour_start' => '16:30', 'hour_end' => '17:00'],
            ['hour_start' => '17:00', 'hour_end' => '17:30'],
            ['hour_start' => '17:30', 'hour_end' => '18:00'],
            ['hour_start' => '18:00', 'hour_end' => '18:30'],
            ['hour_start' => '18:30', 'hour_end' => '19:00'],
            ['hour_start' => '19:00', 'hour_end' => '19:30'],
            ['hour_start' => '19:30', 'hour_end' => '20:00'],
            ['hour_start' => '20:00', 'hour_end' => '20:30'],
            ['hour_start' => '20:30', 'hour_end' => '21:00'],
            ['hour_start' => '21:00', 'hour_end' => '21:30'],
            ['hour_start' => '21:30', 'hour_end' => '22:00'],
        ];

        foreach ($timeSlots as $slot) {
            TimeSlot::create($slot);
        }
    
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BarberAvailability;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeSlot {
    public $id;
    public $hour_start;
    public $hour_end;

    public function __construct($id, $hour_start, $hour_end) {
        $this->id = $id;
        $this->hour_start = $hour_start;
        $this->hour_end = $hour_end;
    }
}

class BarberController extends Controller
{
    //
    
    public function index()
    {
        // Obtener el ID del rol "peluquero"
        $barberRoleId = DB::table('roles')->where('name', 'peluquero')->value('id');

        // Consultar todos los usuarios con el rol de "peluquero" y sus detalles
        $barbers = User::whereHas('profiles', function ($query) use ($barberRoleId) {
                $query->where('role_id', $barberRoleId);
            })
            ->with(['profiles' => function ($query) use ($barberRoleId) {
                $query->where('role_id', $barberRoleId);
            }, 'detail']) // Cargar también los detalles del usuario
            ->get();

        return response()->json($barbers);
    }


    /*---------- DISPONIBILIDAD ----------*/
    // Función para crear un horario inactivo para un perfil recién registrado
    public function createDefaultAvailability($profileId)
    {
        // Array con los días de la semana (0 = Domingo, 6 = Sábado)
        $days = [
            0 => 'Lunes',
            1 => 'Martes',
            2 => 'Miércoles',
            3 => 'Jueves',
            4 => 'Viernes',
            5 => 'Sábado',
            6 => 'Domingo',
        ];
        // Obtener la fecha del primer día de la semana (lunes)
        $startOfWeek = Carbon::now('America/Bogota')->startOfWeek();  // Esto asegura que la semana comienza el lunes

        // Estructura para almacenar la disponibilidad de toda la semana
        $weeklyAvailability = [];

        // Obtener las franjas horarias
        $timeSlots = [
            new TimeSlot(1, '07:00', '07:30'),
            new TimeSlot(2, '07:30', '08:00'),
            new TimeSlot(3, '08:00', '08:30'),
            new TimeSlot(4, '08:30', '09:00'),
            new TimeSlot(5, '09:00', '09:30'),
            new TimeSlot(6, '09:30', '10:00'),
            new TimeSlot(7, '10:00', '10:30'),
            new TimeSlot(8, '10:30', '11:00'),
            new TimeSlot(9, '11:00', '11:30'),
            new TimeSlot(10, '11:30', '12:00'),
            new TimeSlot(11, '12:00', '12:30'),
            new TimeSlot(12, '12:30', '13:00'),
            new TimeSlot(13, '13:00', '13:30'),
            new TimeSlot(14, '13:30', '14:00'),
            new TimeSlot(15, '14:00', '14:30'),
            new TimeSlot(16, '14:30', '15:00'),
            new TimeSlot(17, '15:00', '15:30'),
            new TimeSlot(18, '15:30', '16:00'),
            new TimeSlot(19, '16:00', '16:30'),
            new TimeSlot(20, '16:30', '17:00'),
            new TimeSlot(21, '17:00', '17:30'),
            new TimeSlot(22, '17:30', '18:00'),
            new TimeSlot(23, '18:00', '18:30'),
            new TimeSlot(24, '18:30', '19:00'),
            new TimeSlot(25, '19:00', '19:30'),
            new TimeSlot(26, '19:30', '20:00'),
            new TimeSlot(27, '20:00', '20:30'),
            new TimeSlot(28, '20:30', '21:00'),
            new TimeSlot(29, '21:00', '21:30'),
            new TimeSlot(30, '21:30', '22:00'),
        ];

        // Reorganizamos los días en la agenda empezando desde el lunes
        foreach ($days as $dayIndex => $dayName) {
            // Asignar la fecha correcta a cada día de la semana (basado en el inicio de la semana)
            $dayDate = $startOfWeek->copy()->addDays($dayIndex); // Ajusta la fecha de cada día
            
            // Crear el array de `time_slots` con todos los ids inactivos inicialmente
            $timeSlotData = [];
            foreach ($timeSlots as $timeSlot) {
                $timeSlotData[] = [
                    'id' => $timeSlot->id,
                    'status' => false // Inicialmente inactivo
                ];
            }

            // Agregar la disponibilidad de cada día a la estructura semanal
            $weeklyAvailability[$dayName] = [
                'date' => $dayDate->toDateString(), // Asignar la fecha real de ese día
                'time_slots' => $timeSlotData,
                'availability' => false, // Inicialmente todo el día inactivo
            ];
        }

        // Guardar la disponibilidad semanal en la base de datos
        BarberAvailability::create([
            'profile_id' => $profileId,
            'week_start_date' => $startOfWeek,
            'agenda' => json_encode($weeklyAvailability) // Guardar la disponibilidad como JSON
        ]);

        return response()->json(['message' => 'Horario predeterminado semanal creado con éxito.']);
    }
    // Consultar 2 semanas de disponibilidad para un perfil específico y mostar los 14 dias siguientes
    public function getAvailability($profile_id, $date, $maxDays = 14, $limitRecords = 2)
    {
        try {
            $startDate = Carbon::parse($date);
            $daysOfWeek = [];

            // Calcular los días solicitados a partir de la fecha de inicio
            for ($i = 0; $i < $maxDays; $i++) {
                $daysOfWeek[] = $startDate->copy()->addDays($i);
            }

            // Obtener todos los registros de disponibilidad del barbero ordenados por fecha de inicio de semana
            $availabilities = BarberAvailability::where('profile_id', $profile_id)
                ->orderBy('week_start_date', 'asc')
                ->take($limitRecords)
                ->get();

            if ($availabilities->isEmpty()) {
                return response()->json([
                    'availability' => false,
                ], 404);
            }

            $days = [
                0 => 'Domingo',
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado'
            ];

            $availabilityData = [];
            $allDatesInvalid = true;

            foreach ($daysOfWeek as $date) {
                $dateString = $date->format('Y-m-d');
                $dayOfWeek = $date->dayOfWeek;
                $dayName = $days[$dayOfWeek];
                $found = false;

                // Recorrer cada registro de disponibilidad para encontrar la fecha en alguno de ellos
                foreach ($availabilities as $availability) {
                    $weeklyAvailability = json_decode($availability->agenda, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return response()->json([
                            'error' => 'Error al decodificar la disponibilidad',
                            'details' => json_last_error_msg()
                        ], 500);
                    }

                    if (isset($weeklyAvailability[$dayName]) && $weeklyAvailability[$dayName]['date'] === $dateString) {
                        $availabilityData[] = [
                            'date' => $dayName . ' ' . $dateString,
                            'availability' => $weeklyAvailability[$dayName]['availability'],
                            'time_slots' => $weeklyAvailability[$dayName]['time_slots'],
                        ];
                        $allDatesInvalid = false;
                        $found = true;
                        break;  // Salir del bucle de registros ya que encontramos la fecha
                    }
                }

                if (!$found) {
                    $availabilityData[] = [
                        'date' => $dateString . ' ' . $dayName,
                        'error' => 'Esta fecha no está programada en la agenda.'
                    ];
                }
            }

            if ($allDatesInvalid) {
                return response()->json([
                    'error' => 'Ninguna de las fechas solicitadas está programada en la agenda del barbero.'
                ], 404);
            }

            return response()->json([
                'agenda' => $availabilityData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error al procesar la disponibilidad',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    
    /*************************************************************************/

    // Actualizar la disponibilidad de un perfil en un rango de fechas
    public function updateAvailability($profile_id, $startDate, $endDate, Request $request)
    {
        $days = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];

        // Obtener la agenda del JSON de entrada
        $availabilityInput = $request->input('agenda', []);

        // Convertir las fechas de inicio y fin
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Iterar sobre cada semana entre startDate y endDate
        $currentWeekStart = $startDate->copy()->startOfWeek();

        while ($currentWeekStart->lte($endDate)) {
            // Buscar o crear el registro de disponibilidad para la semana actual
            $availability = BarberAvailability::firstOrNew([
                'profile_id' => $profile_id,
                'week_start_date' => $currentWeekStart->toDateString()
            ]);

            // Cargar la disponibilidad semanal existente, o iniciar una nueva estructura
            $existingWeeklyAvailability = $availability->exists ? json_decode($availability->agenda, true) : [];

            // Iterar sobre los 7 días de la semana actual
            for ($i = 0; $i < 7; $i++) {
                $day = $currentWeekStart->copy()->addDays($i);
                $dayOfWeek = $day->dayOfWeek;
                $dayName = $days[$dayOfWeek];

                // Si hay datos en el JSON de entrada para este día, procesarlos
                if (isset($availabilityInput[$dayName])) {
                    // Asegurar que la estructura del día exista en la disponibilidad actual
                    if (!isset($existingWeeklyAvailability[$dayName])) {
                        $existingWeeklyAvailability[$dayName] = [
                            'date' => $day->toDateString(),	
                            'time_slots' => [],
                            'availability' => false
                        ];
                    }

                    // Actualizar los time_slots específicos para el día
                    $newTimeSlots = $availabilityInput[$dayName]['time_slots'];
                    foreach ($newTimeSlots as $newSlot) {
                        $found = false;
                        foreach ($existingWeeklyAvailability[$dayName]['time_slots'] as &$existingSlot) {
                            if ($existingSlot['id'] === $newSlot['id']) {
                                $existingSlot['status'] = $newSlot['status'];
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            $existingWeeklyAvailability[$dayName]['time_slots'][] = $newSlot;
                        }
                    }

                    // Actualizar el estado general de disponibilidad del día
                    if (isset($availabilityInput[$dayName]['availability'])) {
                        $existingWeeklyAvailability[$dayName]['availability'] = $availabilityInput[$dayName]['availability'];
                    }
                }
            }

            // Guardar el estado de la agenda actualizado en la base de datos
            $availability->agenda = json_encode($existingWeeklyAvailability);
            $availability->save();

            // Avanzar al inicio de la siguiente semana
            $currentWeekStart->addWeek();
        }

        return response()->json(['message' => 'Disponibilidad actualizada exitosamente']);
    }


}

<?php

namespace App\Http\Controllers;

use App\Models\AvailabilityDay;
use App\Models\BarberAvailability;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        // Array con los días de la semana, mapeados por Carbon (0 = Domingo, 6 = Sábado)
        $days = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];

        // Obtener todas las franjas horarias
        $timeSlots = TimeSlot::all();

        // Estructura para almacenar la disponibilidad de toda la semana
        $weeklyAvailability = [];

        foreach ($days as $dayIndex => $dayName) {
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
                'time_slots' => $timeSlotData,
                'status' => 0 // Inicialmente inactivo
            ];
        }

        // Guardar la disponibilidad semanal en la base de datos
        BarberAvailability::create([
            'profile_id' => $profileId,
            'time_slot_id' => json_encode($weeklyAvailability) // Guardar la disponibilidad como JSON
        ]);

        return response()->json(['message' => 'Horario predeterminado semanal creado con éxito.']);
    }
    // Función para actualizar la disponibilidad de un perfil en un día específico
    public function updateAvailability1($profile_id, $date, Request $request)
    {
        // Definir los días de la semana con los índices correspondientes de Carbon
        $days = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];

        // Convertir la fecha en el día de la semana
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $dayName = $days[$dayOfWeek];  // Obtener el nombre del día de la semana

        // Obtener los datos de time_slots y status del cuerpo de la solicitud
        $timeSlots = $request->input('time_slots', []);
        
        // Validación de los datos de entrada
        if (!is_array($timeSlots)) {
            return response()->json(['error' => 'time_slots es requerido y debe ser un array'], 400);
        }

        // Buscar o crear un registro de disponibilidad para el perfil
        $availability = BarberAvailability::firstOrNew(['profile_id' => $profile_id]);

        // Decodificar los time_slots existentes si el registro ya existía
        $existingWeeklyAvailability = $availability->exists ? json_decode($availability->time_slot_id, true) : [];

        // Asegurarse de que existe el día correspondiente en la estructura semanal
        if (!isset($existingWeeklyAvailability[$dayName])) {
            // Si no existe, inicializar la estructura para ese día
            $existingWeeklyAvailability[$dayName] = [
                'time_slots' => [],
                'status' => 0
            ];
        }

        // Obtener las franjas horarias del día actual
        $existingTimeSlots = &$existingWeeklyAvailability[$dayName]['time_slots'];

        foreach ($timeSlots as $newSlot) {
            $found = false;
            foreach ($existingTimeSlots as &$existingSlot) {
                if ($existingSlot['id'] === $newSlot['id']) {
                    // Cambiar el status de la franja horaria de true a false o viceversa
                    $existingSlot['status'] = !$existingSlot['status'];
                    $found = true;
                    break;
                }
            }

            // Si la franja horaria no existe, agregarla
            if (!$found) {
                // Si no está en la lista, agregar la franja horaria con el status cambiado
                $newSlot['status'] = !$newSlot['status']; // Cambiar el status
                $existingTimeSlots[] = $newSlot;
            }
        }

        // Actualizar el campo status de la disponibilidad semanal si se proporciona
        if ($request->has('status')) {
            $existingWeeklyAvailability[$dayName]['status'] = $request->input('status');
        }

        // Guardar los cambios en la base de datos
        $availability->time_slot_id = json_encode($existingWeeklyAvailability);
        $availability->save();

        return response()->json(['message' => 'Disponibilidad actualizada exitosamente']);
    }
    // Actualizar la disponibilidad de un perfil en un rango de fechas
    public function updateAvailability($profile_id, $start_date, $end_date, Request $request)
    {
        // Convertir las fechas a objetos Carbon
        $startDate = Carbon::parse($start_date);
        $endDate = Carbon::parse($end_date);
    
        // Validar que las fechas sean correctas
        if ($startDate->gt($endDate)) {
            return response()->json(['error' => 'La fecha de inicio no puede ser posterior a la fecha de fin.'], 400);
        }
    
        // Obtener las franjas horarias del cuerpo de la solicitud
        $timeSlots = $request->input('time_slots', []);
    
        // Validación de los datos de entrada
        if (!is_array($timeSlots)) {
            return response()->json(['error' => 'time_slots es requerido y debe ser un array'], 400);
        }
    
        // Definir los días de la semana con los índices correspondientes de Carbon
        $days = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];
    
        // Buscar o crear un registro de disponibilidad para el perfil
        $availability = BarberAvailability::firstOrNew(['profile_id' => $profile_id]);
    
        // Decodificar los time_slots existentes si el registro ya existía
        $existingWeeklyAvailability = $availability->exists ? json_decode($availability->time_slot_id, true) : [];
    
        // Iterar sobre cada día del rango de fechas
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeek;
            $dayName = $days[$dayOfWeek];  // Obtener el nombre del día de la semana
    
            // Asegurarse de que existe el día correspondiente en la estructura semanal
            if (!isset($existingWeeklyAvailability[$dayName])) {
                // Si no existe, inicializar la estructura para ese día
                $existingWeeklyAvailability[$dayName] = [
                    'time_slots' => [],
                    'status' => 0
                ];
            }
    
            // Obtener las franjas horarias del día actual
            $existingTimeSlots = &$existingWeeklyAvailability[$dayName]['time_slots'];
    
            // Actualizar las franjas horarias para el día actual
            foreach ($timeSlots as $newSlot) {
                $found = false;
                foreach ($existingTimeSlots as &$existingSlot) {
                    if ($existingSlot['id'] === $newSlot['id']) {
                        // Cambiar el status de la franja horaria de true a false o viceversa
                        $existingSlot['status'] = !$existingSlot['status'];
                        $found = true;
                        break;
                    }
                }
    
                // Si la franja horaria no existe, agregarla
                if (!$found) {
                    // Si no está en la lista, agregar la franja horaria con el status cambiado
                    $newSlot['status'] = !$newSlot['status']; // Cambiar el status
                    $existingTimeSlots[] = $newSlot;
                }
            }
    
            // Avanzar al siguiente día
            $currentDate->addDay();
        }
    
        // Si se proporciona el campo `status`, actualizar el campo status de la disponibilidad semanal
        if ($request->has('status')) {
            foreach ($existingWeeklyAvailability as &$dayData) {
                $dayData['status'] = $request->input('status');
            }
        }
    
        // Guardar los cambios en la base de datos
        $availability->time_slot_id = json_encode($existingWeeklyAvailability);
        $availability->save();
    
        return response()->json(['message' => 'Disponibilidad actualizada exitosamente']);
    }
    


    //Un dia en especifico
    public function getAvailability1($profile_id, $date)
    {
        try {
            // Convertir la fecha al día de la semana (0 = Domingo, 6 = Sábado)
            $dayOfWeek = Carbon::parse($date)->dayOfWeek;
    
            // Obtener el registro de disponibilidad semanal del barbero
            $availability = BarberAvailability::where('profile_id', $profile_id)->first();
    
            // Si no existe disponibilidad general para el barbero, retornar false
            if (!$availability) {
                return response()->json([
                    'profile_id' => $profile_id,
                    'day_id' => $dayOfWeek,
                    'availability' => false
                ], 404);
            }
    
            // Decodificar el JSON de disponibilidad semanal y verificar que sea válido
            $weeklyAvailability = json_decode($availability->time_slot_id, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Error al decodificar la disponibilidad',
                    'details' => json_last_error_msg()
                ], 500);
            }
    
            // Crear un array de nombres de días de la semana
            $days = [
                0 => 'Domingo',
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado'
            ];
    
            // Obtener el nombre del día de la semana
            $dayName = $days[$dayOfWeek];
    
            // Comprobar si existe disponibilidad para el día específico
            $dayAvailability = $weeklyAvailability[$dayName] ?? null;
    
            if ($dayAvailability && $dayAvailability['status'] === true) {
                // Retornar la disponibilidad del día específico
                return response()->json([
                    'profile_id' => $profile_id,
                    'day_id' => $dayOfWeek,
                    'day_name' => $dayName,
                    'status' => $dayAvailability['status'],
                    'availability' => true,
                    'time_slots' => $dayAvailability['time_slots'],
                    
                ]);
            } else {
                // Retornar false si no existe disponibilidad para ese día
                return response()->json([
                    'profile_id' => $profile_id,
                    'day_id' => $dayOfWeek,
                    'day_name' => $dayName,
                    'availability' => false
                ]);
            }
        } catch (\Exception $e) {
            // Capturar cualquier excepción y retornar error 500 con el mensaje
            return response()->json([
                'error' => 'Ocurrió un error al procesar la disponibilidad',
                'details' => $e->getMessage()
            ], 500);
        }

    }
    // n=7 dias a partir de la fecha
    public function getAvailability($profile_id, $date)
{
    try {
        // Convertir la fecha al día de la semana (0 = Domingo, 6 = Sábado)
        $startDate = Carbon::parse($date); // Fecha de inicio proporcionada
        $daysOfWeek = []; // Array para almacenar los días de la semana a partir de la fecha

        // Calcular los 7 días a partir de la fecha proporcionada
        for ($i = 0; $i < 7; $i++) {
            $daysOfWeek[] = $startDate->copy()->addDays($i); // Generar los 7 días
        }
        
        // Obtener el registro de disponibilidad semanal del barbero
        $availability = BarberAvailability::where('profile_id', $profile_id)->first();
        
        // Si no existe disponibilidad general para el barbero, retornar false
        if (!$availability) {
            return response()->json([
                'profile_id' => $profile_id,
                'availability' => false
            ], 404);
        }
        
        // Decodificar el JSON de disponibilidad semanal y verificar que sea válido
        $weeklyAvailability = json_decode($availability->time_slot_id, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'Error al decodificar la disponibilidad',
                'details' => json_last_error_msg()
            ], 500);
        }

        // Crear un array de nombres de días de la semana
        $days = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];

        $availabilityData = []; // Para almacenar los resultados de disponibilidad de los 7 días

        // Recorrer cada día a partir de la fecha proporcionada
        foreach ($daysOfWeek as $date) {
            $dayOfWeek = $date->dayOfWeek; // Obtener el día de la semana para la fecha
            $dayName = $days[$dayOfWeek]; // Obtener el nombre del día
            
            // Comprobar si existe disponibilidad para el día específico
            $dayAvailability = $weeklyAvailability[$dayName] ?? null;

            if ($dayAvailability && $dayAvailability['status'] === true) {
                // Si existe disponibilidad para el día, agregarla a la respuesta
                $availabilityData[] = [
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $dayName,
                    'availability' => true,
                    'time_slots' => $dayAvailability['time_slots'],
                    'status' => $dayAvailability['status']
                ];
            } else {
                // Si no existe disponibilidad, agregar la respuesta indicando disponibilidad falsa
                $availabilityData[] = [
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $dayName,
                    'availability' => false
                ];
            }
        }

        // Retornar la disponibilidad para los 7 días a partir de la fecha proporcionada
        return response()->json([
            'profile_id' => $profile_id,
            'availability' => $availabilityData
        ]);

    } catch (\Exception $e) {
        // Capturar cualquier excepción y retornar error 500 con el mensaje
        return response()->json([
            'error' => 'Ocurrió un error al procesar la disponibilidad',
            'details' => $e->getMessage()
        ], 500);
    }
}


}

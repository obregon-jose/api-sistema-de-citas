<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Profile;
use App\Models\Day;
use App\Models\TimeSlot;
use Carbon\Carbon;

class TimeSlotController extends Controller
{

    public function generarFranjaSemana(Request $request, $profileId)
{
    // Validar el formato del cuerpo de la solicitud
    $request->validate([
        '*' => 'array', // Las claves son días y apuntan a arreglos de horarios
        '*.0' => 'date_format:H:i', // Cada horario debe tener el formato H:i
    ]);

    // Validar que el perfil existe
    $profile = Profile::find($profileId);
    if (!$profile) {
        return response()->json(['message' => 'El perfil no existe.'], 404);
    }

    try {
        $diasHorarios = $request->all();
        $tamanoFranja = 30; // Tamaño de la franja en minutos

        // Configurar el rango de fechas desde hoy hasta el 31 de diciembre del presente año
        $fechaInicio = now();
        $fechaFin = now()->endOfYear();

        // Mapa de días en inglés a español
        $diasEnInglesAEspanol = [
            'Monday' => 'LUNES', 'Tuesday' => 'MARTES', 'Wednesday' => 'MIERCOLES',
            'Thursday' => 'JUEVES', 'Friday' => 'VIERNES', 'Saturday' => 'SABADO', 'Sunday' => 'DOMINGO'
        ];

        // Iterar por el rango de fechas
        for ($fecha = $fechaInicio; $fecha->lte($fechaFin); $fecha->addDay()) {
            $diaNombre = $fecha->format('l'); // Día en inglés
            $diaNombreSolicitud = $diasEnInglesAEspanol[$diaNombre] ?? null;

            if ($diaNombreSolicitud && isset($diasHorarios[$diaNombreSolicitud])) {
                // Crear el día asociado al perfil si no existe
                $day = Day::firstOrCreate([
                    'profile_id' => $profileId,
                    'name' => $diaNombreSolicitud,
                    'fecha' => $fecha->format('Y-m-d'),
                ]);

                // Generar franjas horarias
                foreach ($diasHorarios[$diaNombreSolicitud] as $horaInicio) {
                    $horaInicioTimestamp = strtotime($horaInicio);

                    // Crear franjas en función del tamaño configurado
                    for ($i = 0; $i < 2; $i++) { // Cambiar este "2" si se requiere más franjas
                        $horaFin = date("H:i", strtotime("+$tamanoFranja minutes", $horaInicioTimestamp));

                        TimeSlot::create([
                            'day_id' => $day->id,
                            'hour_start' => date("H:i", $horaInicioTimestamp),
                            'hour_end' => $horaFin,
                            'available' => true,
                        ]);

                        $horaInicioTimestamp = strtotime($horaFin);
                    }
                }
            }
        }

        return response()->json(['message' => 'Franjas horarias generadas'], 201);

    } catch (\Exception $err) {
        return response()->json([
            'message' => 'Ha ocurrido un error inesperado.',
            'error' => $err->getMessage(),
        ], 500);
    }
}



    public function TimeSlotsBarber($id)
    { 
    // Cargar el perfil con sus relaciones 'timeSlots' y 'days'
    $horario = Profile::with(['day', 'day.timeSlots'])->find($id);

    // return response()->json($horario);

    return response()->json([
        'horarios' => $horario,
    ], 200);

    }

    public function obtenerFranjasPorFecha($profile_id, $fecha, $retardo = 60)
{
    // try {
    //     // Validación de los parámetros
    //     if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    //         return response()->json(['message' => 'Formato de fecha inválido.'], 422);
    //     }

    //     // Busca el día correspondiente para la fecha y el perfil del peluquero
    //     $day = Day::where('fecha', $fecha)
    //         ->where('profile_id', $profile_id)
    //         ->first();

    //     // Verifica si el día existe
    //     if (!$day) {
    //         return response()->json(['message' => 'No se encontraron horarios para esta fecha y peluquero.'], 404);
    //     }

    //     // Obtén las franjas horarias (TimeSlots) para el día específico
    //     $timeSlots = TimeSlot::where('day_id', $day->id)
    //         ->where('available', true)  // Solo franjas disponibles
    //         ->get(['hour_start', 'hour_end', 'available']);

    //     return response()->json([
    //         'fecha' => $day->fecha,
    //         'dia' => $day->name,
    //         'franjas' => $timeSlots,
    //     ], 200);

    // } catch (\Exception $e) {
    //     return response()->json(['message' => 'Error interno del servidor.'], 500);
    // }
    try {
        // Validación de los parámetros
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return response()->json(['message' => 'Formato de fecha inválido.'], 422);
        }
    
        // Busca el día correspondiente para la fecha y el perfil del peluquero
        $day = Day::where('fecha', $fecha)
            ->where('profile_id', $profile_id)
            ->first();
    
        // Verifica si el día existe
        if (!$day) {
            return response()->json(['message' => 'No se encontraron horarios para esta fecha y peluquero.'], 404);
        }
    
        // Obtén la hora actual en la zona horaria de Colombia
        $horaActual = now()->setTimezone('America/Bogota');
    
        // Suma minutos a la hora actual para evitar que reserve a la misma hora actual
        $hora = $horaActual->copy()->addMinutes($retardo);
    
        // Obtén las franjas horarias (TimeSlots) disponibles y mayores o iguales a la hora modificada
        $timeSlots = TimeSlot::where('day_id', $day->id)
            ->where('available', true) // Solo franjas disponibles
            ->where('hour_start', '>=', $hora->format('H:i:s')) // Franjas iguales o mayores a la hora con 60 minutos más
            ->get(['hour_start', 'hour_end', 'available']);
    
        return response()->json([
            // 'fecha' => $day->fecha,
            // 'dia' => $day->name,
            // 'hora_actual' => $horaActual->format('H:i:s'),
            // 'hora_mas_60' => $hora->format('H:i:s'),
            'franjas' => $timeSlots,
        ], 200);
    
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error interno del servidor.'], 500);
    }
    
    
}


public function ocuparFranja(Request $request)
{
    // Validar que los campos date y hour_start están presentes y tienen el formato correcto
    $request->validate([
        'barber_id' => 'required',
        'date' => 'required|date_format:Y-m-d',
        'time' => 'required',
    ]);

    // Obtener la fecha y la hora de inicio desde la solicitud
    $date = $request->input('date');
    $hourStart = $request->input('time');

    // Buscar el día correspondiente usando la fecha
    $day = Day::where('profile_id',$request->barber_id)
                ->where('fecha', $date)
                ->first();

    if (!$day) {
        return response()->json(['message' => 'No se encontró el día para la fecha proporcionada.'], 404);
    }

    // Buscar la franja horaria usando el día y la hora de inicio
    $timeSlot = TimeSlot::where('day_id', $day->id)
                        ->where('hour_start', $hourStart)
                        ->first();

    if (!$timeSlot) {
        return response()->json(['message' => 'No se encontró la franja horaria para la fecha y hora proporcionadas.'], 404);
    }

    // Marcar la franja como ocupada
    $timeSlot->available = false;
    $timeSlot->save();

}

public function actualizarHorarioPorFecha(Request $request, $profileId, $fecha)
{
    // Validar el formato de la fecha y los horarios
    $request->validate([
        'horarios' => 'required|array',
        'horarios.*' => 'required|date_format:H:i',
    ]);

    // Verificar que el perfil existe
    $profile = Profile::find($profileId);
    if (!$profile) {
        return response()->json(['message' => 'El perfil especificado no existe.'], 404);
    }

    // Buscar el día usando la fecha proporcionada y el perfil
    $day = Day::where('profile_id', $profileId)
            ->where('fecha', $fecha)
            ->first();

    $diaNombre = Carbon::parse($fecha)->format('l'); // Día en inglés

    // Mapa de días en inglés a español
    $diasEnInglesAEspanol = [
        'Monday' => 'LUNES', 'Tuesday' => 'MARTES', 'Wednesday' => 'MIERCOLES',
        'Thursday' => 'JUEVES', 'Friday' => 'VIERNES', 'Saturday' => 'SABADO', 'Sunday' => 'DOMINGO'
    ];

    $diaNombreSolicitud = $diasEnInglesAEspanol[$diaNombre] ?? null;
    // Si no existe el día, lo creamos
    if (!$day) {
        $day = Day::create([
            'profile_id' => $profileId,
            'name' => $diaNombreSolicitud, // nombre del día
            'fecha' => $fecha,
        ]);
    }

    $horariosNuevos = $request->input('horarios');
    $tamanoFranja = 30; // Tamaño de la franja en minutos
    $horariosNuevosConvertidos = [];

    try {

        // Obtener las franjas horarias existentes en el día
        $timeSlotsExistentes = TimeSlot::where('day_id', $day->id)->get();

        // Identificar las franjas a eliminar
        foreach ($timeSlotsExistentes as $timeSlot) {
            $existsInNewSlots = collect($horariosNuevosConvertidos)->contains(function ($nuevoHorario) use ($timeSlot) {
                return $nuevoHorario['hour_start'] === $timeSlot->hour_start && $nuevoHorario['hour_end'] === $timeSlot->hour_end;
            });

            // Si la franja no está en los nuevos horarios y está disponible, eliminarla
            if (!$existsInNewSlots && $timeSlot->available) {
                $timeSlot->delete();
            }
        }

        foreach ($horariosNuevos as $hourStart) {
            $horaInicioTimestamp = strtotime($hourStart);

            // Crear dos franjas de 30 minutos cada una
            for ($i = 0; $i < 2; $i++) {
                $horaFin = date("H:i", strtotime("+$tamanoFranja minutes", $horaInicioTimestamp));

                // Verificar si la franja horaria ya existe
                $timeSlotExistente = TimeSlot::where('day_id', $day->id)
                    ->where('hour_start', date("H:i", $horaInicioTimestamp))
                    ->where('hour_end', $horaFin)
                    ->first();

                // Guardar el horario generado en el array para comparar luego
                $horariosNuevosConvertidos[] = [
                    'hour_start' => date("H:i", $horaInicioTimestamp),
                    'hour_end' => $horaFin
                ];

                // Si no existe, crear la franja horaria
                if (!$timeSlotExistente) {
                    TimeSlot::create([
                        'day_id' => $day->id,
                        'hour_start' => date("H:i", $horaInicioTimestamp),
                        'hour_end' => $horaFin,
                        'available' => true,
                    ]);
                }

                // Actualizar la hora de inicio para la siguiente franja
                $horaInicioTimestamp = strtotime($horaFin);
            }
        }

        return response()->json(['message' => 'Horario actualizado correctamente.'], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error al actualizar el horario.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function actualizarFranja(Request $request, $profileId)
{
    // Validar el formato del cuerpo de la solicitud
    $request->validate([
        '*' => 'array', // Las claves son días y apuntan a arreglos de horarios
        '*.0' => 'date_format:H:i', // Cada horario debe tener el formato H:i
    ]);

    // Validar que el perfil existe
    $profile = Profile::find($profileId);
    if (!$profile) {
        return response()->json(['message' => 'El perfil no existe.'], 404);
    }

    try {
        $diasHorarios = $request->all();
        $tamanoFranja = 30; // Tamaño de la franja en minutos

        // Configurar el rango de fechas desde hoy hasta el 31 de diciembre del presente año
        $fechaInicio = now();
        $fechaFin = now()->endOfYear();

        // Mapa de días en inglés a español
        $diasEnInglesAEspanol = [
            'Monday' => 'LUNES', 'Tuesday' => 'MARTES', 'Wednesday' => 'MIERCOLES',
            'Thursday' => 'JUEVES', 'Friday' => 'VIERNES', 'Saturday' => 'SABADO', 'Sunday' => 'DOMINGO'
        ];

        // Mantener un registro de los días procesados
        $diasProcesados = [];

        // Eliminar días no incluidos en la nueva configuración
        $diasExistentes = Day::where('profile_id', $profileId)->get();
        foreach ($diasExistentes as $day) {
            if (!in_array($day->id, $diasProcesados)) {

                $fechaActual = now()->format('Y-m-d');

                if ($day->fecha >= $fechaActual) {
                    // Verificar si el día tiene franjas ocupadas que no estén canceladas
                    $tieneFranjasOcupadas = TimeSlot::where('day_id', $day->id)
                                                    ->where(function ($query) {
                                                        $query->where('barber_available', true)
                                                            ->where('available', false);
                                                    })
                                                    ->exists();
                
                    // Si hay franjas ocupadas no canceladas, impedir la eliminación del día
                    if ($tieneFranjasOcupadas) {
                        return response()->json([
                            'message' => 'Tiene reservaciones pendientes. Por favor cancelar primero.',
                        ]);
                    }
                
                    // Si no tiene franjas ocupadas activas, eliminar el día y sus franjas
                    $day->timeSlots()->delete();
                    $day->delete();
                }
                
            }
        }
        

        // Iterar por el rango de fechas
        for ($fecha = $fechaInicio; $fecha->lte($fechaFin); $fecha->addDay()) {
            $diaNombre = $fecha->format('l'); // Día en inglés
            $diaNombreSolicitud = $diasEnInglesAEspanol[$diaNombre] ?? null;

            if ($diaNombreSolicitud && isset($diasHorarios[$diaNombreSolicitud])) {
                // Crear el día asociado al perfil si no existe
                $day = Day::firstOrCreate([
                    'profile_id' => $profileId,
                    'name' => $diaNombreSolicitud,
                    'fecha' => $fecha->format('Y-m-d'),
                ]);

                $diasProcesados[] = $day->id; // Registrar el día como procesado

                $horariosNuevos = $diasHorarios[$diaNombreSolicitud];
                $horariosNuevosConvertidos = [];

                // Generar franjas horarias nuevas
                foreach ($horariosNuevos as $horaInicio) {
                    $horaInicioTimestamp = strtotime($horaInicio);

                    // Crear dos franjas de 30 minutos cada una
                    for ($i = 0; $i < 2; $i++) {
                        $horaFin = date("H:i", strtotime("+$tamanoFranja minutes", $horaInicioTimestamp));

                        // Guardar el horario generado en el array para comparar luego
                        $horariosNuevosConvertidos[] = [
                            'hour_start' => date("H:i", $horaInicioTimestamp),
                            'hour_end' => $horaFin
                        ];

                        // Verificar si la franja horaria ya existe
                        $timeSlotExistente = TimeSlot::where('day_id', $day->id)
                            ->where('hour_start', date("H:i", $horaInicioTimestamp))
                            ->where('hour_end', $horaFin)
                            ->first();

                        // Si no existe, crear la franja horaria
                        if (!$timeSlotExistente) {
                            TimeSlot::create([
                                'day_id' => $day->id,
                                'hour_start' => date("H:i", $horaInicioTimestamp),
                                'hour_end' => $horaFin,
                                'available' => true,
                            ]);
                        }

                        // Actualizar la hora de inicio para la siguiente franja
                        $horaInicioTimestamp = strtotime($horaFin);
                    }
                }
            }
        }

        return response()->json(['message' => 'Franjas y días actualizados correctamente.'], 200);

    } catch (\Exception $err) {
        return response()->json([
            'message' => 'Ha ocurrido un error inesperado.',
            'error' => $err->getMessage(),
        ], 500);
    }
}



    /**
    * Método para eliminar una franja horaria específica.
    */
    public function eliminarFranja($id)
    {
        $timeSlot = TimeSlot::findOrFail($id);

        // Eliminar la franja horaria
        $timeSlot->delete();
        return response()->json(['message' => 'Franja horaria eliminada con éxito'], 200);
    }
}

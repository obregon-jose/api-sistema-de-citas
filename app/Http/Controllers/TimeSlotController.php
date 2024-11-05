<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Day;
use App\Models\TimeSlot;

class TimeSlotController extends Controller
{

    public function generarFranjaSemana(Request $request, $profileId)
{
   // Validar los datos de entrada
    $request->validate([
       'horas_inicio' => 'required|array', // Validar que se recibe un array de horas de inicio
       'horas_inicio.*' => 'date_format:H:i', // Validar que cada hora sea un formato de hora válido (ej. '09:00')
       'dias' => 'required|array', // Validar que se recibe un array de días
       'dias.*' => 'string', // Validar que cada día del array sea un string
    ]);

    // Validar que el perfil existe
    $profile = Profile::find($profileId);
    if (!$profile) {
        return Response()->json(['message' => 'El perfil no existe.'], 404);
    }

    try {
        // Obtener los días y las horas de inicio desde la solicitud
        $diasSemana = $request->input('dias');
        $horasInicio = $request->input('horas_inicio');
        $tamanoFranja = 30; // Tamaño de la franja en minutos

        // Configurar el rango de fechas desde hoy hasta el 31 de diciembre del presente año
        $fechaInicio = now();
        $fechaFin = now()->endOfYear();

        // Generar días y sus franjas horarias en el rango de fechas
        for ($fecha = $fechaInicio; $fecha->lte($fechaFin); $fecha->addDay()) {
            $diaNombre = $fecha->format('l'); // Nombre del día en inglés (ej. 'Monday', 'Tuesday')
            
            // Convertir a español si se requiere
            $diasEnInglesAEspanol = [
                'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miercoles',
                'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sabado', 'Sunday' => 'Domingo'
            ];
            $diaNombreEspanol = $diasEnInglesAEspanol[$diaNombre] ?? null;

            if (in_array($diaNombreEspanol, $diasSemana)) {
                // Crear el día asociado al perfil si no existe
                $day = Day::firstOrCreate([
                    'profile_id' => $profileId,            // Asegura que se incluya profile_id
                    'name' => $diaNombreEspanol,
                    'fecha' => $fecha->format('Y-m-d'),     // Asegura que se incluya la fecha
                ]);                
                foreach ($horasInicio as $horaInicio) {
                    // Generar la primera franja horaria de 30 minutos
                    $horaInicioTimestamp = strtotime($horaInicio);
                    $horaFin1 = date("H:i", strtotime("+$tamanoFranja minutes", $horaInicioTimestamp));

                    TimeSlot::create([
                        'day_id' => $day->id,
                        'hour_start' => $horaInicio,
                        'hour_end' => $horaFin1,
                        'available' => true,
                    ]);

                    // Generar la segunda franja horaria consecutiva
                    $horaInicio2 = $horaFin1;
                    $horaFin2 = date("H:i", strtotime("+$tamanoFranja minutes", strtotime($horaInicio2)));

                    TimeSlot::create([
                        'day_id' => $day->id,
                        'hour_start' => $horaInicio2,
                        'hour_end' => $horaFin2,
                        'available' => true,
                    ]);
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

    public function obtenerFranjasPorFecha(Request $request)
    {
    try {
        $request->validate([
            'fecha' => 'required|date_format:Y-m-d',
        ]);

        $fecha = $request->input('fecha');
        $day = Day::where('fecha', $fecha)->first();

        if (!$day) {
            return response()->json(['message' => 'No se encontraron franjas horarias para esta fecha.'], 404);
        }

        $timeSlots = TimeSlot::where('day_id', $day->id)->get(['hour_start', 'hour_end', 'available']);

        return response()->json([
            'fecha' => $day->fecha,
            'dia' => $day->name,
            'franjas' => $timeSlots,
        ], 200);
    } catch (\Exception) {
        return response()->json(['message' => 'Error interno del servidor.'], 500);
    }
    }


    // public function actualizarFranja(Request $request, $id)
    // {
    // // Validar los datos de entrada
    // $request->validate([
    //     'fecha' => 'required|date_format:Y-m-d',
    //     'hora_inicio' => 'required|date_format:H:i',
    //     'estado' => 'required|boolean',
    // ]);

    // // Obtener la fecha y la hora de inicio desde el request
    // $fecha = $request->input('fecha');
    // $horaInicio = $request->input('hora_inicio');

    // // Buscar el día correspondiente a la fecha dada
    // $day = Day::where('fecha', $fecha)->first();

    // if (!$day) {
    //     return response()->json(['message' => 'No se encontró el día para la fecha proporcionada.'], 404);
    // }

    // // Buscar la franja horaria por su ID y día correspondiente
    // $timeSlot = TimeSlot::where('id', $id)->where('day_id', $day->id)->first();

    // if (!$timeSlot) {
    //     return response()->json(['message' => 'No se encontró la franja horaria para la fecha y hora especificadas.'], 404);
    // }

    // // Actualizar los datos de la franja horaria
    // $timeSlot->available = $request->input('estado');

    // // Guardar los cambios
    // $timeSlot->save();

    // return response()->json(['message' => 'Franja horaria actualizada con éxito', 'data' => $timeSlot], 200);
    // }


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

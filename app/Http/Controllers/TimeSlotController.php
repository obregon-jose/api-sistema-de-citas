<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Agenda;
use App\Models\Week;
use App\Models\TimeSlot;
use Carbon\Carbon;

class TimeSlotController extends Controller
{
    /**
     * Método para crear una nueva agenda para un peluquero específico.
     */   
    public function store(Request $request, $profileId)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255', // Asegúrate de que el nombre esté validado
                'description' => 'nullable|string|max:255',
            ]);

            // Validar que el peluquero existe
            $profile = Profile::findOrFail($profileId);

            // Crear la agenda
            $agenda = Agenda::create([
                'profile_id' => $profile->id,
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
            ]);

            return response()->json([
                'message' => 'La agenda del peluquero fue creada con éxito.',
                'agenda' => $agenda,
            ], 201);
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $err->getMessage(),
            ], 400);
        }
    }

    /**
     * Método para generar franjas horarias para una semana específica dentro de una agenda.
     */
    public function generarFranjaSemana(Request $request, $agendaId)
    {
        // Validar los datos de entrada
        $request->validate([
            'fecha_inicio_semana' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'tamano_franja' => 'required|integer',
        ]);

        // Crear la semana
        $fechaInicioSemana = Carbon::parse($request->input('fecha_inicio_semana'));
        $fechaFinSemana = $fechaInicioSemana->copy()->addDays(6);

        $week = Week::create([
            'agenda_id' => $agendaId,
            'fecha_inicio' => $fechaInicioSemana->format('Y-m-d'),
            'fecha_fin' => $fechaFinSemana->format('Y-m-d')
        ]);

        // Días de la semana que se usarán en el ciclo
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        // Generar franjas horarias para cada día de la semana
        foreach ($diasSemana as $dia) {
            $horaInicio = $request->input('hora_inicio');
            $horaFin = $request->input('hora_fin');
            $tamanoFranja = $request->input('tamano_franja');

            // Convertir las horas de inicio y fin a timestamps para calcular las franjas
            $horaInicioTimestamp = strtotime($horaInicio);
            $horaFinTimestamp = strtotime($horaFin);

            // Crear franjas horarias en intervalos de $tamanoFranja minutos
            while ($horaInicioTimestamp < $horaFinTimestamp) {
                // Formatear hora de inicio de la franja
                $inicio = date("H:i", $horaInicioTimestamp);
                // Calcular el fin de la franja
                $horaSiguiente = strtotime("+$tamanoFranja minutes", $horaInicioTimestamp);
                $fin = date("H:i", $horaSiguiente);

                // Guardar la franja horaria en la base de datos
                TimeSlot::create([
                    'week_id' => $week->id, // Relacionar la franja con la semana
                    'dia' => $dia,           // Día de la semana
                    'inicio' => $inicio,     // Hora de inicio de la franja
                    'fin' => $fin,           // Hora de fin de la franja
                    'estado' => true         // Estado de la franja
                ]);

                // Avanzar al siguiente intervalo de franja
                $horaInicioTimestamp = $horaSiguiente;
            }
        }

        // Retornar respuesta exitosa con código 201
        return response()->json(['message' => 'Franjas horarias generadas para la semana'], 201);
    }

    /**
     * Método para obtener todas las semanas y sus franjas horarias asociadas a una agenda específica.
     */
    public function obtenerSemanas($agendaId)
    {
        // Cargar las semanas con sus franjas horarias asociadas
        $weeks = Week::with('timeSlots')->where('agenda_id', $agendaId)->get();
        
        // Retornar el resultado en formato JSON
        return response()->json($weeks);
    }

    /**
     * Método para obtener las franjas horarias de una semana específica.
     */
    public function obtenerSemanasPorFecha(Request $request, $agendaId)
{
    try {
        // Validar que la fecha de inicio esté presente y sea una fecha válida
        $request->validate([
            'fecha_inicio' => 'required|date',
        ]);

        // Obtener la fecha de inicio desde la solicitud
        $fechaInicio = $request->input('fecha_inicio');

        // Buscar las semanas que coincidan con la fecha de inicio dentro de la agenda
        $weeks = Week::with('timeSlots')
            ->where('agenda_id', $agendaId)
            ->where('fecha_inicio', $fechaInicio)
            ->get();

        // Verificar si se encontraron semanas
        if ($weeks->isEmpty()) {
            return response()->json(['message' => 'No se encontraron semanas para la fecha de inicio especificada.'], 404);
        }

        // Retornar las semanas encontradas
        return response()->json($weeks, 200);
    } catch (\Exception $err) {
        return response()->json([
            'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
            'error' => $err->getMessage(),
        ], 400);
    }
}


    public function obtenerAgendasPorBarbero($profileId)
    {
        // Cargar las agendas del barbero con sus semanas y franjas horarias asociadas
        $agendas = Agenda::with('weeks.timeSlots')->where('profile_id', $profileId)->get();

        // Retornar el resultado en formato JSON
        return response()->json($agendas);
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

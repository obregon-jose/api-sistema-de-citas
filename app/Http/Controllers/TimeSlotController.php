<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Day;
use App\Models\Agenda;
use App\Models\TimeSlot;

class TimeSlotController extends Controller{

    /**
     * Método para generar franjas horarias para una semana específica dentro de una agenda.
     */
    public function generarFranjaSemana(Request $request, $agendaId)
    {
    // Validar los datos de entrada
    $request->validate([
        'hora_inicio' => 'required',
        'hora_fin' => 'required',
        'tamano_franja' => 'required|integer',
        'dias' => 'required|array', // Validar que se recibe un array de días
        'dias.*' => 'string', // Validar que cada día del array sea un string
    ]);

    $agenda = Agenda::find($agendaId);
        if (!$agenda) {
            return response()->json(['message' => 'La agenda no existe.'], 404);
        }

    // Iniciar la transacción
    DB::beginTransaction();

    try {

        // Obtener los días desde la solicitud
        $diasSemana = $request->input('dias');

        // Generar días y sus franjas horarias
        foreach ($diasSemana as $diaNombre) {
            // Crear el día asociado a la agenda
            $day = Day::firstOrCreate([
                'agenda_id' => $agendaId,
                'nombre' => $diaNombre
            ]);
            
            $horaInicio = $request->input('hora_inicio');
            $horaFin = $request->input('hora_fin');
            $tamanoFranja = $request->input('tamano_franja');

            // Convertir las horas de inicio y fin a timestamps para calcular las franjas
            $horaInicioTimestamp = strtotime($horaInicio);
            $horaFinTimestamp = strtotime($horaFin);

            // Crear franjas horarias en intervalos de $tamanoFranja minutos
            while ($horaInicioTimestamp < $horaFinTimestamp) {
                $inicio = date("H:i", $horaInicioTimestamp);
                $horaSiguiente = strtotime("+$tamanoFranja minutes", $horaInicioTimestamp);
                $fin = date("H:i", $horaSiguiente);

                // Guardar la franja horaria en la base de datos
                TimeSlot::create([
                    'day_id' => $day->id,
                    'inicio' => $inicio,
                    'fin' => $fin,
                    'estado' => true
                ]);

                // Avanzar al siguiente intervalo de franja
                $horaInicioTimestamp = $horaSiguiente;
            }
        }

        DB::commit();
        return response()->json(['message' => 'Franjas horarias generadas'], 201);
    } catch (\Illuminate\Database\QueryException $queryException) {
        DB::rollBack();
        return response()->json([
            'message' => 'Error en la base de datos.',
            'error' => $queryException->getMessage(),
        ], 500);
    } catch (\Exception $err) {
        DB::rollBack();
        return response()->json([
            'message' => 'Ha ocurrido un error inesperado.',
            'error' => $err->getMessage(),
        ], 500);
    }
}


    public function actualizarFranja(Request $request, $id)
    {
        // Validar los datos de entrada
        $request->validate([
            'estado' => 'required|boolean',
        ]);

        // Buscar la franja horaria por su ID
        $timeSlot = TimeSlot::findOrFail($id);

        // Actualizar los datos de la franja horaria
        $timeSlot->estado = $request->input('estado');
        
        // Guardar los cambios
        $timeSlot->save();

        return response()->json(['message' => 'Franja horaria actualizada con éxito', 'data' => $timeSlot], 200);
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

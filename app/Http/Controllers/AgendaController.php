<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Profile;
use App\Models\TimeSlot;
use App\Models\Day;
use Illuminate\Http\Request;

class AgendaController extends Controller
{

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

    public function update(Request $request, $id)
    {
        // Validar los datos de entrada
        $request->validate([
            'name' => 'nullable|string|unique:agendas,name,' . $id, // Asegurar que el nombre sea único excepto para la agenda actual
            'description' => 'nullable|string',
            'estado' => 'boolean'
        ]);

        // Buscar la agenda por su ID
        $agenda = Agenda::find($id);

        if (!$agenda) {
            return response()->json(['message' => 'Agenda no encontrada'], 404);
        }

        // Actualizar la agenda con los nuevos datos
        $agenda->update($request->all());

        return response()->json([
            'message' => 'Agenda actualizada exitosamente',
            'data' => $agenda
        ]);
    }

    public function index()
    {
        $agendas = Agenda::all();
        return response()->json($agendas);
    }


    public function obtenerAgendaPorNombre(Request $request)
    {
    try {
        // Validar que el nombre de la agenda esté presente
        $validatedData = $request->validate([
            'name' => 'required|string'
        ]);

        $nombreAgenda = $validatedData['name'];

        // Buscar la agenda por su nombre y perfil
        $agenda = Agenda::where('name', $nombreAgenda)
                        ->first();

        // Verificar si la agenda existe y pertenece al perfil
        if (!$agenda) {
            return response()->json(['message' => 'No se encontró ninguna agenda con el nombre especificado para el perfil logeado.'], 404);
        }

        // Obtener las franjas horarias de la agenda
        $timeSlots = TimeSlot::with('day')->whereHas('day.agenda', function ($query) use ($agenda) {
            $query->where('id', $agenda->id);
        })->get();

        $dias = Day::with('timeSlots') // Carga las franjas horarias relacionadas
            ->where('agenda_id', $agenda->id)->get();
            
        $resultado = [
            'dias' => $dias->map(function ($dia) {
                return [
                    'nombre' => $dia->nombre,
                    'franjas' => $dia->timeSlots->map(function ($timeSlot) {
                        return [
                            'id' => $timeSlot->id,
                            'inicio' => $timeSlot->inicio,
                            'fin' => $timeSlot->fin,
                            'estado' => $timeSlot->estado,
                        ];
                    }),
                ];
            }),
        ];
        
        return response()->json($resultado, 200);
    } catch (\Exception $err) {
        return response()->json([
            'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
            'error' => $err->getMessage(),
        ], 400);
        }
    }


    public function obtenerAgendasPorBarbero($profileId)
    {
        // Validar el profileId
        if (empty($profileId) || !is_numeric($profileId)) {
            return response()->json(['message' => 'ID de perfil no válido.'], 400);
        }

        try {
            // Cargar agendas con días y franjas horarias
            $agendas = Agenda::with('days.timeSlots')
                ->where('profile_id', $profileId)
                ->get();

            // Verificar si se encontraron agendas
            if ($agendas->isEmpty()) {
                return response()->json(['message' => 'No se encontraron agendas para el barbero especificado.'], 404);
            }

            // Retornar el resultado en formato JSON
            return response()->json($agendas, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener las agendas.', 'error' => $e->getMessage()], 500);
        }
    }

}

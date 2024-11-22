<?php

namespace App\Http\Controllers;

use App\Jobs\SendReservationEmail;
use App\Models\AttentionQuote;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Models\Day;
use App\Models\Profile;
use App\Models\TimeSlot;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // se puede poner filtro aqui- revisar logica
        $reservations = Reservation::with(['attentionQuote'])->get();
        return response()->json([
            'reservations' => $reservations,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
            $validatedData = $request->validate([
                //cita
                'client_name' => 'nullable|string',
                'barber_id' => 'required|exists:users,id',
                'service_details' => 'required|json',
                'total_paid' => 'required|integer',
                //reserva
                'client_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'time'  => 'required',
                // 'end_time'  => 'date_format:H:i',
                'status' => 'sometimes|in:1,pending', // Siempre queda pendiente?
            ]);

            if (Reservation::where('client_id', $validatedData['client_id'])
                ->where('date', $validatedData['date'])
                ->where('time', $validatedData['time'])
                ->exists()) {
                return response()->json([
                    'message' => 'Ya tienes una reserva para esta fecha y hora.',
                ], 400);
            }
            // Crear la cita
            $attentionQuote = AttentionQuote::create($validatedData);
            $validatedData['quote_id'] = $attentionQuote->id;
            // crear la reserva
            $reservation = Reservation::create($validatedData);

            //NOTA: Código para actualizar la disponibilidad del barbero
            ////////////////////////////////////////////////////////////////////////////////////
            // Obtener la fecha y la hora de inicio desde la solicitud
            $date = $request->input('date');
            $hourStart = $request->input('time');
            
            // Buscar el día correspondiente usando la fecha
            $day = Day::where('profile_id',$request->barber_id)
                        ->where('fecha', $date)
                        ->first();

            // if (!$day) {
            //     return response()->json(['message' => 'No se encontró el día para la fecha proporcionada.'], 404);
            // }

            // Buscar la franja horaria usando el día y la hora de inicio
            $timeSlot = TimeSlot::where('day_id', $day->id)
                                ->where('hour_start', $hourStart)
                                ->first();

            // if (!$timeSlot) {
            //     return response()->json(['message' => 'No se encontró la franja horaria para la fecha y hora proporcionadas.'], 404);
            // }

            // Marcar la franja como ocupada
            $timeSlot->available = false;
            $timeSlot->save();
            ////////////////////////////////////////////////////////////////////////////////////
            
            // Enviar correo de la reserva
            SendReservationEmail::dispatch($attentionQuote, $reservation);
                        
            // Devolver respuesta
            return response()->json([
                'message' => 'Su Reserva se a generado con éxito',
                // 'reservation' => $reservation,
                // 'quote' => $attentionQuote,
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }


    /**
     * Display the specified resource.
     */
    public function showReservationsClient($clientId)
    {
        $pendingReservations = Reservation::where('client_id', $clientId)
        //->where('status', 'pending') // Filtrar por estado "pending"
        ->with('attentionQuote') // Cargar la relación de atención
        ->get();
        
        // Decodificar el campo service_details en la relación attentionQuote
        $pendingReservations->each(function ($reservation) {
            if ($reservation->attentionQuote) {
                $reservation->attentionQuote->service_details = json_decode($reservation->attentionQuote->service_details);
                // Obtener el nombre del barbero a través de la relación attentionQuote
            $barber = Profile::where('user_id', $reservation->attentionQuote->barber_id)->first();
            if ($barber) {
                $reservation->barber_name = $barber->user->name;
            } else {
                $reservation->barber_name = null;
            }
            }
            
        });
        // $barber = Profile::where('user_id', $pendingReservations->attentionQuote->barber_id)->first();

        return response()->json([
            // 'barber' => $barber,
            'reservations' => $pendingReservations,
        ], 200);
    
    }

    public function showReservationsBarber($barberId)
    {
        $pendingReservations = AttentionQuote::where('barber_id', $barberId)
        ->where('status', 'pending') // Filtrar por estado "pending"
        ->with('reservation') // Cargar la relación de atención
        ->get();
        // Decodificar el campo service_details
        $pendingReservations->each(function ($reservation) {
            $reservation->service_details = json_decode($reservation->service_details);
        });

        return response()->json([
            'reservationsPending' => $pendingReservations,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        try {
            $validatedData = $request->validate([
                'client_name' => 'nullable|string',
                'barber_id' => 'required|exists:users,id',
                'service_details' => 'required|string',
                'total_paid' => 'required|integer',

                'client_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'time'  => 'required|date_format:H:i',
                'end_time'  => 'required|date_format:H:i',
                'note' => 'nullable|string', //esto va en delete
                'status' => 'sometimes|in:1,pending,2,completed,3,cancelled,4,expired',
            ]);
            
            if (Reservation::where('client_id', $validatedData['client_id'])
                ->where('date', $validatedData['date'])
                ->where('time', $validatedData['time'])
                ->exists()) {
                return response()->json([
                    'message' => 'Ya tienes una reserva para esta fecha y hora.',
                ], 400);
            }
            $reservation = Reservation::where('quote_id', $id)->firstOrFail();
            $reservation->update($validatedData); //pendiente que esa hora no este tomada

            $attentionQuote = AttentionQuote::findOrFail($id); 
            $attentionQuote->update($validatedData);
    
            return response()->json([
                'message' => 'Reserva actualizada con éxito',
                'reservation' => $reservation,
                'attentionQuote' => $attentionQuote,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 400);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        try {
            $quote = AttentionQuote::findOrFail($id);
            $quote->delete(); //elimina en cascada
    
            return response()->json([
                'message' => 'Reserva eliminada con éxito',
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function expiredReservations()
    {
    //     try {
    //         // Configuración de vencimiento (60 minutos)
    //         $expirationInterval = 60; // Número de minutos para el vencimiento
    //         $currentDateTime = Carbon::now(); // Fecha y hora actuales
        
    //         // Cálculo de la fecha y hora límite de vencimiento
    //         $expirationTime = $currentDateTime->copy()->subMinutes($expirationInterval)->toDateTimeString();
        
    //         // Actualizar reservas vencidas
    //         $updatedReservations = Reservation::where(function ($query) use ($expirationTime) {
    //                 // Condición para las reservas con fecha y hora vencida
    //                 $query->where('status', 'pending') // Reservas pendientes
    //                       ->where(function ($q) use ($expirationTime) {
    //                           $q->whereRaw("CONCAT(date, ' ', time) <= ?", [$expirationTime]);
    //                       });
    //             })
    //             ->update(['status' => 'expired']); // Actualiza a estado 'expired'
        
    //         // Actualizar citas asociadas si no están expiradas
    //         $updatedQuotes = AttentionQuote::whereHas('reservation', function ($query) use ($expirationTime) {
    //                 // Verifica si la reserva asociada ya expiró
    //                 $query->where('status', 'expired');
    //             })
    //             ->where('status', '!=', 'expired') // Evita sobrescribir citas ya expiradas
    //             ->update(['status' => 'expired']); // Actualiza a estado 'expired'
        
    //         return response()->json([
    //             'message' => 'Reservas y citas actualizadas con éxito.',
    //             'updated_reservations' => $updatedReservations,
    //             'updated_quotes' => $updatedQuotes,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         // Manejo de errores
    //         return response()->json([
    //             'error' => 'Ocurrió un error al actualizar las reservas.',
    //             'details' => $e->getMessage(),
    //         ], 500);
    //     }
    }

    public function updateReservations(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        try {
            // Buscar la cita de atención
            $attentionQuote = AttentionQuote::findOrFail($id); 
            $attentionQuote->update(['status' => $status]);
            // Actualizar el estado de las reservas relacionadas
            $reservations = Reservation::where('quote_id', $attentionQuote->id)->get();
            foreach ($reservations as $reservation) {
                $reservation->update(['status' => $status]);

                // Si el estado es "cancelled", marcar la franja horaria como disponible
                if ($status === 'cancelled') {
                    $date = $reservation->date;
                    $hourStart = $reservation->hour_start;
                    // Buscar el día correspondiente
                    $day = Day::where('profile_id', $request->barber_id)
                            ->where('fecha', $date)
                            ->first();
                    if ($day) {
                        // Buscar la franja horaria
                        $timeSlot = TimeSlot::where('day_id', $day->id)
                                            ->where('hour_start', $hourStart)
                                            ->first();
                        if ($timeSlot) {
                            $timeSlot->available = true;
                            $timeSlot->save();
                        }
                    }
                }
            }
            return response()->json([
                'message' => 'Reservas y citas actualizadas con éxito.'
            ], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'error' => 'Ocurrió un error al actualizar las reservas.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    

}

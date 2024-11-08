<?php

namespace App\Http\Controllers;

use App\Models\AttentionQuote;
use App\Models\Reservation;
use Illuminate\Http\Request;

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

            // $roleName = Role::find($defaultRoleId)->name;
            
            // Enviar correo 
            // Mail::to($user->email)->send(new WelcomeEmail($user, $roleName, $passwordGenerado ?? ''));
            
            // Devolver respuesta
            return response()->json([
                'message' => 'Su Reserva se a generado con éxito',
                'reservation' => $reservation,
                'quote' => $attentionQuote,
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
    public function show(Reservation $reservation)
    {
        //
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
                'status' => 'sometimes|in:1,pending,2,completed,3,cancelled',
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
}

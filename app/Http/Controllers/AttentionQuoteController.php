<?php

namespace App\Http\Controllers;

use App\Models\AttentionQuote;
use App\Models\Reservation;
use Illuminate\Http\Request;

class AttentionQuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        // se puede poner filtro aqui- revisar logica
        $attentionQuote = AttentionQuote::with(['reservation'])->get();
        return response()->json([
            'attentionQuote' => $attentionQuote,
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
                'isReservation' => 'required|boolean',
                'client_name' => 'nullable|string',
                'barber_id' => 'required|exists:users,id',
                'service_details' => 'required|string',
                'total_paid' => 'required|integer',
                //reserva
                'cliente_id' => 'nullable|exists:users,id',
                'date' => 'sometimes|date',
                'time'  => 'sometimes|date_format:H:i',
                'status' => 'sometimes|in:1,pending,2,completed,3,cancelled',
                // 'note' , // opcional
            ]);

            if ($validatedData['isReservation'] == true) {
                $validatedData['status'] = 'pending';

                if (Reservation::where('date', $validatedData['date'])
                    ->where('time', $validatedData['time'])
                    ->exists()) {
                    return response()->json([
                        'message' => 'Ya tienes una reserva para esta fecha y hora.',
                    ], 400);
                }
            } else {
                $validatedData['status'] = 'completed'; // revisar si se manda 
            }
            
            // Crear la cita
            $attentionQuote = AttentionQuote::create($validatedData);
            $validatedData['quote_id'] = $attentionQuote->id;
            
            // crear la reserva
            if($validatedData['isReservation'] == true){
                $reservation = Reservation::create($validatedData);
            }

            // $roleName = Role::find($defaultRoleId)->name;
            
            // Enviar correo 
            // Mail::to($user->email)->send(new WelcomeEmail($user, $roleName, $passwordGenerado ?? ''));
            
            // Devolver respuesta
            return response()->json([
                'message' => 'Su Reserva se a generado con éxito',
                'attentionQuote' => $attentionQuote,
                'reservation' => $reservation ?? 'Esta atención no tuvo reserva',
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
    public function show(AttentionQuote $attentionQuote)
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

                'client_id' => 'nullable|exists:users,id',
                'date' => 'sometimes|date',
                'time'  => 'sometimes|date_format:H:i',
                'status' => 'sometimes|in:1,pending,2,completed,3,cancelled',
            ]);

            $attentionQuote = AttentionQuote::findOrFail($id);
            $attentionQuote->update($validatedData);
            
            if (Reservation::where('quote_id', $id)->exists()){
                if (Reservation::where('date', $validatedData['date'])
                    ->where('time', $validatedData['time'])
                    ->exists()) {
                    return response()->json([
                        'message' => 'Ya tienes una reserva para esta fecha y hora.',
                    ], 400);
                }
                $reservation = Reservation::where('quote_id', $id)->firstOrFail();
                $reservation->update($validatedData); //pendiente que esa hora no este tomada
            }
            
    
            return response()->json([
                'message' => 'Reserva actualizada con éxito',
                'attentionQuote' => $attentionQuote,
                'reservation' => $reservation ?? 'Esta atención no tuvo reserva',
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
            $attentionQuote = AttentionQuote::findOrFail($id);
            $attentionQuote->delete(); //elimina en cascada 
    
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

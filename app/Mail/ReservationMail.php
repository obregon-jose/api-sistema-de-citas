<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $client_name;
    public $barber_name;
    public $services_details;
    public $total_paid;
    public $date;
    public $time;
    public $status;
    public $role;


    /**
     * Create a new message instance.
     */
    public function __construct($role, $client_name, $barber_name, $services_details, $total_paid, $date, $time, $status)
    {
        //
        $this->role = $role;
        $this->client_name = $client_name;
        $this->barber_name = $barber_name;
        $this->services_details = $services_details;
        $this->total_paid = $total_paid;
        $this->date = $date;
        $this->time = $time;
        $this->status = $status;
    }

    public function build()
    {
        // Convertir el JSON de services_details a una cadena separada por comas
        $services_details_array = json_decode($this->services_details, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            // Si se decodifica correctamente, unir los nombres con comas
            $this->services_details = implode(', ', array_column($services_details_array, 'nombre'));
        }

        return $this->with(
            [
                'role' => $this->role,
                'client_name' => $this->client_name,
                'barber_name' => $this->barber_name,
                'services_details' => $this->services_details, 
                'total_paid' => $this->total_paid,
                'date' => $this->date,
                'time' => $this->time,
                'status' => $this->status,
            ]
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->status == 'cancelled') {
            return new Envelope(
                subject: 'Reservación Cancelada',
            );
        } else {
            return new Envelope(
                subject: 'Nueva Reservación',
            );
        }
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

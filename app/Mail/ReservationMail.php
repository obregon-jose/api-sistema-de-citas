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


    /**
     * Create a new message instance.
     */
    public function __construct($client_name, $barber_name, $services_details, $total_paid, $date, $time, $status)
    {
        //
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
        return $this->with(
            [
                'client_name' => $this->client_name,
                'barber_name' => $this->barber_name,
                'services_details' => $this->services_details, //esta en json cambiar
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
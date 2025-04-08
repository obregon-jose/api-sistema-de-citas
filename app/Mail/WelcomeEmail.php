<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $role;
    public $passwordPath  = "GENRAR ENLACE PARA IR A ESTABLECER CLAVE";


    /**
     * Create a new message instance.
     */
    public function __construct($user, $role)
    {
        //
        $this->user = $user;
        $this->role = $role;
    }
    
    public function build()
    {
        return $this->with(
            [
                'user' => $this->user, 
                'role' => $this->role, 
                'passwordPath' => $this->passwordPath,
            ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
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

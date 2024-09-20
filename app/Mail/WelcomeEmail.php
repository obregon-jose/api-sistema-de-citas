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
    public $password;


    /**
     * Create a new message instance.
     */
    public function __construct($user, $role, $password)
    {
        //
        $this->user = $user;
        $this->role = $role;
        $this->password = $password;
    }
    
    public function build()
    {
        return $this->view('emails.welcome')
                    ->subject('Bienvenido a nuestra aplicación')
                    ->with(['user' => $this->user, 'role' => $this->role, 'role' => $this->password]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido',
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

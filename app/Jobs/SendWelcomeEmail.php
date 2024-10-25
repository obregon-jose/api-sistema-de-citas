<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $roleName;
    protected $passwordGenerado;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $roleName, $passwordGenerado)
    {
        $this->user = $user;
        $this->roleName = $roleName;
        $this->passwordGenerado = $passwordGenerado;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Mail::to($this->user->email)->send(new WelcomeEmail($this->user, $this->roleName, $this->passwordGenerado));
    }
}

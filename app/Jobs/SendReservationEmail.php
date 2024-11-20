<?php

namespace App\Jobs;

use App\Mail\ReservationMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendReservationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $client_id;
    public $barber_id;
    public $client_name;
    public $barber_name;
    public $services_details;
    public $total_paid;
    public $date;
    public $time;
    public $status;


    /**
     * Create a new job instance.
     */
    public function __construct($attentionQuote, $reservation)
    {
        //
        $this->client_id = $attentionQuote['client_id'];
        $this->barber_id = $attentionQuote['barber_id'];
        $this->client_name = $attentionQuote['client_name'];
        $this->barber_name = User::find($attentionQuote['barber_id'])->name;
        $this->services_details = $attentionQuote['service_details'];
        $this->total_paid = $attentionQuote['total_paid'];
        $this->date = $reservation['date'];
        $this->time = $reservation['time'];
        $this->status = $reservation['status'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userBarber = User::find($this->barber_id);
        Mail::to($userBarber->email)->send(new ReservationMail($this->client_name, $this->barber_name, $this->services_details, $this->total_paid, $this->date, $this->time, $this->status));
        #$userClient = User::find(2);
        #Mail::to($userClient->email)->send(new ReservationMail($this->client_name, $this->barber_name, $this->services_details, $this->total_paid, $this->date, $this->time, $this->status));
    }
}
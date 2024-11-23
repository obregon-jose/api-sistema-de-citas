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
        $this->barber_id = $attentionQuote['barber_id'];
        $this->client_name = $attentionQuote['client_name'];
        $this->barber_name = User::find($attentionQuote['barber_id'])->name;
        $this->services_details = $attentionQuote['service_details'];
        $this->total_paid = $attentionQuote['total_paid'];
        $this->client_id = $reservation['client_id'];
        $this->date = $reservation['date'];
        $this->time = $reservation['time'];
        $this->status = $reservation['status'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $Client = User::find($this->client_id);
        Mail::to($Client->email)->send(new ReservationMail(1, $this->client_name, $this->barber_name, $this->services_details, $this->total_paid, $this->date, $this->time, $this->status));
        $Barber = User::find($this->barber_id);
        Mail::to($Barber->email)->send(new ReservationMail(2, $this->client_name, $this->barber_name, $this->services_details, $this->total_paid, $this->date, $this->time, $this->status));
    
    }
}

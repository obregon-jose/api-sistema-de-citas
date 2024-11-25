<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // /**
    //  * Create a new event instance.
    //  */
    // public function __construct()
    // {
    //     //
    // }

    // /**
    //  * Get the channels the event should broadcast on.
    //  *
    //  * @return array<int, \Illuminate\Broadcasting\Channel>
    //  */
    // public function broadcastOn(): array
    // {
    //     return [
    //         new PrivateChannel('channel-name'),
    //     ];
    // }

    public $servicio;

    public function __construct($servicio)
    {
        $this->servicio = $servicio; // Los datos que se enviarán
    }

    public function broadcastOn()
    {
        // Canal público o privado para transmitir
        return new Channel('servicios');
    }

    public function broadcastAs()
    {
        return 'servicio-actualizado';
        //return 'App\\Events\\ServiceUpdate';
    }

    // public function broadcastWith()
    // {
        
    //     return [
    //         'servicio' => $this->servicio, // Los datos que el cliente recibirá
    //     ];
    // }
}

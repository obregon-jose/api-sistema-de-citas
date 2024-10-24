<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttentionQuote extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_name',
        'barber_id',
        'service_details',
        'total_paid',
        'status',
    ];

    // Relación con los perfiles
    public function profiles()
    {
        return $this->belongsTo(Profile::class);
    }
    
    // Relación uno a uno inversa con la tabla de reservas
    public function reservation()
    {
        return $this->hasOne(Reservation::class, 'quote_id');
    }
}

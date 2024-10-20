<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'date',
        'time',
        'status',
        'quote_id',
        'note',
    ];

    // Relación con los perfiles
    public function profiles()
    {
        return $this->belongsTo(Profile::class);
    }
    
    // Relación uno a uno con la tabla de Atenncion
    public function attentionQuote()
    {
        return $this->belongsTo(AttentionQuote::class, 'quote_id');
    }
}

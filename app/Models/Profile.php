<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id', 'role_id'];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con los roles
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Relación con el reserva - Cliente id
    public function reservation()
    {
        return $this->hasMany(Reservation::class);
    }

    // Relación con el AttentionQuote - Peluquero id
    public function attention()
    {
        return $this->hasMany(AttentionQuote::class);
    }

    // // Relacion con dias
    // public function day()
    // {
    //     return $this->hasMany(Day::class);
    // }

     // Relación con ProfileAvailability (Uno a muchos)
     public function availabilities()
     {
         return $this->hasMany(BarberAvailability::class);
     }
}

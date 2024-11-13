<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    // protected $fillable = ['day_id', 'hour_start', 'hour_end', 'available'];

    // public function day()
    // {
    //     return $this->belongsTo(Day::class);
    // }


    /*---------- NUEVO MODELO -----------*/
    protected $fillable = ['hour_start', 'hour_end'];

    // Relación con ProfileAvailability (Uno a muchos)
    public function barberAvailabilities()
    {
        return $this->hasMany(BarberAvailability::class);
    }

}

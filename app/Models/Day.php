<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_id',
        'nombre'
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }

    public function timeSlots()
    { 
        return $this->hasMany(TimeSlot::class);
    }
}

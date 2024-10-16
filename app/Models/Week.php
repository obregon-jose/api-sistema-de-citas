<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Week extends Model
{
    use HasFactory;

    protected $fillable = ['agenda_id','fecha_inicio', 'fecha_fin'];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }
}

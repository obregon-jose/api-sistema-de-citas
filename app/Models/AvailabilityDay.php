<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityDay extends Model
{
    use HasFactory;

    protected $fillable = ['day_of_week'];

    // Relación con ProfileAvailability (Uno a muchos)
    public function barberAvailabilities()
    {
        return $this->hasMany(BarberAvailability::class);
    }

}

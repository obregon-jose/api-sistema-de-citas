<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberAvailability extends Model
{
    use HasFactory;

    // protected $fillable = ['profile_id', 'day_id', 'time_slot_id', 'status'];
    protected $fillable = ['profile_id', 'time_slot_id'];

    // Relación con el modelo Profile
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

}

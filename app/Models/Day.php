<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'name',
        'fecha'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function timeSlots()
    { 
        return $this->hasMany(TimeSlot::class);
    }
}

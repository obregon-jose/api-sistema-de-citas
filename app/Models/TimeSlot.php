<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = ['day_id', 'inicio', 'fin', 'estado'];

    public function day()
    {
        return $this->belongsTo(Day::class);
    }
}

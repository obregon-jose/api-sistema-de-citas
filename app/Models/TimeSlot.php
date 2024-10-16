<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = ['week_id', 'dia', 'inicio', 'fin', 'estado'];

    public function week()
    {
        return $this->belongsTo(Week::class);
    }
}

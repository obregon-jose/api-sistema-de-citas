<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'name',
        'description',
        ];

    public function profiles()
    {
        return $this->belongsTo(Profile::class);
    }

    public function days()
    {
        return $this->hasMany(Day::class);
    }
}


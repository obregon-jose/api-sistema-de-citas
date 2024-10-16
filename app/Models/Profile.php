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

    //relacion con la agendas
    public function agendas()
    {
        return $this->hasMany(Agenda::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    
    protected $fillable = ['name'];

    // Relación con los perfiles
    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }
}

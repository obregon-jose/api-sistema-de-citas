<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id',
        'nickname',
        'phone',
        'photo',
        'note'
    ];

    // Relación uno a uno inversa con la tabla de usuarios
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

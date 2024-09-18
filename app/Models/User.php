<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    // Relación con los perfiles
    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }

    // Verifica si el usuario tiene un rol específico
    public function hasRole($role)
    {
        return $this->profiles()->whereHas('role', function ($query) use ($role) {
            $query->where('name', $role);
        })->exists();
    }

    // public function role()
    // {
    //     return $this->profile->role; // Acceso al rol desde el perfil
    // }

    // public function hasRole($role)
    // {
    //     return $this->role->name === $role;
    // }

    // public function hasRole($role) {
    //     return $this->roles()->where('name', $role)->exists();
    // }



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;  // Add this import

//implements MustVerifyEmail

class User extends Authenticatable  
{
    use HasFactory, Notifiable, HasApiTokens;  // Add HasApiTokens here

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
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

    /**
     * Handle the model's "creating" event to set a default role if none is provided.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Ensure a default role if none is provided
            if (empty($user->role)) {
                $user->role = 'employee'; // Default role
            }
        });
    }
}

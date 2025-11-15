<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // AJOUTER CETTE LIGNE

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // MODIFIER CETTE LIGNE

    protected $fillable = [
        'first_name',
        'last_name', 
        'email',
        'phone',
        'password',
        'gender',
        'roles',
        'remarks',
        'is_admin',
        'last_login_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_admin' => 'boolean',
        'roles' => 'array'
    ];

    // Relations existantes
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favori::class);
    }

    public function surveyAnswers()
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    public function sharedCarts()
    {
        return $this->hasMany(SharedCart::class);
    }

    public function departures()
    {
        return $this->hasMany(Departure::class);
    }

    // Nouvelle relation pour les tickets SAV
    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    // Méthode pour mettre à jour la dernière connexion
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    // Accessor pour le nom complet (AJOUTER)
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
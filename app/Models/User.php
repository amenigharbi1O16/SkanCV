<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * MISSION : compte HR Staff authentifiable via Sanctum (HasApiTokens).
 * Seul acteur du système — crée offres, uploade CVs, consulte analyses.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Attributs assignables en masse
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // Attributs masqués lors de la sérialisation
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casts des attributs du modèle
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Hashe automatiquement le mot de passe
        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * MISSION : formater le profil HR Staff en JSON sûr (sans password).
 */
class UserResource extends JsonResource
{
    /**
     * Convertit l'utilisateur en tableau (sans les données sensibles).
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'created_at' => $this->created_at,
        ];
    }
}
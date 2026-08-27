<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreJobPostingRequest valide les données envoyées lors de la
 * CRÉATION d'une nouvelle offre d'emploi (POST /api/job-postings).
 *
 * Concrètement, quand une requête HTTP arrive sur
 * JobPostingController::store(), Laravel :
 * 1. Instancie cette classe
 * 2. Appelle authorize() → si false, renvoie une erreur 403
 * 3. Appelle rules() → valide les données selon ces règles
 * 4. Si la validation échoue → renvoie automatiquement une erreur
 *    422 avec le détail des champs invalides, SANS jamais exécuter
 *    le code de store()
 * 5. Si tout est valide → store() s'exécute, et $request->validated()
 *    contient les données propres
 */
class StoreJobPostingRequest extends FormRequest
{
    /**
     * authorize() : l'accès est contrôlé par auth:api (JWT) sur la route,
     * pas ici. Retourne true car seuls les HR authentifiés atteignent ce point.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * rules() définit les règles de validation, champ par champ.
     * Retourne un tableau associatif : nom_du_champ => règles.
     */
    public function rules(): array
    {
        return [
            // 'required' : le champ doit être présent et non vide
            // 'string' : doit être une chaîne de caractères
            // 'max:255' : correspond à la limite VARCHAR(255) définie
            // dans la migration (string() par défaut) — on fait
            // exprès de faire correspondre cette règle à la
            // contrainte SQL réelle, pour éviter un cas où la
            // validation PHP accepterait un titre trop long que
            // MySQL rejetterait ensuite avec une erreur SQL brute.
            'title' => 'required|string|max:255',

            // 'string' seul (pas de max) : correspond au type text()
            // en base, qui n'a pas de limite pratique de taille.
            'description' => 'required|string',

            // 'array' : le champ doit être un tableau PHP (envoyé en
            // JSON depuis React comme un tableau ["PHP","Laravel"]).
            'required_skills' => 'required|array',

            // 'required_skills.*' : validation appliquée à CHAQUE
            // élément du tableau required_skills. Ici, chaque
            // compétence doit être une string non vide.
            'required_skills.*' => 'required|string',
        ];
    }
}
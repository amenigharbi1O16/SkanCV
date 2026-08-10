<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * up() : ce qui se passe quand on lance `sail artisan migrate`.
     * On décrit ici la structure de la table job_postings.
     */
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            // id() = clé primaire auto-incrémentée (BIGINT UNSIGNED).
            // C'est cette valeur qui sera référencée en foreign key
            // par la table `cvs` plus tard (job_posting_id).
            $table->id();

            // Titre de l'offre, ex: "Développeur Laravel Senior".
            // string() = VARCHAR(255) par défaut, largement suffisant.
            $table->string('title');

            // Description complète de l'offre (missions, contexte...).
            // text() = pas de limite de taille pratique (contrairement
            // à string), adapté à du contenu long.
            $table->text('description');

            // Compétences requises pour le poste.
            // On stocke en JSON pour avoir une liste structurée
            // (ex: ["Laravel", "PHP", "MySQL"]) plutôt qu'une simple
            // string non structurée. Cette liste sera comparée aux
            // compétences extraites du CV pour calculer le score.
            $table->json('required_skills');

            // created_at + updated_at (gérés automatiquement par
            // Eloquent). Utile pour savoir depuis quand l'offre est
            // publiée et si elle a été modifiée.
            $table->timestamps();
        });
    }

    /**
     * down() : l'opération inverse, appelée par
     * `sail artisan migrate:rollback`. Elle doit annuler
     * exactement ce que up() a fait.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
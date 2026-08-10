<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();

            // === CLÉ ÉTRANGÈRE VERS job_postings ===
            // foreignId() crée une colonne BIGINT UNSIGNED nommée
            // job_posting_id.
            // constrained() ajoute automatiquement la contrainte de
            // clé étrangère vers la table `job_postings` (colonne id),
            // Laravel devine le nom de la table à partir du nom de
            // colonne (job_posting_id -> job_postings).
            // cascadeOnDelete() = si l'offre d'emploi est supprimée,
            // tous les CVs associés sont supprimés automatiquement
            // (cohérence référentielle : un CV n'a pas de sens sans
            // son offre).
            $table->foreignId('job_posting_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Informations du candidat qui a soumis le CV.
            $table->string('candidate_name');
            $table->string('candidate_email');

            // Chemin de stockage du fichier PDF sur le disque Laravel.
            // Rappel architecture : storage/app/private/cvs/...
            // On ne stocke JAMAIS une URL publique ici, uniquement
            // le chemin interne (le fichier n'est jamais exposé
            // directement, on doit passer par un contrôleur qui
            // vérifie les droits d'accès).
            $table->string('file_path');

            // Texte brut extrait du PDF par le endpoint FastAPI /extract.
            // nullable() car au moment de l'upload, l'extraction n'a
            // pas encore eu lieu (elle se fait de façon asynchrone
            // via la queue Redis).
            $table->longText('extracted_text')->nullable();

            // Compétences extraites du texte du CV par le microservice
            // (ex: ["PHP", "Laravel", "Git"]). Stockées en JSON pour
            // pouvoir être comparées facilement aux required_skills
            // de la job_posting. nullable() pour la même raison que
            // extracted_text : rempli après traitement asynchrone.
            $table->json('extracted_skills')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cvs');
    }
};
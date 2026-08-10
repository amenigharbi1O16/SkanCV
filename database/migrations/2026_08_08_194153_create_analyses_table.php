<?php

use App\Enums\AnalysisStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();

            // === CLÉ ÉTRANGÈRE VERS cvs, AVEC CONTRAINTE UNIQUE ===
            // foreignId()->constrained() : même logique que pour
            // job_posting_id ci-dessus.
            // ->unique() : C'EST LA LIGNE CLÉ de toute la relation
            // "CV 1 -- 1 Analysis". Sans elle, rien n'empêcherait au
            // niveau base de données de créer deux analyses pour un
            // même CV. Avec elle, MySQL lève une erreur d'intégrité
            // si on essaie d'insérer un 2e analyses.cv_id identique.
            // C'est une garantie au niveau SGBD, pas seulement au
            // niveau applicatif (PHP) — plus robuste.
            $table->foreignId('cv_id')
                  ->constrained()
                  ->cascadeOnDelete()
                  ->unique();

            // Statut du cycle de vie, basé sur l'enum AnalysisStatus.
            // ->default(AnalysisStatus::PENDING->value) : toute
            // nouvelle analyse démarre à l'état PENDING avant d'être
            // prise en charge par le worker de la queue.
            $table->string('status')
                  ->default(AnalysisStatus::PENDING->value);

            // Score de similarité cosinus calculé par le endpoint
            // FastAPI /score (entre le CV et la job_posting).
            // decimal(5,4) = 5 chiffres au total, 4 après la virgule,
            // donc une plage de 0.0000 à 9.9999 — largement suffisant
            // puisqu'un cosinus est toujours entre -1 et 1 (souvent
            // 0 à 1 pour des embeddings de texte).
            // nullable() : le score n'existe qu'une fois l'analyse
            // COMPLETED, pas au moment de la création (PENDING).
            $table->decimal('similarity_score', 5, 4)->nullable();

            // Justification en langage naturel générée par le LLM
            // (ex: "Ce candidat correspond à 85% du poste car...").
            // nullable() pour la même raison que similarity_score.
            $table->text('justification')->nullable();

            // Timestamp du moment précis où l'analyse est passée à
            // COMPLETED ou FAILED. Différent de updated_at (qui
            // changerait à chaque modification), on veut ici un
            // horodatage métier précis et intentionnel.
            $table->timestamp('analyzed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
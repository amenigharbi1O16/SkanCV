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

            // Clé étrangère vers la table cvs
            $table->foreignId('cv_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Index unique pour assurer la relation 1-to-1
            $table->unique('cv_id');

            $table->string('status')
                  ->default(AnalysisStatus::PENDING->value);

            $table->decimal('similarity_score', 5, 4)->nullable();

            $table->text('justification')->nullable();

            $table->timestamp('analyzed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};

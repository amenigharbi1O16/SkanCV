<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();

            // Relation polymorphique avec le modèle possédant le jeton
            $table->morphs('tokenable');

            $table->string('name');                      // Nom du jeton
            $table->string('token', 64)->unique();       // Valeur unique du jeton (hachée)
            $table->text('abilities')->nullable();        // Permissions associées au jeton
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Date d'expiration du jeton
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
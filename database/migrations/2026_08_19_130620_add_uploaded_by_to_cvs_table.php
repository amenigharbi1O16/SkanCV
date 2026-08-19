<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cvs', function (Blueprint $table) {
            // nullable() pour ne pas casser les CVs déjà existants en base
            $table->foreignId('uploaded_by')
                  ->nullable()
                  ->after('job_posting_id')
                  ->constrained('users')
                  ->nullOnDelete(); // si le user est supprimé, on garde le CV
        });
    }

    public function down(): void
    {
        Schema::table('cvs', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn('uploaded_by');
        });
    }
};
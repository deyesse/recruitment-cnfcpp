<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            // per_contest   = 1 seule candidature par concours (CIN unique dans tout le concours)
            // per_type      = 1 candidature par type de catégorie dans le même concours (cadres, technicien...)
            // per_position  = 1 candidature par poste (CIN peut postuler à plusieurs postes)
            $table->string('uniqueness_mode')->default('per_contest')->after('show_score');
        });
    }

    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('uniqueness_mode');
        });
    }
};

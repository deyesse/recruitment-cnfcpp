<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remplacement du champ age_reference_year (bonus de score) par :
     *  - max_age         : age maximum accepté (critère d'éligibilité, pas un bonus)
     *  - age_reference_date : date de référence à laquelle l'âge est calculé
     *
     * Applicable à TOUS les types de concours.
     * Exemple : max_age = 35, age_reference_date = 2026-01-01
     * → un candidat né avant le 01/01/1991 est rejeté.
     */
    public function up(): void
    {
        Schema::table('contest_types', function (Blueprint $table) {
            // Supprime l'ancien champ "année de référence pour bonus d'âge"
            $table->dropColumn('age_reference_year');

            // Âge maximum accepté à la date de référence (critère d'éligibilité)
            $table->integer('max_age')->nullable()->after('has_age_bonus');

            // Date exacte de référence pour le calcul de l'âge
            // (ex: 2026-01-01 = غرة جانفي 2026)
            $table->date('age_reference_date')->nullable()->after('max_age');
        });
    }

    public function down(): void
    {
        Schema::table('contest_types', function (Blueprint $table) {
            $table->dropColumn(['max_age', 'age_reference_date']);
            $table->integer('age_reference_year')->nullable()->after('has_age_bonus');
        });
    }
};

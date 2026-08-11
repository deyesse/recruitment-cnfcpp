<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajout des paramètres de calcul du score basé sur le niveau scolaire :
     *
     * - base_average_field : le champ de la moyenne de base à utiliser
     *   Valeurs possibles : 'bac_average', 'btp_average', 'grade_9_average', 'grade_6_average'
     *
     * - school_levels : JSON — liste des niveaux scolaires possibles avec leur nombre
     *   d'années supplémentaires. Exemple pour commis (postes 17-18) :
     *   [
     *     {"label": "التاسعة أساسي بنجاح", "extra_years": 0},
     *     {"label": "الأولى ثانوية", "extra_years": 1},
     *     {"label": "الثانية ثانوية", "extra_years": 2},
     *     {"label": "الثالثة ثانوية", "extra_years": 3},
     *     {"label": "الرابعة ثانوية", "extra_years": 4}
     *   ]
     *
     * - age_reference_year : l'année de référence pour le calcul du bonus d'âge
     *   (ex: 40 - âge_au_1er_janvier_de_l'annee_de_reference)
     */
    public function up(): void
    {
        Schema::table('contest_types', function (Blueprint $table) {
            // Champ de base pour la moyenne : 'bac_average', 'btp_average', 'grade_9_average', 'grade_6_average'
            $table->string('base_average_field')->default('bac_average')->after('has_age_bonus');

            // Niveaux scolaires paramétrables avec leurs extra_years respectifs
            $table->json('school_levels')->nullable()->after('base_average_field');

            // Année de référence pour le calcul (40 - âge_au_01/01/année)
            $table->integer('age_reference_year')->nullable()->after('school_levels');
        });
    }

    public function down(): void
    {
        Schema::table('contest_types', function (Blueprint $table) {
            $table->dropColumn(['base_average_field', 'school_levels', 'age_reference_year']);
        });
    }
};

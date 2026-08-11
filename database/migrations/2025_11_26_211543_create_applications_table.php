<?php

use App\Models\Contest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Contest::class);
            $table->string('position'); // Code (01 à 21)
            $table->string('position_name')->nullable();
            $table->string('name');
            $table->string('gender');
            $table->date('birth_date');
            $table->string('address');
            $table->string('governorate');
            $table->string('city');
            $table->string('postal_code');
            $table->string('cin');
            $table->date('cin_date');
            $table->string('tel');
            $table->string('email');
            $table->string('status')->default('جديد');

            // Cadres (01-15) & Technicien (16)
            $table->string('degree')->nullable();
            $table->string('specialty')->nullable();
            $table->integer('graduation_year')->nullable();
            $table->string('institution')->nullable(); // Higher education or vocational center
            $table->string('equivalence_decision')->nullable();
            $table->date('equivalence_date')->nullable();
            $table->float('bac_average')->nullable();
            $table->float('btp_average')->nullable();
            $table->float('grad_average')->nullable();

            // Commis (17-18), Chauffeur (19), Nettoyage (20-21)
            $table->string('school_level')->nullable();
            $table->integer('end_school_year')->nullable();
            $table->string('school_institution')->nullable();
            $table->float('grade_9_average')->nullable();
            $table->float('grade_6_average')->nullable();

            // Chauffeur (19)
            $table->string('driving_license_category')->nullable();
            $table->date('driving_license_date')->nullable();

            // Scores and status
            $table->float('calculated_score')->nullable();
            $table->boolean('is_admissible')->default(false);
            $table->integer('test_grade')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};

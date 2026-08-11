<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // cadre, technicien, commis, chauffeur, nettoyage
            $table->string('name');
            $table->string('min_school_level')->nullable(); // المستوى التعليمي الأدنى المطلوب
            $table->float('min_score')->default(12.0);
            $table->float('coeff_bac_btp')->default(0.60);
            $table->float('coeff_grad_degree')->default(0.40);
            $table->float('bonus_per_extra_year')->default(1.0);
            $table->integer('max_extra_years')->default(4);
            $table->boolean('has_bac')->default(true);
            $table->boolean('has_degree')->default(true);
            $table->boolean('has_school_level')->default(false);
            $table->boolean('has_driving_license')->default(false);
            $table->boolean('has_age_bonus')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_types');
    }
};

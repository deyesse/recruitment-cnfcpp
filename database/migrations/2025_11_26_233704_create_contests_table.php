<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignIdFor(\App\Models\ContestType::class)->nullable();
            $table->string('type')->default('cadre'); // cadre, technicien, commis, chauffeur, nettoyage
            $table->float('min_score')->default(12.0);
            $table->float('bac_factor')->nullable()->default(0.6);
            $table->float('grad_factor')->nullable()->default(0.4);
            $table->dateTime('ends_at');
            $table->json('degrees')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};

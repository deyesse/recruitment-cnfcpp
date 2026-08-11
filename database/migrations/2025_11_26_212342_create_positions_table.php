<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->foreignIdFor(\App\Models\ContestType::class)->nullable();
            $table->string('type')->default('cadre'); // cadre, technicien, commis, chauffeur, nettoyage
            $table->string('degree')->nullable();
            $table->string('specialty')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table pivot بين المناظرة (Contest) والوظائف المفتوحة فيها (Positions).
     * تسمح بربط مناظرة واحدة بعدة وظائف من أصناف/بروفيلات مختلفة.
     */
    public function up(): void
    {
        Schema::create('contest_position', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Contest::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Position::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_position');
    }
};

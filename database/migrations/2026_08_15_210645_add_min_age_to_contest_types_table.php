<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contest_types', function (Blueprint $table) {
            $table->integer('min_age')->nullable()->after('has_age_bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contest_types', function (Blueprint $table) {
            $table->dropColumn('min_age');
        });
    }
};

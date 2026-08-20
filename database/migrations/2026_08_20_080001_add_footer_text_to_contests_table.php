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
        Schema::table('contests', function (Blueprint $table) {
            $table->text('footer_text')->nullable()->after('header_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('footer_text');
        });
    }
};

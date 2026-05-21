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
        Schema::table('scores', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_generation')->nullable()->after('difficulty');
            $table->unsignedTinyInteger('max_streak')->default(0)->after('max_generation');
            $table->dropIndex(['score', 'difficulty']);
            $table->index(['difficulty', 'max_generation', 'score', 'time_seconds']);
        });
    }

    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropIndex(['difficulty', 'max_generation', 'score', 'time_seconds']);
            $table->dropColumn(['max_generation', 'max_streak']);
            $table->index(['score', 'difficulty']);
        });
    }
};

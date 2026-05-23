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
        Schema::create('league_scores', function (Blueprint $table) {
            $table->id();
            $table->string('player_name', 50);
            $table->unsignedInteger('score')->default(0);
            $table->unsignedTinyInteger('correct_answers')->default(0);
            $table->unsignedTinyInteger('total_questions')->default(50);
            $table->unsignedTinyInteger('lives_lost')->default(0);
            $table->unsignedSmallInteger('time_seconds')->default(0);
            $table->unsignedTinyInteger('max_streak')->default(0);
            $table->unsignedTinyInteger('max_stage')->default(1);
            $table->timestamps();

            $table->index(['score', 'time_seconds']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_scores');
    }
};

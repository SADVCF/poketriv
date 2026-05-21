<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->string('player_name');
            $table->unsignedInteger('score')->default(0);
            $table->unsignedTinyInteger('correct_answers')->default(0);
            $table->unsignedTinyInteger('total_questions')->default(10);
            $table->unsignedSmallInteger('time_seconds')->default(0);
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->timestamps();

            $table->index(['score', 'difficulty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};

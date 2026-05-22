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
        Schema::table('pokemon', function (Blueprint $table) {
            $table->unsignedSmallInteger('hp')->nullable()->after('artwork_url');
            $table->unsignedSmallInteger('attack')->nullable()->after('hp');
            $table->unsignedSmallInteger('defense')->nullable()->after('attack');
            $table->unsignedSmallInteger('sp_atk')->nullable()->after('defense');
            $table->unsignedSmallInteger('sp_def')->nullable()->after('sp_atk');
            $table->unsignedSmallInteger('speed')->nullable()->after('sp_def');
            $table->unsignedSmallInteger('weight')->nullable()->after('speed');
            $table->unsignedSmallInteger('height')->nullable()->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->dropColumn(['hp', 'attack', 'defense', 'sp_atk', 'sp_def', 'speed', 'weight', 'height']);
        });
    }
};

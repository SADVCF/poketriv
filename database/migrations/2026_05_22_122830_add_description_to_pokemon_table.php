<?php

use App\Models\Pokemon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->text('description')->nullable()->after('artwork_url');
        });

        $path = database_path('data/pokemon_descriptions.json');
        if (file_exists($path)) {
            $items = json_decode(file_get_contents($path), true);
            foreach ($items as $item) {
                Pokemon::where('pokedex_id', $item['pokedex_id'])->update([
                    'description' => $item['description'],
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};

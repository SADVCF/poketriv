<?php

use App\Models\Pokemon;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $path = database_path('data/pokemon_descriptions.json');
        if (!file_exists($path)) return;

        $items = json_decode(file_get_contents($path), true);
        foreach ($items as $item) {
            if ($item['description']) {
                Pokemon::where('pokedex_id', $item['pokedex_id'])->update([
                    'description' => $item['description'],
                ]);
            }
        }
    }

    public function down(): void
    {
        Pokemon::query()->update(['description' => null]);
    }
};

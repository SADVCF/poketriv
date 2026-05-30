<?php

namespace Database\Seeders;

use App\Models\Pokemon;
use Illuminate\Database\Seeder;

class PokemonDescriptionEnSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/pokemon_descriptions_en.json');

        if (!file_exists($path)) {
            $this->command->warn('pokemon_descriptions_en.json not found.');
            return;
        }

        $items = json_decode(file_get_contents($path), true);

        foreach ($items as $item) {
            Pokemon::where('pokedex_id', $item['pokedex_id'])->update([
                'description_en' => $item['description_en'],
            ]);
        }

        $this->command->info(count($items) . ' English descriptions seeded.');
    }
}

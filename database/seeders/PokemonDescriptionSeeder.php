<?php

namespace Database\Seeders;

use App\Models\Pokemon;
use Illuminate\Database\Seeder;

class PokemonDescriptionSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/pokemon_descriptions.json');

        if (!file_exists($path)) {
            $this->command->warn('pokemon_descriptions.json not found. Run php artisan pokemon:fetch-descriptions first.');
            return;
        }

        $this->command->info('Updating Pokémon descriptions...');
        $items = json_decode(file_get_contents($path), true);

        $bar = $this->command->getOutput()->createProgressBar(count($items));
        $bar->start();

        foreach ($items as $item) {
            Pokemon::where('pokedex_id', $item['pokedex_id'])->update([
                'description' => $item['description'],
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\nDone! " . count($items) . ' descriptions updated.');
    }
}

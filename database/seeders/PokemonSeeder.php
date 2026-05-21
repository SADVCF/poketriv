<?php

namespace Database\Seeders;

use App\Models\Pokemon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class PokemonSeeder extends Seeder
{
    public function run(): void
    {
        $localFile = database_path('data/pokemon.json');

        if (file_exists($localFile)) {
            $this->seedFromJson($localFile);
        } else {
            $this->seedFromApi();
        }
    }

    private function seedFromJson(string $path): void
    {
        $this->command->info('Seeding Pokémon from local JSON...');
        $items = json_decode(file_get_contents($path), true);

        $bar = $this->command->getOutput()->createProgressBar(count($items));
        $bar->start();

        foreach ($items as $item) {
            Pokemon::updateOrCreate(
                ['pokedex_id' => $item['pokedex_id']],
                [
                    'name'         => $item['name'],
                    'display_name' => $item['display_name'],
                    'types'        => $item['types'],
                    'artwork_url'  => $item['artwork_url'],
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\nDone! " . count($items) . ' Pokémon seeded.');
    }

    private function seedFromApi(): void
    {
        $total = 251;
        $this->command->info("Seeding {$total} Pokémon from PokeAPI...");
        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        for ($id = 1; $id <= $total; $id++) {
            try {
                $data = Http::timeout(10)->get("https://pokeapi.co/api/v2/pokemon/{$id}")->json();
                Pokemon::updateOrCreate(
                    ['pokedex_id' => $id],
                    [
                        'name'         => $data['name'],
                        'display_name' => $this->formatName($data['name']),
                        'types'        => array_map(fn($t) => $t['type']['name'], $data['types']),
                        'artwork_url'  => "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$id}.png",
                    ]
                );
            } catch (\Exception $e) {
                $this->command->error("\nFailed on #{$id}: " . $e->getMessage());
            }
            $bar->advance();
            usleep(80000);
        }

        $bar->finish();
        $this->command->info("\nDone!");
    }

    private function formatName(string $name): string
    {
        $special = [
            'nidoran-f' => 'Nidoran♀', 'nidoran-m' => 'Nidoran♂',
            'mr-mime'   => 'Mr. Mime', 'ho-oh'     => 'Ho-Oh',
            'farfetchd' => "Farfetch'd",
        ];
        return $special[$name] ?? ucwords(str_replace('-', ' ', $name));
    }
}

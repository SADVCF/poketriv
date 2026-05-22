<?php

namespace Database\Seeders;

use App\Models\Pokemon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class PokemonStatsSeeder extends Seeder
{
    public function run(): void
    {
        $localFile = database_path('data/pokemon_stats.json');

        if (file_exists($localFile)) {
            $this->seedFromJson($localFile);
        } else {
            $this->seedFromApi();
        }
    }

    private function seedFromJson(string $path): void
    {
        $this->command->info('Updating Pokémon stats from local JSON...');
        $items = json_decode(file_get_contents($path), true);

        $bar = $this->command->getOutput()->createProgressBar(count($items));
        $bar->start();

        foreach ($items as $item) {
            Pokemon::where('pokedex_id', $item['pokedex_id'])->update([
                'hp'      => $item['hp'],
                'attack'  => $item['attack'],
                'defense' => $item['defense'],
                'sp_atk'  => $item['sp_atk'],
                'sp_def'  => $item['sp_def'],
                'speed'   => $item['speed'],
                'weight'  => $item['weight'],
                'height'  => $item['height'],
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\nDone! " . count($items) . ' Pokémon updated with stats.');
    }

    private function seedFromApi(): void
    {
        $total = 1025;
        $this->command->info("Fetching stats for {$total} Pokémon from PokeAPI...");
        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        $statMap = [
            'hp'              => 'hp',
            'attack'          => 'attack',
            'defense'         => 'defense',
            'special-attack'  => 'sp_atk',
            'special-defense' => 'sp_def',
            'speed'           => 'speed',
        ];

        for ($id = 1; $id <= $total; $id++) {
            try {
                $data = Http::timeout(10)->get("https://pokeapi.co/api/v2/pokemon/{$id}")->json();

                $stats = [];
                foreach ($data['stats'] as $statEntry) {
                    $key = $statEntry['stat']['name'];
                    if (isset($statMap[$key])) {
                        $stats[$statMap[$key]] = $statEntry['base_stat'];
                    }
                }

                Pokemon::where('pokedex_id', $id)->update(array_merge($stats, [
                    'weight' => $data['weight'],
                    'height' => $data['height'],
                ]));
            } catch (\Exception $e) {
                $this->command->error("\nFailed on #{$id}: " . $e->getMessage());
            }
            $bar->advance();
            usleep(60000);
        }

        $bar->finish();
        $this->command->info("\nDone! {$total} Pokémon updated with stats.");
    }
}

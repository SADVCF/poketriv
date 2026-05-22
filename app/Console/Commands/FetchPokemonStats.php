<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchPokemonStats extends Command
{
    protected $signature = 'pokemon:fetch-stats';
    protected $description = 'Fetch stats for all Pokémon from PokeAPI and save to local JSON';

    public function handle(): int
    {
        $path = database_path('data/pokemon.json');
        if (!file_exists($path)) {
            $this->error('pokemon.json not found. Run pokemon:fetch first.');
            return self::FAILURE;
        }

        $pokemon = json_decode(file_get_contents($path), true);
        $count = count($pokemon);
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $stats = [];
        foreach ($pokemon as $p) {
            $id = $p['pokedex_id'];
            try {
                $data = Http::timeout(10)->get("https://pokeapi.co/api/v2/pokemon/{$id}")->json();

                $statMap = [];
                foreach ($data['stats'] as $s) {
                    $statMap[$s['stat']['name']] = $s['base_stat'];
                }

                $stats[] = [
                    'pokedex_id' => $id,
                    'hp'         => $statMap['hp'] ?? 0,
                    'attack'     => $statMap['attack'] ?? 0,
                    'defense'    => $statMap['defense'] ?? 0,
                    'sp_atk'     => $statMap['special-attack'] ?? 0,
                    'sp_def'     => $statMap['special-defense'] ?? 0,
                    'speed'      => $statMap['speed'] ?? 0,
                    'weight'     => $data['weight'] ?? 0,
                    'height'     => $data['height'] ?? 0,
                ];
            } catch (\Exception $e) {
                $this->error("\nFailed on #{$id}: {$e->getMessage()}");
            }
            $bar->advance();
            usleep(30000);
        }

        $bar->finish();

        $dir = database_path('data');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents(
            $dir . '/pokemon_stats.json',
            json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->newLine();
        $this->info("Done! " . count($stats) . " stats saved to database/data/pokemon_stats.json");

        return self::SUCCESS;
    }
}

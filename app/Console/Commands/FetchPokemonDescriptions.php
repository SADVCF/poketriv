<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchPokemonDescriptions extends Command
{
    protected $signature = 'pokemon:fetch-descriptions';
    protected $description = 'Fetch flavor text descriptions for all Pokémon from PokeAPI';

    public function handle(): int
    {
        $total = 1025;
        $descriptions = [];
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        for ($id = 1; $id <= $total; $id++) {
            try {
                $data = Http::timeout(10)
                    ->get("https://pokeapi.co/api/v2/pokemon-species/{$id}")
                    ->json();

                $text = null;
                foreach ($data['flavor_text_entries'] ?? [] as $entry) {
                    if ($entry['language']['name'] === 'es') {
                        $text = $entry['flavor_text'];
                        break;
                    }
                }
                if (!$text) {
                    foreach ($data['flavor_text_entries'] ?? [] as $entry) {
                        if ($entry['language']['name'] === 'en') {
                            $text = $entry['flavor_text'];
                            break;
                        }
                    }
                }

                if ($text) {
                    $text = preg_replace('/[\n\f\r]+/', ' ', $text);
                    $text = preg_replace('/[ \t]+/', ' ', $text);
                    $text = trim($text);
                }

                $descriptions[] = [
                    'pokedex_id'  => $id,
                    'description' => $text,
                ];
            } catch (\Exception $e) {
                $this->error("\nFailed on #{$id}: {$e->getMessage()}");
            }
            $bar->advance();
            usleep(80000);
        }

        $bar->finish();

        $dir = database_path('data');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents(
            $dir . '/pokemon_descriptions.json',
            json_encode($descriptions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->newLine();
        $this->info('Done! ' . count($descriptions) . ' descriptions saved.');

        return self::SUCCESS;
    }
}

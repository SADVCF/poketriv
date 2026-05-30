<?php

namespace App\Console\Commands;

use App\Models\Pokemon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchEnglishDescriptions extends Command
{
    protected $signature   = 'poketriv:fetch-descriptions-en {--chunk=50}';
    protected $description = 'Fetch English Pokémon descriptions from PokeAPI and store in description_en';

    public function handle(): int
    {
        $pokemon = Pokemon::whereNull('description_en')->orderBy('pokedex_id')->get();

        if ($pokemon->isEmpty()) {
            $this->info('All Pokémon already have English descriptions.');
            return 0;
        }

        $this->info("Fetching English descriptions for {$pokemon->count()} Pokémon...");
        $bar = $this->output->createProgressBar($pokemon->count());
        $bar->start();

        $ok = 0;
        $fail = 0;

        foreach ($pokemon as $p) {
            try {
                $response = Http::timeout(10)
                    ->get("https://pokeapi.co/api/v2/pokemon-species/{$p->pokedex_id}/");

                if ($response->failed()) {
                    $fail++;
                    $bar->advance();
                    continue;
                }

                $data    = $response->json();
                $entries = $data['flavor_text_entries'] ?? [];

                // Pick the first English entry from a main-series game
                $desc = collect($entries)
                    ->filter(fn($e) => ($e['language']['name'] ?? '') === 'en')
                    ->map(fn($e) => preg_replace('/\s+/', ' ', str_replace(["\n", "\f", "\r"], ' ', $e['flavor_text'])))
                    ->first();

                if ($desc) {
                    $p->update(['description_en' => trim($desc)]);
                    $ok++;
                } else {
                    $fail++;
                }

                // Small sleep to avoid rate limiting
                usleep(120000); // 120ms

            } catch (\Exception $e) {
                $fail++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done! ✅ {$ok} updated, ❌ {$fail} failed.");

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Pokemon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchPokemon extends Command
{
    protected $signature = 'pokemon:fetch';
    protected $description = 'Fetch all 1025 Pokémon from PokeAPI and save to local JSON';

    public function handle(): int
    {
        $total = 1025;
        $pokemon = [];
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $generations = [
            1  => [1, 151],
            2  => [152, 251],
            3  => [252, 386],
            4  => [387, 493],
            5  => [494, 649],
            6  => [650, 721],
            7  => [722, 809],
            8  => [810, 905],
            9  => [906, 1025],
        ];

        $getGen = function ($id) use ($generations) {
            foreach ($generations as $gen => [$from, $to]) {
                if ($id >= $from && $id <= $to) return $gen;
            }
            return 9;
        };

        $specialNames = [
            'nidoran-f'      => 'Nidoran♀',
            'nidoran-m'      => 'Nidoran♂',
            'mr-mime'        => 'Mr. Mime',
            'ho-oh'          => 'Ho-Oh',
            'farfetchd'      => "Farfetch'd",
            'mime-jr'        => 'Mime Jr.',
            'porygon2'       => 'Porygon2',
            'porygon-z'      => 'Porygon-Z',
            'flabebe'        => 'Flabébé',
            'type-null'      => 'Type: Null',
            'jangmo-o'       => 'Jangmo-o',
            'hakamo-o'       => 'Hakamo-o',
            'kommo-o'        => 'Kommo-o',
            'great-tusk'     => 'Great Tusk',
            'scream-tail'    => 'Scream Tail',
            'brute-bonnet'   => 'Brute Bonnet',
            'flutter-mane'   => 'Flutter Mane',
            'slither-wing'   => 'Slither Wing',
            'sandy-shocks'   => 'Sandy Shocks',
            'iron-treads'    => 'Iron Treads',
            'iron-bundle'    => 'Iron Bundle',
            'iron-hands'     => 'Iron Hands',
            'iron-jugulis'   => 'Iron Jugulis',
            'iron-moth'      => 'Iron Moth',
            'iron-thorns'    => 'Iron Thorns',
            'ting-lu'        => 'Ting-Lu',
            'chien-pao'      => 'Chien-Pao',
            'wo-chien'       => 'Wo-Chien',
            'chi-yu'         => 'Chi-Yu',
            'roaring-moon'   => 'Roaring Moon',
            'iron-valiant'   => 'Iron Valiant',
            'gouging-fire'   => 'Gouging Fire',
            'raging-bolt'    => 'Raging Bolt',
            'iron-boulder'   => 'Iron Boulder',
            'iron-crown'     => 'Iron Crown',
            'walking-wake'   => 'Walking Wake',
            'iron-leaves'    => 'Iron Leaves',
        ];

        $formatName = function ($name) use ($specialNames) {
            if (isset($specialNames[$name])) return $specialNames[$name];
            $name = str_replace('-', ' ', $name);
            $lowerExceptions = [' de ', ' du ', ' van ', ' der '];
            foreach ($lowerExceptions as $exc) {
                if (stripos($name, $exc) !== false) {
                    $parts = explode($exc, $name, 2);
                    return ucwords($parts[0]) . $exc . ltrim(ucwords($parts[1]));
                }
            }
            return ucwords($name);
        };

        for ($id = 1; $id <= $total; $id++) {
            try {
                $data = Http::timeout(10)->get("https://pokeapi.co/api/v2/pokemon/{$id}")->json();
                $pokemon[] = [
                    'pokedex_id'   => $id,
                    'name'         => $data['name'],
                    'display_name' => $formatName($data['name']),
                    'types'        => array_map(fn($t) => $t['type']['name'], $data['types']),
                    'generation'   => $getGen($id),
                    'artwork_url'  => "https://cdn.jsdelivr.net/gh/PokeAPI/sprites@master/sprites/pokemon/other/official-artwork/{$id}.png",
                ];
            } catch (\Exception $e) {
                $this->error("\nFailed on #{$id}: {$e->getMessage()}");
            }
            $bar->advance();
            usleep(60000);
        }

        $bar->finish();

        $dir = database_path('data');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . '/pokemon.json', json_encode($pokemon, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info("Done! " . count($pokemon) . " Pokémon saved to database/data/pokemon.json");

        return self::SUCCESS;
    }
}

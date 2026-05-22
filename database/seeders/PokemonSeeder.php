<?php

namespace Database\Seeders;

use App\Models\Pokemon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class PokemonSeeder extends Seeder
{
    const GENERATIONS = [
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
            $pokedexId = $item['pokedex_id'];
            $generation = $item['generation'] ?? $this->getGeneration($pokedexId);
            Pokemon::updateOrCreate(
                ['pokedex_id' => $pokedexId],
                [
                    'generation'   => $generation,
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
        $total = 1025;
        $this->command->info("Seeding {$total} Pokémon from PokeAPI...");
        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        for ($id = 1; $id <= $total; $id++) {
            try {
                $data = Http::timeout(10)->get("https://pokeapi.co/api/v2/pokemon/{$id}")->json();
                $generation = $this->getGeneration($id);
                Pokemon::updateOrCreate(
                    ['pokedex_id' => $id],
                    [
                        'generation'   => $generation,
                        'name'         => $data['name'],
                        'display_name' => $this->formatName($data['name'], $id),
                        'types'        => array_map(fn($t) => $t['type']['name'], $data['types']),
                        'artwork_url'  => "https://cdn.jsdelivr.net/gh/PokeAPI/sprites@master/sprites/pokemon/other/official-artwork/{$id}.png",
                    ]
                );
            } catch (\Exception $e) {
                $this->command->error("\nFailed on #{$id}: " . $e->getMessage());
            }
            $bar->advance();
            usleep(60000);
        }

        $bar->finish();
        $this->command->info("\nDone! {$total} Pokémon seeded.");
    }

    private function getGeneration(int $pokedexId): int
    {
        foreach (self::GENERATIONS as $gen => [$from, $to]) {
            if ($pokedexId >= $from && $pokedexId <= $to) {
                return $gen;
            }
        }
        return 9;
    }

    private function formatName(string $name, int $id): string
    {
        $special = [
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

        if (isset($special[$name])) {
            return $special[$name];
        }

        $name = str_replace('-', ' ', $name);

        $lowerExceptions = [' de ', ' du ', ' van ', ' der '];
        foreach ($lowerExceptions as $exc) {
            if (stripos($name, $exc) !== false) {
                $parts = explode($exc, $name, 2);
                return ucwords($parts[0]) . $exc . ltrim(ucwords($parts[1]));
            }
        }

        return ucwords($name);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pokemon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LeagueQuestionController extends Controller
{
    // 18×18 type effectiveness table
    // Rows = attacking type, Cols = defending type
    // Order: normal, fire, water, electric, grass, ice, fighting, poison,
    //        ground, flying, psychic, bug, rock, ghost, dragon, dark, steel, fairy
    private const TYPES = [
        'normal', 'fire', 'water', 'electric', 'grass', 'ice',
        'fighting', 'poison', 'ground', 'flying', 'psychic', 'bug',
        'rock', 'ghost', 'dragon', 'dark', 'steel', 'fairy',
    ];

    private const TYPE_TRANSLATIONS = [
        'normal'   => 'Normal',
        'fire'     => 'Fuego',
        'water'    => 'Agua',
        'electric' => 'Eléctrico',
        'grass'    => 'Planta',
        'ice'      => 'Hielo',
        'fighting' => 'Lucha',
        'poison'   => 'Veneno',
        'ground'   => 'Tierra',
        'flying'   => 'Volador',
        'psychic'  => 'Psíquico',
        'bug'      => 'Bicho',
        'rock'     => 'Roca',
        'ghost'    => 'Fantasma',
        'dragon'   => 'Dragón',
        'dark'     => 'Siniestro',
        'steel'    => 'Acero',
        'fairy'    => 'Hada',
    ];

    private const TYPE_TRANSLATIONS_EN = [
        'normal'   => 'Normal',
        'fire'     => 'Fire',
        'water'    => 'Water',
        'electric' => 'Electric',
        'grass'    => 'Grass',
        'ice'      => 'Ice',
        'fighting' => 'Fighting',
        'poison'   => 'Poison',
        'ground'   => 'Ground',
        'flying'   => 'Flying',
        'psychic'  => 'Psychic',
        'bug'      => 'Bug',
        'rock'     => 'Rock',
        'ghost'    => 'Ghost',
        'dragon'   => 'Dragon',
        'dark'     => 'Dark',
        'steel'    => 'Steel',
        'fairy'    => 'Fairy',
    ];

    // multiplier × 2 stored as integer (0=0×, 1=½×, 2=1×, 4=2×)
    private const TYPE_CHART = [
        //          nor  fir  wat  ele  gra  ice  fig  poi  gro  fly  psy  bug  roc  gho  dra  dar  ste  fai
        'normal'  => [2,   2,   2,   2,   2,   2,   2,   2,   2,   2,   2,   2,   1,   0,   2,   2,   1,   2],
        'fire'    => [2,   1,   1,   2,   4,   4,   2,   2,   2,   2,   2,   4,   1,   2,   1,   2,   4,   2],
        'water'   => [2,   4,   1,   2,   1,   2,   2,   2,   4,   2,   2,   2,   4,   2,   1,   2,   2,   2],
        'electric'=> [2,   2,   4,   1,   1,   2,   2,   2,   0,   4,   2,   2,   2,   2,   1,   2,   2,   2],
        'grass'   => [2,   1,   4,   1,   1,   2,   2,   1,   4,   1,   2,   1,   4,   2,   1,   2,   1,   2],
        'ice'     => [2,   1,   1,   2,   4,   1,   2,   2,   4,   4,   2,   2,   2,   2,   4,   2,   1,   2],
        'fighting'=> [4,   2,   2,   2,   2,   4,   2,   1,   2,   1,   1,   1,   4,   0,   2,   4,   4,   1],
        'poison'  => [2,   2,   2,   2,   4,   2,   2,   1,   1,   2,   2,   2,   1,   1,   2,   2,   0,   4],
        'ground'  => [2,   4,   2,   4,   1,   2,   2,   4,   2,   0,   2,   1,   4,   2,   2,   2,   4,   2],
        'flying'  => [2,   2,   2,   1,   4,   2,   4,   2,   2,   2,   2,   4,   1,   2,   2,   2,   1,   2],
        'psychic' => [2,   2,   2,   2,   2,   2,   4,   4,   2,   2,   1,   2,   2,   2,   2,   0,   1,   2],
        'bug'     => [2,   1,   2,   2,   4,   2,   1,   1,   2,   1,   4,   2,   2,   1,   2,   4,   1,   1],
        'rock'    => [2,   4,   2,   2,   2,   4,   1,   2,   1,   4,   2,   4,   2,   2,   2,   2,   1,   2],
        'ghost'   => [0,   2,   2,   2,   2,   2,   2,   2,   2,   2,   4,   2,   2,   4,   2,   1,   2,   2],
        'dragon'  => [2,   2,   2,   2,   2,   2,   2,   2,   2,   2,   2,   2,   2,   2,   4,   2,   1,   0],
        'dark'    => [2,   2,   2,   2,   2,   2,   1,   2,   2,   2,   4,   2,   2,   4,   2,   1,   2,   1],
        'steel'   => [2,   1,   1,   1,   2,   4,   2,   2,   2,   2,   2,   2,   4,   2,   2,   2,   1,   4],
        'fairy'   => [2,   1,   2,   2,   2,   2,   4,   1,   2,   2,   2,   2,   2,   2,   4,   4,   1,   2],
    ];

    private const STAGES = [
        1 => ['from' => 0,  'to' => 9,  'maxGen' => 3, 'options' => 4, 'timeLimit' => 15],
        2 => ['from' => 10, 'to' => 19, 'maxGen' => 5, 'options' => 4, 'timeLimit' => 12],
        3 => ['from' => 20, 'to' => 29, 'maxGen' => 7, 'options' => 6, 'timeLimit' => 10],
        4 => ['from' => 30, 'to' => 39, 'maxGen' => 9, 'options' => 6, 'timeLimit' =>  8],
        5 => ['from' => 40, 'to' => 49, 'maxGen' => 9, 'options' => 6, 'timeLimit' =>  7],
    ];

    private const STAT_LABELS = [
        'hp'      => 'HP',
        'attack'  => 'Ataque',
        'defense' => 'Defensa',
        'sp_atk'  => 'At. Esp.',
        'sp_def'  => 'Def. Esp.',
        'speed'   => 'Velocidad',
    ];

    private const STAT_LABELS_EN = [
        'hp'      => 'HP',
        'attack'  => 'Attack',
        'defense' => 'Defense',
        'sp_atk'  => 'Sp. Atk',
        'sp_def'  => 'Sp. Def',
        'speed'   => 'Speed',
    ];

    /** Return locale-aware text: Spanish or English. */
    private function txt(string $es, string $en): string
    {
        return App::getLocale() === 'en' ? $en : $es;
    }

    /** Return locale-aware type label. */
    private function typeLabel(string $type): string
    {
        $map = App::getLocale() === 'en' ? self::TYPE_TRANSLATIONS_EN : self::TYPE_TRANSLATIONS;
        return $map[$type] ?? ucfirst($type);
    }

    /** Return locale-aware stat labels array. */
    private function statLabels(): array
    {
        return App::getLocale() === 'en' ? self::STAT_LABELS_EN : self::STAT_LABELS;
    }

    public function generate(Request $request)
    {
        $questions = [];
        $answers   = [];

        foreach (self::STAGES as $stageNum => $stage) {
            $pool = Pokemon::where('generation', '<=', $stage['maxGen'])->get();

            if ($pool->count() < 20) {
                return response()->json(['error' => "Not enough Pokemon for stage {$stageNum}"], 422);
            }

            $allNamesInStage = $pool->pluck('display_name', 'id');

            $stagePokemons = $pool->shuffle()->take(10);

            // Build balanced type pool for this stage (10 types, no adjacent duplicates)
            $typePool = $this->buildBalancedTypePool($stagePokemons, $stageNum);

            $idx = 0;
            foreach ($stagePokemons as $pokemon) {
                $qType = $typePool[$idx] ?? 'silhouette';
                $q = $this->buildQuestion($pokemon, $qType, $stage, $stageNum, $pool, $allNamesInStage);
                $questions[] = $q;
                $answers[]   = $q['answer'];
                $idx++;
            }
        }

        $sessionToken = bin2hex(random_bytes(16));
        Session::put('league_token', $sessionToken);
        Session::put('league_questions', $answers);

        return response()->json([
            'questions' => $questions,
            'token'     => $sessionToken,
        ]);
    }

    private function buildBalancedTypePool($pokemons, int $stageNum = 1): array
    {
        $stagePools = [
            1 => ['silhouette','silhouette','size','size','weight','weight','type','type','description','who_wins'],
            2 => ['silhouette','silhouette','size','weight','type','description','description','who_wins','flash','cry'],
            3 => ['silhouette','silhouette','type','description','who_wins','who_wins','flash','flash','stat','cry'],
            4 => ['silhouette','silhouette','type','description','who_wins','flash','flash','stat','stat','cry'],
            5 => ['silhouette','description','who_wins','who_wins','flash','flash','stat','stat','cry','cry'],
        ];

        $pool = $stagePools[$stageNum] ?? $stagePools[1];

        // Replace types that require data when not enough Pokémon have it
        $requirements = [
            'stat'        => fn($p) => $p->hp !== null,
            'weight'      => fn($p) => $p->weight !== null,
            'size'        => fn($p) => $p->weight !== null && $p->height !== null,
            'description' => fn($p) => $p->description !== null,
        ];

        $availableCount = [];
        foreach ($requirements as $type => $check) {
            $availableCount[$type] = 0;
            foreach ($pokemons as $p) {
                if ($check($p)) $availableCount[$type]++;
            }
        }

        foreach ($pool as $i => $type) {
            if (isset($requirements[$type]) && $availableCount[$type] < 3) {
                $pool[$i] = 'silhouette';
            }
        }

        // Shuffle until no adjacent duplicates
        do {
            shuffle($pool);
            $ok = true;
            for ($i = 1; $i < count($pool); $i++) {
                if ($pool[$i] === $pool[$i - 1]) { $ok = false; break; }
            }
        } while (!$ok);

        return $pool;
    }

    private function buildQuestion(
        Pokemon $pokemon,
        string $qType,
        array $stage,
        int $stageNum,
        $pool,
        $allNames
    ): array {
        $locale = App::getLocale();
        $description = $locale === 'en'
            ? ($pokemon->description_en ?? $pokemon->description)
            : $pokemon->description;

        $base = [
            'question_type' => $qType,
            'artwork_url'   => $pokemon->artwork_url,
            'cry_url'       => "https://raw.githubusercontent.com/PokeAPI/cries/main/cries/pokemon/latest/{$pokemon->pokedex_id}.ogg",
            'answer'        => $pokemon->display_name,
            'pokemon_name'  => $pokemon->display_name,
            'types'         => $pokemon->types,
            'generation'    => $pokemon->generation,
            'time_limit'    => $stage['timeLimit'],
            'stage'         => $stageNum,
            'reveal_name'   => false,
            'hp'            => $pokemon->hp,
            'attack'        => $pokemon->attack,
            'defense'       => $pokemon->defense,
            'sp_atk'        => $pokemon->sp_atk,
            'sp_def'        => $pokemon->sp_def,
            'speed'         => $pokemon->speed,
            'weight'        => $pokemon->weight,
            'height'        => $pokemon->height,
            'description'   => $description,
        ];

        return match ($qType) {
            'silhouette' => $this->buildSilhouette($base, $pokemon, $stage, $allNames),
            'pixelated'  => $this->buildPixelated($base, $pokemon, $stage, $allNames),
            'type'       => $this->buildType($base, $pokemon),
            'who_wins'   => $this->buildWhoWins($base, $pokemon, $stage, $pool),
            'stat'       => $this->buildStat($base, $pokemon),
            'weight'     => $this->buildWeight($base, $pokemon, $pool),
            'cry'        => $this->buildCry($base, $pokemon, $stage, $allNames),
            'flash'      => $this->buildFlash($base, $pokemon, $stage, $allNames),
            'size'       => $this->buildSize($base, $pokemon, $pool),
            'description'=> $this->buildDescription($base, $pokemon, $stage, $allNames),
            default      => $this->buildSilhouette($base, $pokemon, $stage, $allNames),
        };
    }

    private function buildSilhouette(array $base, Pokemon $pokemon, array $stage, $allNames): array
    {
        return array_merge($base, [
            'question_text' => $this->txt('¿Quién es este Pokémon?', 'Who is this Pokémon?'),
            'options'       => $this->nameOptions($pokemon, $stage['options'], $allNames),
        ]);
    }

    private function buildPixelated(array $base, Pokemon $pokemon, array $stage, $allNames): array
    {
        return array_merge($base, [
            'question_text' => $this->txt('¿Quién es este Pokémon?', 'Who is this Pokémon?'),
            'options'       => $this->nameOptions($pokemon, $stage['options'], $allNames),
        ]);
    }

    private function buildType(array $base, Pokemon $pokemon): array
    {
        $correctType = $pokemon->types[0] ?? 'normal';
        $wrongTypes  = collect(self::TYPES)
            ->reject(fn($t) => in_array($t, $pokemon->types))
            ->shuffle()
            ->take(3)
            ->values()
            ->toArray();

        $options = collect($wrongTypes)
            ->push($correctType)
            ->shuffle()
            ->values()
            ->toArray();

        return array_merge($base, [
            'question_text' => $this->txt('¿De qué tipo es este Pokémon?', 'What type is this Pokémon?'),
            'answer'        => $correctType,
            'options'       => $options,
        ]);
    }

    private function buildWhoWins(array $base, Pokemon $pokemon, array $stage, $pool): array
    {
        $defenderTypes = $pokemon->types;
        $optionCount   = $stage['options'];

        // Find a pokemon that has type advantage over the subject
        $candidates = $pool->filter(function ($candidate) use ($defenderTypes, $pokemon) {
            if ($candidate->id === $pokemon->id) return false;
            return $this->hasTypeAdvantage($candidate->types, $defenderTypes);
        });

        if ($candidates->isEmpty()) {
            // fallback to silhouette if no counter found
            $allNames = $pool->pluck('display_name', 'id');
            return $this->buildSilhouette(
                array_merge($base, ['question_type' => 'silhouette']),
                $pokemon, $stage, $allNames
            );
        }

        $correct = $candidates->shuffle()->first();

        // Fill rest with pokemon that do NOT have advantage
        $losers = $pool->filter(function ($candidate) use ($defenderTypes, $pokemon, $correct) {
            if ($candidate->id === $pokemon->id || $candidate->id === $correct->id) return false;
            return !$this->hasTypeAdvantage($candidate->types, $defenderTypes);
        })->shuffle()->take($optionCount - 1);

        if ($losers->count() < $optionCount - 1) {
            // fallback to silhouette if not enough non-advantage options
            $allNames = $pool->pluck('display_name', 'id');
            return $this->buildSilhouette(
                array_merge($base, ['question_type' => 'silhouette']),
                $pokemon, $stage, $allNames
            );
        }

        $options = $losers->pluck('display_name')
            ->push($correct->display_name)
            ->shuffle()
            ->values()
            ->toArray();

        // Generate type explanation
        $typePairs = [];
        $chart = self::TYPE_CHART;
        $types = self::TYPES;
        $typeIndex = array_flip($types);
        foreach ($correct->types as $atkType) {
            if (!isset($chart[$atkType])) continue;
            foreach ($pokemon->types as $defType) {
                if (!isset($typeIndex[$defType])) continue;
                if ($chart[$atkType][$typeIndex[$defType]] === 4) {
                    $typePairs[] = $this->typeLabel($atkType) . ' > ' . $this->typeLabel($defType);
                }
            }
        }

        return array_merge($base, [
            'question_text'  => $this->txt(
                "¿Quién vence a {$pokemon->display_name}?",
                "Who beats {$pokemon->display_name}?"
            ),
            'answer'         => $correct->display_name,
            'options'        => $options,
            'reveal_name'    => true,
            'defender_types' => $pokemon->types,
            'attacker_types' => $correct->types,
            'type_explanation' => implode(', ', $typePairs),
        ]);
    }

    private function buildStat(array $base, Pokemon $pokemon): array
    {
        $stats = [
            'hp'      => $pokemon->hp,
            'attack'  => $pokemon->attack,
            'defense' => $pokemon->defense,
            'sp_atk'  => $pokemon->sp_atk,
            'sp_def'  => $pokemon->sp_def,
            'speed'   => $pokemon->speed,
        ];

        $labels = $this->statLabels();
        $maxKey = array_keys($stats, max($stats))[0];
        $correctLabel = $labels[$maxKey];

        $options = array_values($labels);
        shuffle($options);

        return array_merge($base, [
            'question_text' => $this->txt(
                '¿Cuál es la estadística base más alta de este Pokémon?',
                'What is the highest base stat of this Pokémon?'
            ),
            'answer'        => $correctLabel,
            'options'       => $options,
        ]);
    }

    private function buildWeight(array $base, Pokemon $pokemonA, $pool): array
    {
        $candidates = $pool->filter(fn($c) => $c->id !== $pokemonA->id && $c->weight !== null);

        if ($candidates->isEmpty()) {
            $allNames = $pool->pluck('display_name', 'id');
            return $this->buildSilhouette(
                array_merge($base, ['question_type' => 'silhouette']),
                $pokemonA, ['options' => 4], $allNames
            );
        }

        $pokemonB = $candidates->shuffle()->first();

        $heavier = $pokemonA->weight >= $pokemonB->weight
            ? $pokemonA->display_name
            : $pokemonB->display_name;

        $options = collect([$pokemonA->display_name, $pokemonB->display_name])
            ->shuffle()->values()->toArray();

        return array_merge($base, [
            'question_text'  => $this->txt('¿Cuál de estos Pokémon pesa más?', 'Which Pokémon weighs more?'),
            'answer'         => $heavier,
            'options'        => $options,
            'artwork_url_b'  => $pokemonB->artwork_url,
            'display_name_b' => $pokemonB->display_name,
            'weight_kg_a'    => round($pokemonA->weight / 10, 1),
            'weight_kg_b'    => round($pokemonB->weight / 10, 1),
        ]);
    }

    private function buildBlurReveal(array $base, Pokemon $pokemon, array $stage, $allNames): array
    {
        return array_merge($base, [
            'question_text' => $this->txt('¿Quién se esconde tras el desenfoque?', 'Who is hiding behind the blur?'),
            'options'       => $this->nameOptions($pokemon, $stage['options'], $allNames),
            'time_limit'    => max(8, $stage['timeLimit'] + 2),
        ]);
    }

    private function buildCry(array $base, Pokemon $pokemon, array $stage, $allNames): array
    {
        return array_merge($base, [
            'question_text' => $this->txt('¿Qué Pokémon hace este grito?', 'Which Pokémon makes this cry?'),
            'options'       => $this->nameOptions($pokemon, $stage['options'], $allNames),
            'time_limit'    => $stage['timeLimit'] + 3,
        ]);
    }

    private function buildFlash(array $base, Pokemon $pokemon, array $stage, $allNames): array
    {
        return array_merge($base, [
            'question_text' => $this->txt('¿Qué Pokémon has visto?', 'Which Pokémon did you see?'),
            'options'       => $this->nameOptions($pokemon, $stage['options'], $allNames),
        ]);
    }

    private function buildSize(array $base, Pokemon $pokemonA, $pool): array
    {
        $candidates = $pool->filter(fn($c) => $c->id !== $pokemonA->id
            && $c->weight !== null && $c->height !== null);

        if ($candidates->isEmpty()) {
            $allNames = $pool->pluck('display_name', 'id');
            return $this->buildSilhouette(
                array_merge($base, ['question_type' => 'silhouette']),
                $pokemonA, ['options' => 4], $allNames
            );
        }

        $pokemonB = $candidates->shuffle()->first();
        $askWeight = mt_rand(0, 1) === 0;

        if ($askWeight) {
            $answer = $pokemonA->weight >= $pokemonB->weight
                ? $pokemonA->display_name : $pokemonB->display_name;
        } else {
            $answer = $pokemonA->height >= $pokemonB->height
                ? $pokemonA->display_name : $pokemonB->display_name;
        }

        $options = collect([$pokemonA->display_name, $pokemonB->display_name])
            ->shuffle()->values()->toArray();

        return array_merge($base, [
            'question_text'  => $askWeight
                ? $this->txt('¿Cuál pesa más?', 'Which one weighs more?')
                : $this->txt('¿Cuál es más alto?', 'Which one is taller?'),
            'answer'         => $answer,
            'options'        => $options,
            'artwork_url_b'  => $pokemonB->artwork_url,
            'display_name_b' => $pokemonB->display_name,
            'size_ask_weight'=> $askWeight,
            'weight_kg_a'    => round($pokemonA->weight / 10, 1),
            'weight_kg_b'    => round($pokemonB->weight / 10, 1),
            'height_m_a'     => round($pokemonA->height / 10, 1),
            'height_m_b'     => round($pokemonB->height / 10, 1),
        ]);
    }

    private function buildDescription(array $base, Pokemon $pokemon, array $stage, $allNames): array
    {
        // $base already contains the locale-aware description; pass it through
        return array_merge($base, [
            'question_text' => App::getLocale() === 'en' ? 'Which Pokémon is this?' : '¿Qué Pokémon es?',
            'options'       => $this->nameOptions($pokemon, $stage['options'], $allNames),
        ]);
    }

    private function nameOptions(Pokemon $pokemon, int $count, $allNames): array
    {
        $wrongIds = $allNames
            ->reject(fn($name, $id) => $id === $pokemon->id)
            ->keys()
            ->shuffle()
            ->take($count - 1);

        return $wrongIds
            ->map(fn($id) => $allNames[$id])
            ->push($pokemon->display_name)
            ->shuffle()
            ->values()
            ->toArray();
    }

    private function hasTypeAdvantage(array $attackerTypes, array $defenderTypes): bool
    {
        $chart = self::TYPE_CHART;
        $types = self::TYPES;
        $typeIndex = array_flip($types);

        foreach ($attackerTypes as $atkType) {
            if (!isset($chart[$atkType])) continue;
            foreach ($defenderTypes as $defType) {
                if (!isset($typeIndex[$defType])) continue;
                $effectiveness = $chart[$atkType][$typeIndex[$defType]];
                if ($effectiveness === 4) {
                    return true;
                }
            }
        }
        return false;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pokemon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class QuestionController extends Controller
{
    public function generate(Request $request)
    {
        $difficulty   = $request->input('difficulty', 'medium');
        $maxGen       = $request->input('max_generation', 9);
        $questionCount = (int) $request->input('question_count', 10);
        $optionCount   = $difficulty === 'hard' ? 6 : 4;

        $maxGen = min(9, max(1, (int) $maxGen));

        $pool = Pokemon::where('generation', '<=', $maxGen);

        $pokemonCount = $pool->count();
        if ($pokemonCount < $questionCount) {
            return response()->json(['error' => 'Not enough Pokemon in this generation pool'], 422);
        }

        $questions = $pool->inRandomOrder()->take($questionCount)->get();

        $allNames = Pokemon::where('generation', '<=', $maxGen)
            ->pluck('display_name', 'id');

        $generatedQuestions = $questions->map(function ($pokemon) use ($allNames, $optionCount) {
            $wrongIds = $allNames
                ->reject(fn($name, $id) => $id === $pokemon->id)
                ->keys()
                ->shuffle()
                ->take($optionCount - 1);

            $options = $wrongIds
                ->map(fn($id) => $allNames[$id])
                ->push($pokemon->display_name)
                ->shuffle()
                ->values();

            return [
                'id'          => $pokemon->id,
                'pokedex_id'  => $pokemon->pokedex_id,
                'artwork_url' => $pokemon->artwork_url,
                'types'       => $pokemon->types,
                'generation'  => $pokemon->generation,
                'answer'      => $pokemon->display_name,
                'options'     => $options,
            ];
        })->values()->toArray();

        $sessionToken = bin2hex(random_bytes(16));

        Session::put('game_token', $sessionToken);
        Session::put('game_answers', array_column($generatedQuestions, 'answer'));
        Session::put('game_count', count($generatedQuestions));

        return response()->json([
            'questions'     => $generatedQuestions,
            'token'         => $sessionToken,
            'count'         => count($generatedQuestions),
        ]);
    }
}

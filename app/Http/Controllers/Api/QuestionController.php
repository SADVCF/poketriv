<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pokemon;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function generate(Request $request)
    {
        $difficulty  = $request->input('difficulty', 'medium');
        $count       = 10;
        $optionCount = $difficulty === 'hard' ? 6 : 4;

        $pool = match ($difficulty) {
            'easy'  => Pokemon::where('pokedex_id', '<=', 151),
            default => Pokemon::query(),
        };

        $questions = $pool->inRandomOrder()->take($count)->get();

        $allNames = Pokemon::pluck('display_name', 'id');

        return response()->json(
            $questions->map(function ($pokemon) use ($allNames, $optionCount) {
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
                    'artwork_url' => $pokemon->artwork_url,
                    'types'       => $pokemon->types,
                    'answer'      => $pokemon->display_name,
                    'options'     => $options,
                ];
            })
        );
    }
}

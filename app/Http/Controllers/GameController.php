<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $playerName   = '';
        $difficulty   = $request->query('difficulty', 'medium');
        $maxGen       = (int) $request->query('max_generation', 9);
        $questionCount = (int) $request->query('question_count', 10);

        if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $difficulty = 'medium';
        }

        $maxGen = min(9, max(1, $maxGen));
        $questionCount = min(50, max(5, $questionCount));

        $timePerQuestion = match ($difficulty) {
            'easy'  => 12,
            'hard'  => 6,
            default => 8,
        };

        $optionCount = $difficulty === 'hard' ? 6 : 4;

        return view('game', compact('playerName', 'difficulty', 'timePerQuestion', 'optionCount', 'maxGen', 'questionCount'));
    }
}

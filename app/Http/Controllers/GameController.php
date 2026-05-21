<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $playerName = $request->query('player', 'Entrenador');
        $difficulty  = $request->query('difficulty', 'medium');

        if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $difficulty = 'medium';
        }

        $timePerQuestion = match ($difficulty) {
            'easy'  => 12,
            'hard'  => 6,
            default => 8,
        };

        $optionCount = $difficulty === 'hard' ? 6 : 4;

        return view('game', compact('playerName', 'difficulty', 'timePerQuestion', 'optionCount'));
    }
}

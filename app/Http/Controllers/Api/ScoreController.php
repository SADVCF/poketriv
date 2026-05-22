<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ScoreController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'player_name'      => 'required|string|max:50|regex:/^[\p{L}0-9\s\-_\.\']+$/u',
            'score'            => 'required|integer|min:0|max:99999',
            'correct_answers'  => 'required|integer|min:0|max:50',
            'total_questions'  => 'required|integer|min:1|max:50',
            'time_seconds'     => 'required|integer|min:0|max:9999',
            'difficulty'       => 'required|in:easy,medium,hard',
            'max_generation'   => 'nullable|integer|min:1|max:9',
            'max_streak'       => 'nullable|integer|min:0|max:50',
            'token'            => 'required|string',
        ]);

        $sessionToken = Session::get('game_token');
        $expectedAnswers = Session::get('game_answers', []);
        $expectedCount = Session::get('game_count', 0);

        if (!$sessionToken || $sessionToken !== $validated['token']) {
            return response()->json(['error' => 'Invalid game session'], 403);
        }

        if ($validated['correct_answers'] > $expectedCount) {
            return response()->json(['error' => 'Invalid score data'], 403);
        }

        $maxGen = $validated['max_generation'] ?? 9;
        $genMultiplier = 1 + ($maxGen - 1) * 0.15;
        $maxPossibleScore = (int) ceil($expectedCount * (100 + 120 + 475) * $genMultiplier);

        if ($validated['score'] > $maxPossibleScore) {
            return response()->json(['error' => 'Score exceeds maximum possible'], 403);
        }

        Session::forget(['game_token', 'game_answers', 'game_count']);

        $score = Score::create([
            'player_name'     => $validated['player_name'],
            'score'           => $validated['score'],
            'correct_answers' => $validated['correct_answers'],
            'total_questions' => $validated['total_questions'],
            'time_seconds'    => $validated['time_seconds'],
            'difficulty'      => $validated['difficulty'],
            'max_generation'  => $maxGen,
            'max_streak'      => $validated['max_streak'] ?? 0,
        ]);

        $rank = Score::where('difficulty', $score->difficulty)
            ->where('max_generation', $maxGen)
            ->where(function ($q) use ($score) {
                $q->where('score', '>', $score->score)
                  ->orWhere(function ($q2) use ($score) {
                      $q2->where('score', $score->score)
                         ->where('time_seconds', '<', $score->time_seconds);
                  });
            })
            ->count() + 1;

        return response()->json(['rank' => $rank, 'id' => $score->id]);
    }
}

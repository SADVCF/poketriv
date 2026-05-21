<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Score;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'player_name'      => 'required|string|max:50',
            'score'            => 'required|integer|min:0',
            'correct_answers'  => 'required|integer|min:0|max:10',
            'total_questions'  => 'required|integer|min:1|max:10',
            'time_seconds'     => 'required|integer|min:0',
            'difficulty'       => 'required|in:easy,medium,hard',
        ]);

        $score = Score::create($validated);

        $rank = Score::where('difficulty', $score->difficulty)
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

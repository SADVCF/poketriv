<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeagueScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LeagueScoreController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'player_name'     => 'required|string|max:50|regex:/^[a-zA-Z0-9\s\-\_\.\']+$/u',
            'score'           => 'required|integer|min:0|max:9999999',
            'correct_answers' => 'required|integer|min:0|max:40',
            'total_questions' => 'required|integer|min:1|max:40',
            'lives_lost'      => 'required|integer|min:0|max:3',
            'time_seconds'    => 'required|integer|min:0|max:9999',
            'max_streak'      => 'nullable|integer|min:0|max:40',
            'max_stage'       => 'required|integer|min:1|max:4',
            'token'           => 'required|string',
        ]);

        $sessionToken = Session::get('league_token');
        $storedAnswers = Session::get('league_questions', []);

        if (!$sessionToken || $sessionToken !== $validated['token']) {
            return response()->json(['error' => 'Invalid league session'], 403);
        }

        if ($validated['correct_answers'] > count($storedAnswers)) {
            return response()->json(['error' => 'Invalid score data'], 403);
        }

        // Max possible score estimate (generous upper bound for anti-cheat)
        // Per question: base 200 + max time bonus (15*15=225) + max streak bonus (40*50=2000)
        // Gen multiplier for stage 4 (maxGen=9): 1 + (9-1)*0.15 = 2.2
        $maxScore = (int) ceil(40 * (200 + 225 + 2000) * 2.2) + (3 * 500);
        if ($validated['score'] > $maxScore) {
            return response()->json(['error' => 'Score exceeds maximum possible'], 403);
        }

        Session::forget(['league_token', 'league_questions']);

        $leagueScore = LeagueScore::create([
            'player_name'     => $validated['player_name'],
            'score'           => $validated['score'],
            'correct_answers' => $validated['correct_answers'],
            'total_questions' => $validated['total_questions'],
            'lives_lost'      => $validated['lives_lost'],
            'time_seconds'    => $validated['time_seconds'],
            'max_streak'      => $validated['max_streak'] ?? 0,
            'max_stage'       => $validated['max_stage'],
        ]);

        $rank = LeagueScore::where(function ($q) use ($leagueScore) {
                $q->where('score', '>', $leagueScore->score)
                  ->orWhere(function ($q2) use ($leagueScore) {
                      $q2->where('score', $leagueScore->score)
                         ->where('time_seconds', '<', $leagueScore->time_seconds);
                  });
            })
            ->count() + 1;

        return response()->json(['rank' => $rank, 'id' => $leagueScore->id]);
    }
}

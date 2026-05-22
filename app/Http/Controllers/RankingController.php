<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\LeagueScore;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $difficulty = $request->query('difficulty', 'all');
        $maxGenRaw  = $request->query('max_generation', 'all');
        $maxGen     = $maxGenRaw === 'all' ? 'all' : min(9, max(1, (int) $maxGenRaw));

        $scores = collect();

        if ($difficulty === 'league') {
            $leagueScores = LeagueScore::orderByDesc('score')->orderBy('time_seconds')->take(50)->get();
            $scores = $leagueScores->map(fn($s) => (object) [
                '_type'          => 'league',
                'difficulty'     => 'league',
                'player_name'    => $s->player_name,
                'score'          => $s->score,
                'correct_answers'=> $s->correct_answers,
                'total_questions'=> 40,
                'time_seconds'   => $s->time_seconds,
                'max_streak'     => $s->max_streak,
                'max_generation' => 9,
                'max_stage'      => $s->max_stage,
                'lives_lost'     => $s->lives_lost ?? 0,
            ]);
        } else {
            $query = Score::orderByDesc('score')->orderBy('time_seconds');

            if (in_array($difficulty, ['easy', 'medium', 'hard'])) {
                $query->where('difficulty', $difficulty);
            }

            if ($maxGen !== 'all') {
                $query->where('max_generation', '<=', $maxGen);
            }

            $normalScores = $query->take(50)->get()->map(fn($s) => (object) [
                '_type'          => 'normal',
                'difficulty'     => $s->difficulty,
                'player_name'    => $s->player_name,
                'score'          => $s->score,
                'correct_answers'=> $s->correct_answers,
                'total_questions'=> $s->total_questions,
                'time_seconds'   => $s->time_seconds,
                'max_streak'     => $s->max_streak,
                'max_generation' => $s->max_generation,
            ]);

            if ($difficulty === 'all') {
                $leagueScores = LeagueScore::orderByDesc('score')->orderBy('time_seconds')->take(50)->get()
                    ->map(fn($s) => (object) [
                        '_type'          => 'league',
                        'difficulty'     => 'league',
                        'player_name'    => $s->player_name,
                        'score'          => $s->score,
                        'correct_answers'=> $s->correct_answers,
                        'total_questions'=> 40,
                        'time_seconds'   => $s->time_seconds,
                        'max_streak'     => $s->max_streak,
                        'max_generation' => 9,
                        'max_stage'      => $s->max_stage,
                        'lives_lost'     => $s->lives_lost ?? 0,
                    ]);

                $scores = $normalScores->concat($leagueScores)
                    ->sort(function ($a, $b) {
                        return $b->score <=> $a->score ?: $a->time_seconds <=> $b->time_seconds;
                    })
                    ->values()
                    ->take(50);
            } else {
                $scores = $normalScores;
            }
        }

        return view('ranking', [
            'scores'     => $scores,
            'difficulty' => $difficulty,
            'maxGen'     => $maxGen,
        ]);
    }
}

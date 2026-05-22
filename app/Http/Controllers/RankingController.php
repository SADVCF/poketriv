<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\LeagueScore;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $difficulty = $request->query('difficulty', 'all');
        $maxGenRaw  = $request->query('max_generation', 'all');
        $maxGen     = $maxGenRaw === 'all' ? 'all' : min(9, max(1, (int) $maxGenRaw));
        $page       = max(1, (int) $request->query('page', 1));
        $perPage    = 10;

        if ($difficulty === 'league') {
            $paginated = LeagueScore::orderByDesc('score')->orderBy('time_seconds')->paginate($perPage, page: $page);
            $scores = collect($paginated->items())->map(fn($s) => (object) [
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
            $paginador = new LengthAwarePaginator(
                $scores, $paginated->total(), $perPage, $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } elseif ($difficulty === 'all') {
            $normalQuery = Score::orderByDesc('score')->orderBy('time_seconds');
            $normalScores = $normalQuery->take(5000)->get()->map(fn($s) => (object) [
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

            $leagueScores = LeagueScore::orderByDesc('score')->orderBy('time_seconds')->take(5000)->get()
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

            $allScores = $normalScores->concat($leagueScores)
                ->sort(function ($a, $b) {
                    return $b->score <=> $a->score ?: $a->time_seconds <=> $b->time_seconds;
                })
                ->values();

            $total = $allScores->count();
            $slice = $allScores->slice(($page - 1) * $perPage, $perPage)->values();

            $paginador = new LengthAwarePaginator(
                $slice, $total, $perPage, $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $query = Score::orderByDesc('score')->orderBy('time_seconds')
                ->where('difficulty', $difficulty);

            if ($maxGen !== 'all') {
                $query->where('max_generation', '<=', $maxGen);
            }

            $paginated = $query->paginate($perPage, page: $page);
            $scores = collect($paginated->items())->map(fn($s) => (object) [
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

            $paginador = new LengthAwarePaginator(
                $scores, $paginated->total(), $perPage, $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // Global top 3 for podium (independent of pagination)
        $globalTop = collect();
        if ($difficulty === 'league') {
            $globalTop = LeagueScore::orderByDesc('score')->orderBy('time_seconds')->take(3)->get()
                ->map(fn($s) => (object) [
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
            $q = Score::orderByDesc('score')->orderBy('time_seconds');
            $globalTop = $q->take(3)->get()->map(fn($s) => (object) [
                'player_name'    => $s->player_name,
                'score'          => $s->score,
                'correct_answers'=> $s->correct_answers,
                'total_questions'=> $s->total_questions,
                'time_seconds'   => $s->time_seconds,
                'max_streak'     => $s->max_streak,
                'max_generation' => $s->max_generation,
            ]);
            if ($difficulty === 'all') {
                $leagueTop3 = LeagueScore::orderByDesc('score')->orderBy('time_seconds')->take(3)->get()
                    ->map(fn($s) => (object) [
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
                $globalTop = $globalTop->concat($leagueTop3)
                    ->sort(fn($a,$b) => $b->score <=> $a->score ?: $a->time_seconds <=> $b->time_seconds)
                    ->values()->take(3);
            }
        }

        return view('ranking', [
            'scores'     => $paginador,
            'difficulty' => $difficulty,
            'maxGen'     => $maxGen,
            'top3'       => $globalTop,
        ]);
    }
}

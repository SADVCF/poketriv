<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\LeagueScore;

class HomeController extends Controller
{
    public function index()
    {
        $normalTop = Score::orderByDesc('score')->orderBy('time_seconds')->take(10)->get();
        $leagueTop = LeagueScore::orderByDesc('score')->orderBy('time_seconds')->take(10)->get();

        $top10 = collect()
            ->concat($normalTop->map(fn($s) => [
                'name'  => $s->player_name,
                'score' => $s->score,
                'mode'  => $s->difficulty,
            ]))
            ->concat($leagueTop->map(fn($s) => [
                'name'  => $s->player_name,
                'score' => $s->score,
                'mode'  => 'LIGA',
            ]))
            ->sort(fn($a, $b) => $b['score'] <=> $a['score'])
            ->values()
            ->take(10);

        return view('home', compact('top10'));
    }
}

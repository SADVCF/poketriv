<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $difficulty = $request->query('difficulty', 'all');

        $query = Score::orderByDesc('score')->orderBy('time_seconds');

        if (in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $query->where('difficulty', $difficulty);
        }

        $scores = $query->take(50)->get();

        return view('ranking', compact('scores', 'difficulty'));
    }
}

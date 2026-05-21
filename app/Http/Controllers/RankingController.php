<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $difficulty = $request->query('difficulty', 'all');
        $maxGenRaw  = $request->query('max_generation', 'all');

        // 'all' = no filtrar por generación
        $maxGen = $maxGenRaw === 'all' ? 'all' : min(9, max(1, (int) $maxGenRaw));

        $query = Score::orderByDesc('score')->orderBy('time_seconds');

        if (in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $query->where('difficulty', $difficulty);
        }

        if ($maxGen !== 'all') {
            $query->where('max_generation', $maxGen);
        }

        $scores = $query->take(50)->get();

        return view('ranking', compact('scores', 'difficulty', 'maxGen'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LeagueController extends Controller
{
    public function index(Request $request)
    {
        $playerName = $request->query('player', 'Entrenador');

        return view('league', compact('playerName'));
    }
}

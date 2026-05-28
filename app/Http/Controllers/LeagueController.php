<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LeagueController extends Controller
{
    public function index()
    {
        return view('league', ['playerName' => '']);
    }
}

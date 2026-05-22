<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/game', [GameController::class, 'index'])->name('game');
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking');
Route::get('/league', [LeagueController::class, 'index'])->name('league');

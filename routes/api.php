<?php

use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ScoreController;
use Illuminate\Support\Facades\Route;

Route::post('/game/questions', [QuestionController::class, 'generate']);
Route::post('/game/score', [ScoreController::class, 'store']);

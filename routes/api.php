<?php

use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\LeagueQuestionController;
use App\Http\Controllers\Api\LeagueScoreController;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::middleware([
    EncryptCookies::class,
    StartSession::class,
    AddQueuedCookiesToResponse::class,
    VerifyCsrfToken::class,
    'throttle:10,1',
])->post('/game/questions', [QuestionController::class, 'generate']);

Route::middleware([
    EncryptCookies::class,
    StartSession::class,
    AddQueuedCookiesToResponse::class,
    VerifyCsrfToken::class,
    'throttle:5,1',
])->post('/game/score', [ScoreController::class, 'store']);

Route::middleware([
    EncryptCookies::class,
    StartSession::class,
    AddQueuedCookiesToResponse::class,
    'throttle:5,1',
])->post('/league/questions', [LeagueQuestionController::class, 'generate']);

Route::middleware([
    EncryptCookies::class,
    StartSession::class,
    AddQueuedCookiesToResponse::class,
    'throttle:3,1',
])->post('/league/score', [LeagueScoreController::class, 'store']);

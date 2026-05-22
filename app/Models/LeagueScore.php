<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeagueScore extends Model
{
    protected $table = 'league_scores';

    protected $fillable = [
        'player_name', 'score', 'correct_answers', 'total_questions',
        'lives_lost', 'time_seconds', 'max_streak', 'max_stage',
    ];
}

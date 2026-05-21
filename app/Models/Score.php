<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'player_name', 'score', 'correct_answers',
        'total_questions', 'time_seconds', 'difficulty',
    ];
}

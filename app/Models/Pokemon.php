<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    protected $table = 'pokemon';

    protected $fillable = [
        'pokedex_id', 'generation', 'name', 'display_name', 'types', 'artwork_url',
        'hp', 'attack', 'defense', 'sp_atk', 'sp_def', 'speed', 'weight', 'height',
        'description', 'description_en',
    ];

    protected $casts = [
        'types' => 'array',
    ];
}

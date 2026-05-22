<?php

use App\Models\Pokemon;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Pokemon::chunk(100, function ($pokemon) {
            foreach ($pokemon as $p) {
                $id = $p->pokedex_id;
                $newUrl = "https://cdn.jsdelivr.net/gh/PokeAPI/sprites@master/sprites/pokemon/other/official-artwork/{$id}.png";
                if ($p->artwork_url !== $newUrl) {
                    $p->update(['artwork_url' => $newUrl]);
                }
            }
        });
    }

    public function down(): void
    {
        Pokemon::chunk(100, function ($pokemon) {
            foreach ($pokemon as $p) {
                $id = $p->pokedex_id;
                $newUrl = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$id}.png";
                if ($p->artwork_url !== $newUrl) {
                    $p->update(['artwork_url' => $newUrl]);
                }
            }
        });
    }
};

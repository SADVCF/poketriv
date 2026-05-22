<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PokemonSeeder::class);
        $this->call(PokemonStatsSeeder::class);
        $this->call(PokemonDescriptionSeeder::class);
    }
}

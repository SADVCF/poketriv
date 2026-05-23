<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeployPokemon extends Command
{
    protected $signature = 'deploy:pokemon';
    protected $description = 'Run all Pokémon data tasks: migrate, fetch descriptions, seed';

    public function handle(): int
    {
        $this->call('migrate');

    $this->call('db:seed', ['--class' => 'PokemonStatsSeeder']);
    $this->call('db:seed', ['--class' => 'PokemonDescriptionSeeder']);

    $this->info('All Pokémon data tasks complete.');
        return self::SUCCESS;
    }
}

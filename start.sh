#!/bin/sh
# Este script se ejecuta CADA VEZ que el contenedor arranca en Render

echo "==> Cacheando config, rutas y vistas..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Ejecutando migraciones..."
php artisan migrate --force

echo "==> Seeding Pokémon (desde JSON local, instantáneo)..."
php artisan db:seed --class=PokemonSeeder --force

echo "==> Servidor listo en puerto ${PORT:-10000}"
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}

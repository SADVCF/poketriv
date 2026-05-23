#!/bin/sh
set -e  # Si cualquier comando falla, el script se detiene (visible en logs de Render)

echo "==> APP_ENV: ${APP_ENV}"
echo "==> DB_CONNECTION: ${DB_CONNECTION}"
echo "==> DB_HOST: ${DB_HOST}"

echo "==> Cacheando config, rutas y vistas..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Ejecutando migraciones..."
php artisan migrate --force

echo "==> Seeding Pokémon (desde JSON local)..."
php artisan db:seed --class=PokemonSeeder --force
php artisan db:seed --class=PokemonStatsSeeder --force
php artisan db:seed --class=PokemonDescriptionSeeder --force

echo "==> Servidor listo en puerto ${PORT:-10000}"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"

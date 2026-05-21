# Setup PokéTrivia

## 1. Arrancar Laragon y crear la BD
Abre HeidiSQL o phpMyAdmin y crea la base de datos `poketriv`.

## 2. Migrar y seedear (tarda ~25 segundos)
```bash
cd C:\laragon\www\poketriv
php artisan migrate
php artisan db:seed
```

## 3. Acceder
http://poketriv.test (si tienes Laragon configurado)
o http://localhost/poketriv/public

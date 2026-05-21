# ── Imagen base: PHP 8.3 mínima (Alpine = ~5 MB vs Ubuntu ~100 MB) ──────────
FROM php:8.3-cli-alpine

# ── Dependencias del sistema ─────────────────────────────────────────────────
# libpq-dev  → driver PostgreSQL
# libpng-dev → extensión GD (imágenes)
# zip/unzip  → Composer
RUN apk add --no-cache git curl libpq-dev libpng-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql opcache gd

# ── Composer (copiado desde imagen oficial) ───────────────────────────────────
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ── Código de la app ──────────────────────────────────────────────────────────
WORKDIR /app
COPY . .

# ── Instalar dependencias PHP (sin paquetes de desarrollo) ────────────────────
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ── Permisos Laravel ─────────────────────────────────────────────────────────
RUN chmod -R 775 storage bootstrap/cache

# ── Puerto que expone el contenedor (Render inyecta $PORT en runtime) ─────────
EXPOSE 10000

# ── Script de arranque ────────────────────────────────────────────────────────
COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]

# syntax=docker/dockerfile:1

# Base: PHP 8.4 + servidor FrankenPHP (Caddy) con las extensiones que usa Snip.
FROM dunglas/frankenphp:1-php8.4 AS base
RUN install-php-extensions pdo_pgsql opcache intl zip

# Render no concede capacidades extra al contenedor. Si el binario de FrankenPHP
# las trae (para abrir los puertos 80/443), exec falla con "Operation not permitted".
# Escuchamos en un puerto alto, así que no las necesitamos.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libcap2-bin \
    && getcap /usr/local/bin/frankenphp \
    && (setcap -r /usr/local/bin/frankenphp || true) \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# 1) Dependencias de PHP (solo producción).
FROM base AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 2) Compilación de CSS/JS con Vite. Necesita vendor/ porque app.css escanea
#    las vistas de paginación de Laravel para generar sus clases de Tailwind.
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# 3) Imagen final.
FROM base AS final
COPY --from=vendor /app /app
COPY --from=assets /app/public/build /app/public/build
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint \
    && chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV PORT=8080
EXPOSE 8080
CMD ["entrypoint"]

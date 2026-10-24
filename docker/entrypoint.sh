#!/bin/sh
set -e

php artisan migrate --force

# Solo en el primer despliegue: RUN_DEMO_SEED=true carga los enlaces de ejemplo.
if [ "$RUN_DEMO_SEED" = "true" ]; then
    php artisan db:seed --class=LinkSeeder --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec frankenphp php-server --root public/ --listen ":${PORT:-8080}"

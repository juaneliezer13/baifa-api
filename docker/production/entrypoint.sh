#!/bin/sh
set -e

# Asegurar directorios de cache y storage
mkdir -p /var/www/storage/framework/cache/data \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/logs \
         /var/www/bootstrap/cache

# Permisos correctos para usuario web www-data
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Generar enlace simbólico de almacenamiento público si no existe
if [ ! -L /var/www/public/storage ]; then
    php artisan storage:link --no-interaction || true
fi

# Opcional: auto-migración si se especifica la variable de entorno
if [ "${AUTO_MIGRATE:-false}" = "true" ]; then
    echo "[Entrypoint] Ejecutando migraciones de base de datos..."
    php artisan migrate --force --no-interaction || true
fi

# Opcional: optimización de caches de Laravel en producción
if [ "${AUTO_CACHE:-false}" = "true" ] && [ "${APP_ENV:-production}" = "production" ]; then
    echo "[Entrypoint] Optimizando caches de Laravel para producción..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "[Entrypoint] Iniciando servicios de producción (PHP-FPM + Nginx con Supervisor)..."
exec "$@"

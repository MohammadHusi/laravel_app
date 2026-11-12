#!/usr/bin/env sh
set -e

# Pastikan direktori storage & cache selalu ada dan memiliki permission yang tepat
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Jika APP_KEY belum ada di environment, jangan biarkan error crash
if [ -z "$APP_KEY" ]; then
    echo "Peringatan: APP_KEY belum diatur! Pastikan menambahkan APP_KEY di Environment Coolify."
fi

# Jalankan cache config & route jika environment production
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configuration and routes..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Jalankan command default container (apache2-foreground)
exec "$@"

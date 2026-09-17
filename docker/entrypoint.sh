#!/bin/bash
set -e

# Replace LISTEN_PORT placeholder with actual $PORT (defaults to 8080)
PORT="${PORT:-8080}"
sed -i "s/LISTEN_PORT/$PORT/g" /etc/nginx/nginx.conf

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# If APP_KEY is set, cache the configuration
if [ -n "$APP_KEY" ]; then
    echo "Caching Laravel configuration and routes..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Run database migrations if requested
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations on Supabase..."
    php artisan migrate --force || true
fi

# Start PHP-FPM in daemon mode
php-fpm -D

# Start Nginx in foreground
echo "Selection Exercise system started successfully on port $PORT"
exec nginx -g "daemon off;"

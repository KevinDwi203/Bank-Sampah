#!/bin/bash
set -e

# Support dynamic PORT from Render if provided
PORT="${PORT:-80}"
sed -i "s/Listen [0-9]*/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/*.conf

# Link storage if not already linked
php artisan storage:link || true

# Discover packages at runtime
php artisan package:discover --ansi || true

# Clear/optimize cache if APP_KEY is provided
if [ -n "$APP_KEY" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec apache2-foreground

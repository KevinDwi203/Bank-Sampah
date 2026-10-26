#!/bin/bash
set -e

# Support dynamic PORT from Render if provided
PORT="${PORT:-80}"
sed -i "s/Listen [0-9]*/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/*.conf

# Link storage if not already linked
php artisan storage:link || true

# Bersihkan cache bootstrap lama jika ada
rm -f /var/www/html/bootstrap/cache/*.php || true

# Discover packages at runtime
php artisan package:discover --ansi || true

# Jalankan migrasi & seeder secara otomatis saat server menyala
if [ -n "$DB_HOST" ]; then
    echo "Menjalankan migrasi dan seeding database..."
    php artisan migrate --seed --force || true
    echo "Mengimpor data nasabah, transaksi, dan arus kas..."
    php artisan banksampah:import-csv database/import --replace-demo-data --force || true
fi

# Clear/optimize cache if APP_KEY is provided
if [ -n "$APP_KEY" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec apache2-foreground

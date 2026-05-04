#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data
mkdir -p storage/logs storage/app/public bootstrap/cache

# Writable by PHP-FPM (www-data)
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

if [ ! -L public/storage ] && [ -d storage/app/public ]; then
    php artisan storage:link 2>/dev/null || true
fi

# Apply DB migrations on each deploy (Render/docker: build does not run artisan migrate).
if [ "${SKIP_DB_MIGRATE:-}" != "1" ]; then
    php artisan migrate --force --no-interaction
fi

exec "$@"

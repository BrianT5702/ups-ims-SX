#!/bin/bash
set -e

echo "=== Laravel Application Debug Startup ==="
echo "Current directory: $(pwd)"
echo "PHP version: $(php --version | head -n 1)"
echo "Apache version: $(apache2 -v | head -n 1)"

echo "=== Checking file permissions ==="
ls -la /var/www/html/
ls -la /var/www/html/public/
ls -la /var/www/html/storage/

echo "=== Setting up Laravel ==="
cd /var/www/html

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create necessary directories
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
touch /var/www/html/storage/logs/laravel.log
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

echo "=== Testing Laravel ==="
php artisan --version

echo "=== Clearing caches ==="
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "=== Testing database connection ==="
if php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database connected successfully'; } catch (\Exception \$e) { echo 'Database connection failed: ' . \$e->getMessage(); }"; then
    echo "Running migrations..."
    php artisan migrate --force
else
    echo "Skipping migrations due to database connection issues"
fi

echo "=== Rebuilding caches ==="
php artisan config:cache
php artisan view:cache

echo "=== Testing Apache configuration ==="
apache2ctl -t

echo "=== Testing basic PHP functionality ==="
php -r "echo 'PHP is working!'; echo PHP_EOL;"

echo "=== Starting Apache ==="
echo "Apache will start on port 80"
echo "You can test the application at:"
echo "- http://localhost/ (Laravel app)"
echo "- http://localhost/test.php (PHP test)"
echo "- http://localhost/up (Laravel health check)"

exec apache2-foreground 
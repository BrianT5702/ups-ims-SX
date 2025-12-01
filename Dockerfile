# Use the official PHP image with Apache
FROM php:8.3-apache

# Install system dependencies including Node.js
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libonig-dev libpng-dev libxml2-dev zip curl ca-certificates \
    libfreetype6-dev libjpeg62-turbo-dev libwebp-dev libcurl4-openssl-dev \
    # Add Node.js installation
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    # PHP extensions
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module and PHP
RUN a2enmod rewrite && \
    a2enmod php

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf && \
    echo "php_flag display_errors on" >> /etc/apache2/apache2.conf && \
    echo "php_value error_reporting E_ALL" >> /etc/apache2/apache2.conf

# Create Apache virtual host configuration
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        php_flag display_errors on\n\
        php_value error_reporting E_ALL\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    LogLevel debug\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Verify Composer installation and show version
RUN composer --version

# Set Composer environment variables
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=2G

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --verbose --no-scripts

# Copy the rest of the application
COPY . .

# Install Node.js dependencies and build assets
RUN npm install && \
    npm run build

# Create storage directory and set permissions
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} && \
    mkdir -p /var/www/html/storage/logs && \
    touch /var/www/html/storage/logs/laravel.log && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink
RUN ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Create startup script
RUN echo '#!/bin/bash\n\
echo "Checking Laravel status..."\n\
php artisan --version\n\
\n\
echo "Setting up storage and logs..."\n\
mkdir -p /var/www/html/storage/logs\n\
touch /var/www/html/storage/logs/laravel.log\n\
chown -R www-data:www-data /var/www/html/storage/logs\n\
chmod -R 775 /var/www/html/storage/logs\n\
\n\
wait_for_connection() {\n\
  local CONNECTION=\"$1\"\n\
  local MAX_TRIES=3\n\
  local COUNT=0\n\
  echo "Testing database connection: ${CONNECTION}"\n\
  while [ $COUNT -lt $MAX_TRIES ]; do\n\
    if php -r "try { require 'vendor/autoload.php'; $app=require 'bootstrap/app.php'; $kernel=$app->make(Illuminate\\Contracts\\Console\\Kernel::class); $kernel->bootstrap(); $db=Illuminate\\Support\\Facades\\DB::connection('${CONNECTION}'); $db->getPdo(); echo 'ok'; exit(0); } catch (Throwable \$e) { echo \$e->getMessage(); exit(1); }" > /dev/null 2>&1; then\n\
      echo "✓ ${CONNECTION} connection successful"\n\
      return 0\n\
    fi\n\
    COUNT=$((COUNT+1))\n\
    if [ $COUNT -lt $MAX_TRIES ]; then\n\
      echo "⚠ ${CONNECTION} connection failed. Retrying in 3 seconds... ($COUNT/$MAX_TRIES)"\n\
      sleep 3\n\
    fi\n\
  done\n\
  echo "WARNING: ${CONNECTION} connection failed after $MAX_TRIES attempts."\n\
  echo "This might be due to:"\n\
  echo "  - Aiven database IP whitelisting not configured for Render IPs"\n\
  echo "  - Incorrect database credentials in environment variables"\n\
  echo "  - Database server not accessible"\n\
  echo "The application will continue, but database operations may fail."\n\
  echo "Please check your environment variables in Render dashboard and Aiven settings."\n\
  return 1\n\
}\n\
\n\
# Ensure config is fresh\n\
php artisan config:clear || true\n\
\n\
# Test database connections (non-blocking, continue even if they fail)\n\
echo "Testing database connections..."\n\
wait_for_connection ups || echo "Continuing despite UPS connection failure..."\n\
wait_for_connection urs || echo "Continuing despite URS connection failure..."\n\
wait_for_connection ucs || echo "Continuing despite UCS connection failure..."\n\
\n\
echo "Running migrations for UPS/URS/UCS..."\n\
php artisan migrate --force --database=ups || true\n\
php artisan migrate --force --database=urs || true\n\
php artisan migrate --force --database=ucs || true\n\
\n\
echo "Running seeders for UPS/URS/UCS..."\n\
php artisan db:seed --force --database=ups || true\n\
php artisan db:seed --force --database=urs || true\n\
php artisan db:seed --force --database=ucs || true\n\
\n\
echo "Clearing caches..."\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan view:clear\n\
php artisan route:clear\n\
\n\
echo "Rebuilding caches..."\n\
php artisan config:cache\n\
php artisan view:cache\n\
\n\
echo "Setting proper permissions..."\n\
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache\n\
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache\n\
\n\
echo "Starting Apache..."\n\
exec apache2-foreground' > /usr/local/bin/start.sh && \
chmod +x /usr/local/bin/start.sh

# Run Laravel setup commands
RUN php artisan config:cache && \
    php artisan view:cache

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s \
    CMD curl -f http://localhost/ || exit 1

CMD ["/usr/local/bin/start.sh"]
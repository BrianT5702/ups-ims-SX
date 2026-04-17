# syntax=docker/dockerfile:1
# Production: PHP-FPM + Nginx in one image (Supervisor).
# Queue workers: run a separate container/service, e.g.
#   php artisan queue:work --tries=3 --timeout=300

# -----------------------------------------------------------------------------
# Stage: Composer dependencies (PHP 8.3 + ext-gd — matches runtime; avoids
# composer:2 image shipping PHP 8.5+ which breaks locked sabberworm/php-css-parser)
# -----------------------------------------------------------------------------
FROM php:8.3-cli-bookworm AS vendor
WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libfreetype6-dev \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" zip intl gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_MEMORY_LIMIT=-1

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# -----------------------------------------------------------------------------
# Stage: Vite frontend assets
# -----------------------------------------------------------------------------
FROM node:22-bookworm-slim AS frontend
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# -----------------------------------------------------------------------------
# Stage: Runtime (PHP-FPM + Nginx)
# -----------------------------------------------------------------------------
FROM php:8.3-fpm-bookworm

LABEL org.opencontainers.image.title="UPS IMS" \
      org.opencontainers.image.description="Laravel (PHP-FPM + Nginx)"

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# System deps + Nginx + Supervisor + PHP extensions (MySQL, Excel, DomPDF, Livewire)
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        curl \
        ca-certificates \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        zip \
        intl \
        opcache \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/conf.d/*.ini /usr/local/etc/php/conf.d/
COPY docker/php-fpm.d/zzz-pool.conf /usr/local/etc/php-fpm.d/zzz-pool.conf

# Nginx: drop default site, use Laravel vhost
RUN rm -f /etc/nginx/sites-enabled/default \
    && ln -sf /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/laravel

COPY docker/nginx/laravel.conf /etc/nginx/sites-available/laravel

# Supervisor program fragments
COPY docker/supervisor/laravel.conf /etc/supervisor/conf.d/laravel.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
# Windows checkouts often use CRLF; Linux then fails with "no such file or directory" on shebang
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Application (respects .dockerignore)
COPY --chown=www-data:www-data . /var/www/html

COPY --from=vendor --chown=www-data:www-data /app/vendor /var/www/html/vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

RUN mkdir -p \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache/data \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Optimize autoload + package discovery; normalize ownership (runtime user is www-data in FPM)
RUN composer dump-autoload --optimize --classmap-authoritative \
    && php artisan package:discover --ansi || true \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl -fsS http://127.0.0.1/ > /dev/null || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]

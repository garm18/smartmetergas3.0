# syntax=docker/dockerfile:1
FROM php:8.4-apache-bookworm AS php-runtime
WORKDIR /var/www/html

# Pertahankan library runtime setelah paket header dilepas.
RUN apt-get update \
    && apt-get install -y --no-install-recommends 'libicu-dev=72.*' 'libzip-dev=1.7.*' \
    && docker-php-ext-install -j"$(nproc)" intl pdo_mysql zip \
    && apt-mark manual libicu72 libzip4 \
    && apt-get purge -y --auto-remove libicu-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/*

FROM php-runtime AS php-dependencies
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock ./

# Filament membutuhkan source aplikasi sebelum skrip Composer bisa dijalankan.
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

FROM php-dependencies AS php-app
COPY artisan ./
COPY app/ app/
COPY bootstrap/ bootstrap/
COPY config/ config/
COPY database/ database/
COPY public/ public/
COPY resources/ resources/
COPY routes/ routes/
COPY storage/ storage/
RUN composer dump-autoload --no-dev --optimize --no-interaction

FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ resources/
# Tailwind membaca template pagination dari dependensi, terpisah dari kode PHP aplikasi.
COPY --from=php-dependencies /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views/ vendor/laravel/framework/src/Illuminate/Pagination/resources/views/
RUN npm run build

FROM php-runtime AS app
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Apache memakai port 8080 agar bisa berjalan sebagai www-data.
RUN sed -i 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && a2enmod rewrite \
    && install -d -o www-data -g www-data /var/run/apache2 /var/lock/apache2

# Kode PHP berubah lewat rebuild image, jadi OPcache tidak perlu memeriksa stempel waktu.
RUN printf '%s\n' 'opcache.validate_timestamps=0' > "$PHP_INI_DIR/conf.d/app-opcache.ini"

COPY --link --from=php-app /var/www/html/ ./
COPY --link --from=assets /app/public/build/ public/build/
RUN mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

# Debian menetapkan UID dan GID 33 untuk www-data.
USER 33:33
EXPOSE 8080
HEALTHCHECK --interval=15s --timeout=4s --start-period=20s --retries=4 \
    CMD ["curl", "-fsS", "-o", "/dev/null", "http://127.0.0.1:8080/up"]
CMD ["apache2-foreground"]

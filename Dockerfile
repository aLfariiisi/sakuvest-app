FROM php:8.2-fpm

# 1. Tambahkan ekstensi libzip-dev dan zip yang sering diminta oleh Composer
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring xml zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# 2. Beri memori tak terbatas untuk Composer dan abaikan peringatan versi PHP
ENV COMPOSER_MEMORY_LIMIT=-1
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Menjalankan server
CMD php artisan config:cache && php artisan route:cache && php artisan migrate --force && php -S 0.0.0.0:8000 -t public
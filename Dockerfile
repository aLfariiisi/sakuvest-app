FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring xml

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Menjalankan server Laravel saat dihosting
CMD php artisan config:cache && php artisan route:cache && php artisan migrate --force && php -S 0.0.0.0:8000 -t public
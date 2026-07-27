FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring xml zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

ENV COMPOSER_MEMORY_LIMIT=-1

# Pastikan hanya ada SATU kata RUN di sini:
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

CMD php artisan config:clear && php artisan cache:clear && php -S 0.0.0.0:8000 -t public
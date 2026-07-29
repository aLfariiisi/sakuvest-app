FROM php:8.2-apache

# Install ekstensi yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy semua file project
COPY . .

# Install dependencies PHP
RUN composer install --no-dev --optimize-autoloader

# Ubah DocumentRoot Apache ke folder public Laravel
RUN sed -i -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Izinkan mod_rewrite Apache untuk routing Laravel
RUN a2enmod rewrite

# Beri izin akses folder storage dan bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
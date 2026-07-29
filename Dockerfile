FROM php:8.2-apache

# 1. Install ekstensi PHP & Node.js (untuk mem-build tampilan Vite)
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
    libxml2-dev \
    libpq-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 2. Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Install ekstensi PHP untuk database
RUN docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# 4. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Set working directory
WORKDIR /var/www/html

# 6. Copy semua file project
COPY . .

# 7. Install dependencies PHP backend
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 8. Install dependencies Frontend & Build desain Vite (INI YANG BARU)
RUN npm install
RUN npm run build

# 9. Ubah DocumentRoot Apache ke folder public Laravel
RUN sed -i -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# 10. Izinkan mod_rewrite Apache untuk routing
RUN a2enmod rewrite

# 11. Salin file entrypoint dan beri izin eksekusi
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 12. Beri izin akses folder storage untuk Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# 13. Jalankan server
ENTRYPOINT ["docker-entrypoint.sh"]
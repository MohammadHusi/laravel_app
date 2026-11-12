# 1. Gunakan base image resmi PHP 8.2 dengan Apache
FROM php:8.2-apache

# 2. Install dependensi sistem yang dibutuhkan oleh Laravel & Composer
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# 3. Install ekstensi PHP yang wajib untuk database MySQL dan fitur Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 4. Install Composer secara global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Tentukan direktori kerja di dalam container
WORKDIR /var/www/html

# 6. Salin semua file project ke dalam container
COPY . /var/www/html

# 7. Ubah DocumentRoot Apache agar mengarah ke folder public Laravel
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# 8. Aktifkan modul mod_rewrite Apache untuk routing Laravel
RUN a2enmod rewrite

# 9. Berikan izin akses (permissions) untuk folder storage dan bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 10. Buka port 80 untuk akses web
EXPOSE 80

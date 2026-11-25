FROM php:8.5-apache

# Install ekstensi PHP (MySQL, Zip, GD) & Node.js 22 untuk build Vite
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Arahkan Apache ke folder public/ Laravel & aktifkan mod_rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install dependensi PHP & build aset Vite
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci && npm run build \
    && rm -rf node_modules \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

# Sesuaikan port Render, siapkan storage & cache, lalu jalankan Apache
CMD sed -i "s/Listen 80/Listen ${PORT:-80}/g" /etc/apache2/ports.conf \
    && sed -i "s/:80/:${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf \
    && (php artisan storage:link --force || true) \
    && php artisan migrate --force \
    && php artisan optimize \
    && apache2-foreground
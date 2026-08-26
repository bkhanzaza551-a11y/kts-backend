FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    libsqlite3-dev libzip-dev supervisor nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && docker-php-ext-configure pdo_sqlite --with-pdo-sqlite \
    && docker-php-ext-install pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files first for caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application code
COPY . .

# Generate autoloader and optimize
RUN composer dump-autoload --optimize \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Setup storage permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/app/public \
    && mkdir -p storage/logs \
    && chmod -R 775 storage \
    && chmod -R 775 bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Copy Nginx config
COPY deployment/nginx.conf /etc/nginx/sites-available/default

# Copy Supervisor config
COPY deployment/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy startup script
COPY deployment/start.sh /var/www/start.sh
RUN chmod +x /var/www/start.sh

# PHP-FPM pool config for /var/www
RUN echo "[www]\nuser = www-data\ngroup = www-data\nlisten = /var/run/php/php8.3-fpm.sock\nlisten.owner = www-data\nlisten.group = www-data\nlisten.mode = 0660\npm = dynamic\npm.max_children = 5\npm.start_servers = 2\npm.min_spare_servers = 1\npm.max_spare_servers = 3\n" > /usr/local/etc/php-fpm.d/www.conf

EXPOSE 8080

CMD ["/var/www/start.sh"]

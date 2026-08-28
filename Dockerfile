FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    libsqlite3-dev libpq-dev libzip-dev supervisor nginx \
    && docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && docker-php-ext-configure pdo_sqlite --with-pdo-sqlite \
    && docker-php-ext-install pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy all code first
COPY . .

# Create .env for build-time artisan commands
RUN cp .env.example .env 2>/dev/null || printf "APP_NAME=\"KTS Markets\"\nAPP_ENV=local\nAPP_KEY=base64:buildkey1234567890123456789012=\nAPP_DEBUG=true\nDB_CONNECTION=sqlite\n" > .env

# Install dependencies WITH autoloader
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --prefer-dist

# Setup storage
RUN mkdir -p storage/framework/{sessions,views,cache} storage/app/public storage/logs \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY deployment/nginx.conf /etc/nginx/sites-available/default
COPY deployment/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY deployment/start.sh /var/www/start.sh
RUN chmod +x /var/www/start.sh

RUN mkdir -p /var/run/php && printf '[www]\nuser = www-data\ngroup = www-data\nlisten = 127.0.0.1:9000\npm = dynamic\npm.max_children = 5\npm.start_servers = 2\npm.min_spare_servers = 1\npm.max_spare_servers = 3\n' > /usr/local/etc/php-fpm.d/www.conf

EXPOSE 8080

CMD ["/var/www/start.sh"]

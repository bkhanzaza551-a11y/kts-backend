#!/bin/bash
set -e

echo "🚀 Starting KTS Markets Backend..."

# Create .env if it doesn't exist
if [ ! -f /var/www/.env ]; then
    echo "📝 Creating .env file..."
    cat > /var/www/.env <<EOF
APP_NAME="KTS Markets"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://${RAILWAY_PUBLIC_DOMAIN:-kts-backend-production.up.railway.app}
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12
LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error
DB_CONNECTION=sqlite
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database
CACHE_PREFIX=
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=ahmedbilalkhangl09@gmail.com
MAIL_PASSWORD=pvrahwujjucsqwlo
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="ahmedbilalkhangl09@gmail.com"
MAIL_FROM_NAME="KTS 10 Pips"
SANCTUM_STATEFUL_DOMAINS=kts-backend-production.up.railway.app
SANCTUM_TOKEN_EXPIRATION=1440
FRONTEND_URL=http://localhost:3000
EOF
fi

# Generate APP_KEY if not set
if grep -q "APP_KEY=$" /var/www/.env 2>/dev/null; then
    echo "🔑 Generating APP_KEY..."
    php /var/www/artisan key:generate --force
fi

# Create SQLite database if it doesn't exist
touch /var/www/database/database.sqlite
chmod 775 /var/www/database/database.sqlite

# Run migrations
echo "📦 Running migrations..."
php /var/www/artisan migrate --force

# Seed database (only if empty)
USER_COUNT=$(php /var/www/artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "🌱 Seeding database..."
    php /var/www/artisan db:seed --force
fi

# Create storage symlink
php /var/www/artisan storage:link --force

# Fix permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "✅ Application ready!"

# Start Supervisor (runs nginx, php-fpm, queue worker, scheduler)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

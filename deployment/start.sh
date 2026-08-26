#!/bin/bash
set -e

echo "🚀 Starting KTS Markets Backend..."

# Create SQLite database if it doesn't exist
touch /var/www/database/database.sqlite

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

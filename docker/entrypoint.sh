#!/bin/bash

# Copy .env.example to .env if .env does not exist
if [ ! -f "/var/www/html/.env" ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate application key
php artisan key:generate

# Wait for the database to be ready
echo "Waiting for database connection..."
while ! mysqladmin ping -h"db" -P"3306" -u"root" -p"secret" --silent; do
    sleep 1
done
echo "Database connected!"

# Run migrations
php artisan migrate --force

# Start Apache
exec apache2-foreground
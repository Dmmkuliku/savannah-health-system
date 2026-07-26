#!/bin/bash
set -e
cd /var/www/html

if [ -n "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
  export DB_URL="$DATABASE_URL"
fi

php artisan config:clear || true
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction || true
php artisan storage:link || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
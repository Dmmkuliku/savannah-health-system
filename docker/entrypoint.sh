#!/bin/bash
set -e

cd /var/www/html

if [ -z "$APP_KEY" ]; then
  echo "APP_KEY missing â€” generating temporary key"
  php artisan key:generate --force --show > /tmp/appkey || true
fi

# Prefer DATABASE_URL (Render Postgres) when provided
php artisan config:clear || true
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction || true
php artisan storage:link || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"

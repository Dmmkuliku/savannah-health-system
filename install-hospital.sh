#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"

echo "=== Savannah Health System - Local Install ==="

if [ ! -f .env ]; then
  cp .env.example .env
fi

composer install --no-interaction
php artisan key:generate --force

# Create MySQL DB if mysql client exists
if command -v mysql >/dev/null 2>&1; then
  mysql -u root -e "CREATE DATABASE IF NOT EXISTS savannah_health CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || true
fi

php artisan migrate --force
php artisan db:seed --force
npm install
npm run build

echo ""
echo "Install complete."
echo "Run: php artisan serve --host=0.0.0.0 --port=8000"
echo "Admin: admin@savannah.health / Savannah@Admin1"
echo "Register other staff from Staff Users after login."

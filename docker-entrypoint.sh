#!/bin/sh
set -e

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue worker in background
php artisan queue:work --sleep=3 --tries=1 --timeout=300 --no-interaction &

# Web server in foreground
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"

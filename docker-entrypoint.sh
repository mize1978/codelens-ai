#!/bin/sh
set -e

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue worker — restart automatically if it crashes
(while true; do
    php artisan queue:work --sleep=3 --tries=1 --timeout=360 --no-interaction
    echo "[entrypoint] queue:work exited, restarting in 5s..."
    sleep 5
done) &

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"

#!/usr/bin/env bash
set -euo pipefail

export PORT="${PORT:-10000}"
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

cd /var/www/html

php artisan config:clear
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

php artisan storage:link || true

exec "$@"

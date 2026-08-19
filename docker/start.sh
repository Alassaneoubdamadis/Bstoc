#!/bin/sh
set -eu

PORT="${PORT:-10000}"
export PORT

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

mkdir -p \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache \
  public/uploads/platform

chown -R www-data:www-data storage bootstrap/cache public/uploads
chmod -R ug+rwx storage bootstrap/cache public/uploads

if [ ! -L public/storage ]; then
  php artisan storage:link --force || true
fi

php artisan config:clear
php artisan migrate --force --no-interaction
php artisan config:cache
php artisan view:cache

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

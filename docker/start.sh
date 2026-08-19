#!/bin/sh
set -u

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

php artisan config:clear || true

# Ouvrir $PORT tout de suite (Render tue le deploy si rien n'écoute pendant les migrations).
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &
SUPERVISOR_PID=$!

php artisan migrate --force --no-interaction || echo "WARN: migrate a échoué, le site écoute quand même."
php artisan config:cache || true
php artisan view:cache || true

wait "${SUPERVISOR_PID}"

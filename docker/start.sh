#!/bin/sh
set -u

PORT="${PORT:-10000}"
export PORT

# Conteneur Docker / Render : toujours stderr (sauf LOG_TO_FILE=true).
# Évite "Permission denied … storage/logs/laravel.log" quand artisan (root)
# crée le fichier puis PHP-FPM (www-data) ne peut plus y écrire.
if [ "${LOG_TO_FILE:-}" != "true" ]; then
  export LOG_CHANNEL=stderr
fi
export LOG_DEPRECATIONS_CHANNEL="${LOG_DEPRECATIONS_CHANNEL:-null}"

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

fix_storage_perms() {
  mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    bootstrap/cache \
    public/uploads/platform \
    public/uploads/product_barcode

  # artisan tourne en root : un laravel.log créé alors bloque www-data (PHP-FPM).
  rm -f storage/logs/laravel.log
  touch storage/logs/laravel.log

  chown -R www-data:www-data storage bootstrap/cache public/uploads
  chmod -R ug+rwx storage bootstrap/cache public/uploads
  # Garantit l'écriture même si un fichier a été créé en root entre-temps.
  chmod -R a+rwX storage bootstrap/cache public/uploads 2>/dev/null || true
}

fix_storage_perms

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

# Remettre les droits après les commandes root (migrate / cache).
fix_storage_perms

wait "${SUPERVISOR_PID}"

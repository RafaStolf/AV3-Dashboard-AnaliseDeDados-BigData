#!/bin/sh
set -eu
cd /var/www/html

# A named volume may be empty or owned by root on its first mount.
if [ "$(id -u)" = 0 ]; then
    mkdir -p storage/app storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    if [ "${1:-}" = php-fpm ]; then
        gosu www-data "$0" deploy-initialize
        # The standard root FPM master opens container stderr and drops workers
        # to www-data via the pool configuration. Artisan never runs as root.
        exec "$@"
    fi
    exec gosu www-data "$0" "$@"
fi

# Maintenance commands do not migrate or import implicitly.
if [ "${1:-}" = php-fpm ] || [ "${1:-}" = deploy-initialize ]; then
    : "${APP_KEY:?Supply a persistent APP_KEY at runtime}"
    : "${DB_DATABASE:?Set an absolute SQLite DB_DATABASE inside the storage volume}"
    case "$DB_DATABASE" in
        /var/www/html/storage/*) ;;
        *) echo 'DB_DATABASE must be inside /var/www/html/storage' >&2; exit 1 ;;
    esac
    mkdir -p "$(dirname "$DB_DATABASE")"
    if [ ! -f "$DB_DATABASE" ]; then
        touch "$DB_DATABASE"
        chmod 600 "$DB_DATABASE"
    fi
    php artisan config:clear
    php artisan migrate --force --no-interaction
    php deploy/initialize.php
    if [ "${DEPLOY_CACHE:-false}" = true ]; then
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
    else
        php artisan route:clear
        php artisan view:clear
    fi
    if [ "${1:-}" = deploy-initialize ]; then exit 0; fi
fi
exec "$@"

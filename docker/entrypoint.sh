#!/bin/sh
set -e

ROLE=${CONTAINER_ROLE:-app}
ENVIRONMENT=${APP_ENV:-production}

echo "[entrypoint] Starting container with role: ${ROLE} in environment: ${ENVIRONMENT}"

# 1. Wait for Database dependency
if [ -n "$DB_HOST" ]; then
    DB_PORT=${DB_PORT:-3306}
    echo "[entrypoint] Waiting for database at ${DB_HOST}:${DB_PORT}..."
    timeout=60
    while ! nc -z "$DB_HOST" "$DB_PORT" >/dev/null 2>&1; do
        timeout=$((timeout - 1))
        if [ "$timeout" -le 0 ]; then
            echo "[entrypoint] ERROR: Timed out waiting for database ${DB_HOST}:${DB_PORT}"
            exit 1
        fi
        sleep 1
    done
    echo "[entrypoint] Database is ready."
fi

# 2. Wait for Redis dependency (if configured)
if [ -n "$REDIS_HOST" ]; then
    REDIS_PORT=${REDIS_PORT:-6379}
    echo "[entrypoint] Waiting for Redis at ${REDIS_HOST}:${REDIS_PORT}..."
    timeout=30
    while ! nc -z "$REDIS_HOST" "$REDIS_PORT" >/dev/null 2>&1; do
        timeout=$((timeout - 1))
        if [ "$timeout" -le 0 ]; then
            echo "[entrypoint] WARNING: Timed out waiting for Redis ${REDIS_HOST}:${REDIS_PORT}, proceeding..."
            break
        fi
        sleep 1
    done
    echo "[entrypoint] Redis check finished."
fi

# 3. Production Optimizations
if [ "$ENVIRONMENT" = "production" ]; then
    echo "[entrypoint] Caching configuration, routes, and views..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# 4. Role-based execution
case "$ROLE" in
    app)
        if [ "$AUTORUN_MIGRATIONS" = "true" ] || [ "$AUTORUN_MIGRATIONS" = "1" ]; then
            echo "[entrypoint] Running database migrations..."
            php artisan migrate --force
        fi

        if [ "$#" -gt 0 ]; then
            exec "$@"
        else
            echo "[entrypoint] Starting php-fpm..."
            exec php-fpm -F
        fi
        ;;

    worker)
        echo "[entrypoint] Starting queue worker..."
        exec php artisan queue:work --verbose --tries=3 --timeout=90
        ;;

    scheduler)
        echo "[entrypoint] Starting scheduler loop..."
        while [ true ]; do
            php artisan schedule:run --verbose --no-interaction &
            sleep 60
        done
        ;;

    all-in-one)
        if [ "$AUTORUN_MIGRATIONS" = "true" ] || [ "$AUTORUN_MIGRATIONS" = "1" ]; then
            echo "[entrypoint] Running database migrations..."
            php artisan migrate --force
        fi
        echo "[entrypoint] Starting supervisord managing php-fpm, worker, and scheduler..."
        exec supervisord -c /etc/supervisor/supervisord.conf
        ;;

    *)
        echo "[entrypoint] Custom command specified: $@"
        exec "$@"
        ;;
esac

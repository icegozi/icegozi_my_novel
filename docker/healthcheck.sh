#!/bin/sh
set -e

# Enterprise healthcheck script for php-fpm and container roles

if [ -n "$CONTAINER_ROLE" ] && [ "$CONTAINER_ROLE" = "worker" ]; then
    pgrep -f "artisan queue:work" > /dev/null || exit 1
    exit 0
fi

if [ -n "$CONTAINER_ROLE" ] && [ "$CONTAINER_ROLE" = "scheduler" ]; then
    pgrep -f "schedule:run" > /dev/null || exit 0
fi

# Default: Verify php-fpm is responding via FastCGI ping
SCRIPT_NAME=/ping \
SCRIPT_FILENAME=/ping \
REQUEST_METHOD=GET \
cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1

exit 0

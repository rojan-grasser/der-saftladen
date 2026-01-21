#!/bin/bash
set -e

if [ "$RUNMODE" -eq "server" ]; then
    # Run migrations
    php artisan migrate --force

    # Start Octane
    php artisan octane:start --host=0.0.0.0 --port=9000 --server=roadrunner
elif [ "$RUNMODE" -eq "housekeeping" ]; then
    php artisan app:housekeeping
else
    echo "[ERROR]: Unknown runmode $RUNMODE"
fi

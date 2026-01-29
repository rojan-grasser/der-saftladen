#!/bin/bash
set -e

# Run migrations
php artisan migrate --force

# Run feature deployment initialization if FEATURE env var is set
if [ -n "$FEATURE" ]; then
  php artisan app:feature-deployment-init
fi

# Start Octane
php artisan octane:start --host=0.0.0.0 --port=9000 --server=roadrunner

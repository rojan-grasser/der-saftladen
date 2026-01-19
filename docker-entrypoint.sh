#!/bin/bash
set -e

# Run migrations
php artisan migrate --force

# Start Octane
php artisan octane:start --host=0.0.0.0 --port=9000 --server=swoole

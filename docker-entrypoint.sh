#!/bin/bash
set -e

# Run migrations
php artisan migrate --force

# Start Octane
rr serve -c /.rr.yaml

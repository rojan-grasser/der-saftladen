#!/bin/bash
set -e

if [[ "$RUNMODE" == 'terraform' ]]; then
    cd terraform
    terraform init
    terraform plan
    terraform apply -auto-approve
else [[ "$RUNMODE" == 'server' ]]; then
    # Run migrations
    php artisan migrate --force

    # Run feature deployment initialization if FEATURE env var is set
    if [ -n "$FEATURE" ]; then
        echo "Creating init user with email 'test-admin@example.com' and password 'password'"

        php artisan app:feature-deployment-init
    fi

    # Start Octane
    php artisan octane:start --host=0.0.0.0 --port=9000 --server=roadrunner
fi

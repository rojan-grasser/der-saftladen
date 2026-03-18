#!/bin/bash
set -e

function tf-runmode() {
    if [[ "$WAIT_FOR_MINIO" == 'true' ]]; then
        echo "Waiting for MinIO to be ready..."
        until wget -qO- $AWS_ENDPOINT/minio/health/live > /dev/null 2>&1; do
            echo "MinIO not ready yet, retrying in 2s..."
            sleep 2
        done
    fi

    cd terraform

    export TF_DATA_DIR=/terraform/.terraform

    terraform init
    terraform plan
    terraform apply -auto-approve

    cd ..
}

function server-runmode() {
    # Run migrations
    php artisan migrate --force

    # Run feature deployment initialization if FEATURE env var is set
    if [ -n "$FEATURE" ]; then
        echo "Creating init user with email 'test-admin@example.com' and password 'password'"

        php artisan app:feature-deployment-init
    fi

    WORKERS=${OCTANE_WORKERS:-6}

    php artisan octane:start \
        --host=0.0.0.0 \
        --port=$APP_PORT \
        --server=roadrunner \
        --workers=$WORKERS
}

if [[ "$RUNMODE" == 'terraform' ]]; then
    tf-runmode
elif [[ "$RUNMODE" == 'server' ]]; then
    server-runmode
elif [[ "$RUNMODE" == 'all' ]]; then
    tf-runmode
    server-runmode
fi

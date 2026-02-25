#!/usr/bin/env bash

set -euo pipefail

for attempt in 1 2 3 4 5; do
    if docker compose exec "$@" php-fpm php phing -D production.confirm.action=y db-create frontend-api-generate-new-keys build-demo-dev-quick elasticsearch-index-recreate elasticsearch-export; then
        exit 0
    fi

    if [[ "$attempt" -eq 5 ]]; then
        exit 1
    fi

    sleep_time=$((attempt * 10))
    echo "build-demo-dev-quick failed (attempt $attempt/5), retrying in ${sleep_time}s..."
    sleep "$sleep_time"
done

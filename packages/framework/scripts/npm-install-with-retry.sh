#!/bin/sh

set -e

npm_executable="${1:-npm}"
max_attempts=5

for attempt in 1 2 3 4 5; do
    if "$npm_executable" install; then
        exit 0
    fi

    if [ "$attempt" -eq "$max_attempts" ]; then
        exit 1
    fi

    sleep_time=$((attempt * 5))
    echo "npm install failed (attempt $attempt/$max_attempts), retrying in $sleep_time s..."
    sleep "$sleep_time"
done

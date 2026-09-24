#!/usr/bin/env bash

# Regression check for the "db-migrations-count-with-maintenance" Phing target:
# the maintenance mode must stay off when there is nothing to migrate and must be enabled when there is a migration to execute.

set -euo pipefail

TEMPORARY_MIGRATION_PATH=""
# the administration is served by PHP directly (the homepage is proxied to the storefront, which does not run in this CI job)
WEBSITE_URL="http://127.0.0.1:8000/admin/"

cleanup() {
    if [[ -n "${TEMPORARY_MIGRATION_PATH}" ]]; then
        docker compose exec -T php-fpm rm -f "${TEMPORARY_MIGRATION_PATH}"
    fi
    docker compose exec -T php-fpm php phing maintenance-off > /dev/null
}
trap cleanup EXIT

runTargetAndCheckOutput() {
    local expectedOutput="$1"
    local output

    output="$(docker compose exec -T php-fpm php phing db-migrations-count-with-maintenance)"
    echo "${output}"

    if ! grep --quiet --fixed-strings "${expectedOutput}" <<< "${output}"; then
        echo "FAILED: expected the target output to contain \"${expectedOutput}\""
        exit 1
    fi
}

assertHttpStatusCode() {
    local expectedStatusCode="$1"
    local description="$2"
    local actualStatusCode

    actualStatusCode="$(curl --silent --output /dev/null --write-out '%{http_code}' "${WEBSITE_URL}")"

    if [[ "${actualStatusCode}" != "${expectedStatusCode}" ]]; then
        echo "FAILED: ${description} - expected HTTP ${expectedStatusCode}, got HTTP ${actualStatusCode}"
        exit 1
    fi

    echo "OK: ${description} (HTTP ${actualStatusCode})"
}

echo "Running the target without migrations to execute..."
runTargetAndCheckOutput "There is no need to enable maintenance mode"
assertHttpStatusCode 200 "maintenance mode stays disabled when there is nothing to migrate"

echo "Running the target with a migration to execute..."
generatorOutput="$(docker compose exec -T php-fpm php bin/console shopsys:migrations:generate --empty --no-interaction)"
echo "${generatorOutput}"
TEMPORARY_MIGRATION_PATH="$(sed -n 's/.*Migration file "\([^"]*\)" was saved.*/\1/p' <<< "${generatorOutput}")"

if [[ -z "${TEMPORARY_MIGRATION_PATH}" ]]; then
    echo "FAILED: could not find the path of the generated migration in the generator output"
    exit 1
fi

runTargetAndCheckOutput "Migrations to execute: 1 - enabling maintenance mode"
assertHttpStatusCode 503 "maintenance mode is enabled when there is a migration to execute"

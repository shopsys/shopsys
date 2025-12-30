#!/bin/bash

DIR="${BASH_SOURCE%/*}"
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
. "$DIR/configure.sh"

echo "Installing application inside a php-fpm container"

docker compose exec -T php-fpm composer install
docker compose exec -T php-fpm ./phing db-create test-db-create frontend-api-generate-new-keys build-demo-dev-quick

DOMAINS_URLS_FILE="$DIR/../app/config/domains_urls.yaml"
DOMAIN_URLS=($(grep -oP 'url:\s*\K\S+' "$DOMAINS_URLS_FILE"))

FIRST_DOMAIN_URL="${DOMAIN_URLS[0]}"

echo "Your application is now ready under the following domains:"
for url in "${DOMAIN_URLS[@]}"; do
    echo "  - $url"
done
echo "Administration is ready under ${FIRST_DOMAIN_URL}/admin, you can log in using username 'admin' and password 'admin123'"

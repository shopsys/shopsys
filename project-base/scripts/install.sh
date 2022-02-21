#!/bin/bash

DIR="${BASH_SOURCE%/*}"
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
. "$DIR/configure.sh"

echo "Installing application inside a php-fpm container"

docker-compose exec -T php-fpm composer install
docker-compose exec -T php-fpm ./phing db-create test-db-create build-demo-dev-quick error-pages-generate

echo "Your application is now ready under http://127.0.0.1:8000 and second domain under http://127.0.0.2:8000"
echo "Administration is ready under http://127.0.0.1:8000/admin, you can log in using username 'admin' and password 'admin123'"

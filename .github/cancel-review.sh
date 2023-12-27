BRANCH_NAME=${1,,}

if [ -n "$BRANCH_NAME" ]; then
    echo "Info: Trying to cancel review for branch $BRANCH_NAME"

    if [ -d "../$BRANCH_NAME" ]; then
        cd "../$BRANCH_NAME"

        docker compose exec php-fpm php phing -D production.confirm.action=y elasticsearch-index-delete clean-redis clean-redis-old clean-redis-storefront
        docker compose exec php-fpm php ./bin/console doctrine:database:drop --force
        bash .github/rabbitmq-vhost.sh remove $BRANCH_NAME

        docker compose down -v --remove-orphans
        docker system prune -a -f
        cd ..
        rm -rf "$BRANCH_NAME"
    else
        echo "Info: Branch directory not found - review has already been cancelled."
    fi
else
    echo "Error: Branch name not provided."
fi

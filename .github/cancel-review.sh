BRANCH_NAME=${1,,}
BRANCH_NAME_ESCAPED="${BRANCH_NAME//./-}"

if [ -n "$BRANCH_NAME" ]; then
    echo "Info: Trying to cancel review for branch $BRANCH_NAME"

    if [ -d "../$BRANCH_NAME" ]; then
        cd "../$BRANCH_NAME"

        docker compose exec php-fpm php phing elasticsearch-index-delete clean-redis
        docker compose exec php-fpm php ./bin/console doctrine:database:drop --force

        docker compose down -v --remove-orphans
        docker system prune -a -f
        cd ..
        rm -rf "$BRANCH_NAME"
    else
        echo "Info: Branch directory not found - review has already been cancelled."
    fi

    echo "Cleaning up Elasticsearch"
    docker exec 6938e9798f9f curl -X DELETE "http://localhost:9200/${BRANCH_NAME_ESCAPED}_*"

    echo "Cleaning up Redis"
    docker exec 9468e1494b30 redis-cli keys "${BRANCH_NAME_ESCAPED}:*" | xargs -L1 redis-cli del

else
    echo "Error: Branch name not provided."
fi

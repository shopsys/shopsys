BRANCH_NAME=${1,,}

if [ -n "$BRANCH_NAME" ]; then
    echo "Info: Trying to cancel review for branch ${BRANCH_NAME}"

    docker exec github-runner-postgres-1 psql -d postgres -c "DROP DATABASE IF EXISTS \"${BRANCH_NAME}\";"
    docker exec github-runner-redis-1 redis-cli --scan --pattern "${BRANCH_NAME}:*" | xargs -r docker exec github-runner-redis-1 redis-cli del
    docker exec github-runner-rabbitmq-1 rabbitmqctl delete_vhost "${BRANCH_NAME}" || true
    docker exec github-runner-elasticsearch-1 curl -X DELETE "localhost:9200/${BRANCH_NAME}_*" 2>/dev/null || true

    BASE_DIR="/home/github-runner/actions-runner/_work/shopsys/shopsys"

    if [ -d "$BASE_DIR/$BRANCH_NAME" ]; then
        cd "$BASE_DIR/$BRANCH_NAME"

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

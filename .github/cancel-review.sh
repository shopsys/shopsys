BRANCH_NAME=${1,,}

echo "=== Cancel Review Script Debug ==="
echo "Raw parameter received: '$1'"
echo "Processed branch name (lowercase): '$BRANCH_NAME'"
echo "================================="

if [ -n "$BRANCH_NAME" ]; then
    echo "Info: Trying to cancel review for branch ${BRANCH_NAME}"

    echo "Dropping database..."
    docker exec github-runner-postgres-1 psql -d postgres -c "DROP DATABASE IF EXISTS \"${BRANCH_NAME}\";"

    echo "Cleaning Redis keys..."
    docker exec github-runner-redis-1 redis-cli --scan --pattern "${BRANCH_NAME}:*" | xargs -r docker exec github-runner-redis-1 redis-cli del

    echo "Deleting RabbitMQ vhost..."
    docker exec github-runner-rabbitmq-1 rabbitmqctl delete_vhost "${BRANCH_NAME}" || true

    echo "Deleting Elasticsearch indices..."
    docker exec github-runner-elasticsearch-1 curl -X DELETE "localhost:9200/${BRANCH_NAME}_*" 2>/dev/null || true

    BASE_DIR="/home/github-runner/actions-runner/_work/shopsys/shopsys"
    echo "Base directory: $BASE_DIR"
    echo "Target directory: $BASE_DIR/$BRANCH_NAME"

    if [ -d "$BASE_DIR/$BRANCH_NAME" ]; then
        echo "Found branch directory, cleaning up..."
        cd "$BASE_DIR/$BRANCH_NAME"

        docker compose down -v --remove-orphans
        docker system prune -a -f
        cd ..
        rm -rf "$BRANCH_NAME"
        echo "Directory cleanup completed."
    else
        echo "Info: Branch directory not found - review has already been cancelled."
    fi

    echo "Review cancellation completed for branch: $BRANCH_NAME"
else
    echo "Error: Branch name not provided."
    exit 1
fi

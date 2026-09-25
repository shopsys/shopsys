BRANCH_NAME=${1,,}
BRANCH_NAME="${BRANCH_NAME//[\/.]/-}"

if [ -n "$BRANCH_NAME" ]; then
    echo "Info: Trying to cancel review for branch ${BRANCH_NAME}"

    docker exec github-runner-postgres-1 psql -d postgres -c "DROP DATABASE IF EXISTS \"${BRANCH_NAME}\";"
    docker exec github-runner-redis-1 redis-cli --scan --pattern "${BRANCH_NAME}:*" | xargs -r docker exec github-runner-redis-1 redis-cli del
    docker exec github-runner-rabbitmq-1 rabbitmqctl delete_vhost "${BRANCH_NAME}" || true
    docker exec github-runner-elasticsearch-1 curl -X DELETE "localhost:9200/${BRANCH_NAME}_*" 2>/dev/null || true

    REVIEWS_DIR="/home/github-runner/reviews"

    if [ -n "$(docker ps -a -q --filter "label=com.docker.compose.project=${BRANCH_NAME}")" ]; then
        docker compose -p "${BRANCH_NAME}" down -v --remove-orphans
        docker system prune -a -f
    else
        echo "Info: Review containers not found - review has already been cancelled."
    fi

    rm -rf "${REVIEWS_DIR:?}/${BRANCH_NAME}"
else
    echo "Error: Branch name not provided."
fi

BRANCH_NAME=${1,,}
BRANCH_NAME="${BRANCH_NAME//[\/.]/-}"

# docker compose derives the project name from the review directory name and keeps only [a-z0-9_-] of it
PROJECT_NAME=$(printf '%s' "${BRANCH_NAME}" | tr -cd 'a-z0-9_-' | sed 's/^[_-]*//')

if [ "$PROJECT_NAME" = "github-runner" ]; then
    echo "Error: ${BRANCH_NAME} is the project of the review server itself, refusing to cancel it."
    exit 1
fi

if [ -n "$BRANCH_NAME" ]; then
    echo "Info: Trying to cancel review for branch ${BRANCH_NAME}"

    docker exec github-runner-postgres-1 psql -d postgres -c "DROP DATABASE IF EXISTS \"${BRANCH_NAME}\";"
    docker exec github-runner-redis-1 redis-cli --scan --pattern "${BRANCH_NAME}:*" | xargs -r docker exec github-runner-redis-1 redis-cli del
    docker exec github-runner-rabbitmq-1 rabbitmqctl delete_vhost "${BRANCH_NAME}" || true
    docker exec github-runner-elasticsearch-1 curl -X DELETE "localhost:9200/${BRANCH_NAME}_*" 2>/dev/null || true

    REVIEWS_DIR="/home/github-runner/reviews"

    if [ -n "$(docker ps -a -q --filter "label=com.docker.compose.project=${PROJECT_NAME}")" ]; then
        docker compose -p "${PROJECT_NAME}" down -v --remove-orphans
        docker image prune -a -f --filter "until=24h"
    else
        echo "Info: Review containers not found - review has already been cancelled."
    fi

    rm -rf "${REVIEWS_DIR:?}/${BRANCH_NAME}"

    # Reviews deployed before the shared reviews directory existed live in the workspace of the runner,
    # remove this once no such review is left
    rm -rf /home/github-runner/actions-runner*/_work/shopsys/shopsys/"${BRANCH_NAME}"
else
    echo "Error: Branch name not provided."
fi

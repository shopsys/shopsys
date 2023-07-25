BRANCH_NAME=$1

echo "Trying to cancel review for branch $BRANCH_NAME"

if [ -d "../$BRANCH_NAME" ]; then
    cd "../$BRANCH_NAME"
    docker compose down -v --remove-orphans
    docker system prune -a -f
    cd ..
    rm -rf "$BRANCH_NAME"
fi

BRANCH_NAME=${1,,}

if [ -n "$BRANCH_NAME" ]; then
    echo "Info: Trying to cancel review for branch $BRANCH_NAME"

    if [ -d "../$BRANCH_NAME" ]; then
        cd "../$BRANCH_NAME"
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

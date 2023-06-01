if [ -d "$BRANCH_NAME" ]; then
    cd "$BRANCH_NAME"
    docker compose down --rmi all -v --remove-orphans
    cd ..
    rm -rf "$BRANCH_NAME"
fi

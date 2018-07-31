#!/usr/bin/env bash

# Add repositories to a monorepo from specified remotes
# You must first add the remotes by "git remote add <remote-name> <repository-url>" and fetch from them by "git fetch --all"
# It will merge master branches of the monorepo and all remotes together while keeping all current branches in monorepo intact
# If subdirectory is not specified remote name will be used instead
#
# Usage: monorepo_add.sh <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...
#
# Example: monorepo_add.sh additional-repository package-gamma:packages/gamma package-delta:packages/delta

# Check provided arguments
if [ "$#" -lt "1" ]; then
    echo 'Please provide at least 1 remote to be added into an existing monorepo'
    echo 'Usage: monorepo_add.sh <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...'
    echo 'Example: monorepo_add.sh additional-repository package-gamma:packages/gamma package-delta:packages/delta'
    exit
fi
# Get directory of the other scripts
MONOREPO_SCRIPT_DIR=$(dirname "$0")
# Wipe original refs (possible left-over back-up after rewriting git history)
$MONOREPO_SCRIPT_DIR/original_refs_wipe.sh
for PARAM in $@; do
    # Parse parameters in format <remote-name>[:<subdirectory>]
    PARAM_ARR=(${PARAM//:/ })
    REMOTE=${PARAM_ARR[0]}
    SUBDIRECTORY=${PARAM_ARR[1]}
    if [ "$SUBDIRECTORY" == "" ]; then
        SUBDIRECTORY=$REMOTE
    fi
    echo "Building branch 'master' of the remote '$REMOTE'"
    git checkout --detach $REMOTE/master
    $MONOREPO_SCRIPT_DIR/rewrite_history_into.sh $SUBDIRECTORY
    MERGE_REFS="$MERGE_REFS $(git rev-parse HEAD)"
    # Wipe the back-up of original history
    $MONOREPO_SCRIPT_DIR/original_refs_wipe.sh
done
# Merge all master branches
COMMIT_MSG="merge multiple repositories into an existing monorepo"$'\n'$'\n'"- merged using: 'monorepo_add.sh $@'"$'\n'"- see https://github.com/shopsys/monorepo-tools"
git checkout master
echo "Merging refs: $MERGE_REFS"
git merge --no-commit -q $MERGE_REFS --allow-unrelated-histories
echo 'Resolving conflicts using trees of all parents'
for REF in $MERGE_REFS; do
    # Add all files from all master branches into index
    # "git read-tree" with multiple refs cannot be used as it is limited to 8 refs
    git ls-tree -r $REF | git update-index --index-info
done
git commit -m "$COMMIT_MSG"
git reset --hard


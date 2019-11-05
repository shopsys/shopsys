#!/usr/bin/env bash

# Rewrite git history so that all filepaths are in a specific subdirectory
# You can use arguments for "git rev-list" to specify what commits to rewrite (defaults to rewriting history of the checked-out branch)
# All tags in the provided range will be rewritten as well
#
# Usage: rewrite_history_into.sh <subdirectory> [<rev-list-args>]
#
# Example: rewrite_history_into.sh packages/alpha
# Example: rewrite_history_into.sh main-repository --branches

SUBDIRECTORY=$1
REV_LIST_PARAMS=${@:2}
[[ ${@:3} == "--filter-commits" ]] && FILTER_COMMITS=true
if [[ $REV_LIST_PARAMS == "--filter-commits" ]]; then
    REV_LIST_PARAMS=''
    FILTER_COMMITS=true
fi

echo "Rewriting history into a subdirectory '$SUBDIRECTORY'"

if [[ $FILTER_COMMITS == true ]]; then
    echo "Filter commits list:"
    COMMITS_LIST=$(cat)
    if [ -z "$COMMITS_LIST" ]; then
        echo "Commits list should be passed. E.g. git rev-list <options> | rewrite_history_into.sh"
        exit 1
    fi
    echo "$(echo "$COMMITS_LIST" | wc -l) item(s)"
fi

# All paths in the index are prefixed with a subdirectory and the index is updated
# Previous index file is replaced by a new one (otherwise each file would be in the index twice)
# The tags are rewritten as well as commits (the "cat" command will use original name without any change)
COMMITS_LIST=$COMMITS_LIST SUBDIRECTORY_SED=${SUBDIRECTORY//-/\\-} TAB=$'\t' git filter-branch \
    --index-filter '
    if [ -z "$COMMITS_LIST" ] || [ ! -z $(echo "$COMMITS_LIST" | grep "$GIT_COMMIT") ]; then
        git ls-files -s | sed "s-$TAB\"*-&$SUBDIRECTORY_SED/-" | GIT_INDEX_FILE=$GIT_INDEX_FILE.new git update-index --index-info && if [ -f "$GIT_INDEX_FILE.new" ]; then mv "$GIT_INDEX_FILE.new" "$GIT_INDEX_FILE"; fi
    fi' \
    --tag-name-filter 'cat' \
    -- $REV_LIST_PARAMS

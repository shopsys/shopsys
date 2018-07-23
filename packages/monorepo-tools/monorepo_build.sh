#!/usr/bin/env bash

# Build monorepo from specified remotes
# You must first add the remotes by "git remote add <remote-name> <repository-url>" and fetch from them by "git fetch --all"
# Final monorepo will contain all branches from the first remote and master branches of all remotes will be merged
# If subdirectory is not specified remote name will be used instead
#
# Usage: monorepo_build.sh <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...
#
# Example: monorepo_build.sh main-repository package-alpha:packages/alpha package-beta:packages/beta

# Check provided arguments
PARTIAL=false
if [ "$#" -lt "2" ]; then
    echo 'Please provide at least 2 remotes to be merged into a new monorepo'
    echo 'Usage: monorepo_build.sh [OPTION] <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...'
    echo ''
    echo 'Example: monorepo_build.sh main-repository package-alpha:packages/alpha package-beta:packages/beta'
    exit
fi
COMMIT_MSG="merge multiple repositories into a monorepo"$'\n'$'\n'"- merged using: 'monorepo_build.sh $@'"$'\n'"- see https://github.com/shopsys/monorepo-tools"
# Get directory of the other scripts
MONOREPO_SCRIPT_DIR=$(dirname "$0")
. $MONOREPO_SCRIPT_DIR/build_or_add_repos.sh $@

#!/usr/bin/env bash

# Build monorepo from specified remotes
# You must first add the remotes by "git remote add <remote-name> <repository-url>" and fetch from them by "git fetch --all"
# Final monorepo will be augmented with master branches of all remotes specified
# If subdirectory is not specified remote name will be used instead
#
# Usage: monorepo_add.sh <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...
#
# Example: monorepo_add.sh additional-repository package-alpha:packages/alpha package-beta:packages/beta

# Check provided arguments
PARTIAL=true
if [ "$#" -lt "1" ]; then
    echo 'Please provide at least 1 remote to be merged into an existing monorepo'
    echo 'Usage: monorepo_add.sh <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...'
    echo ''
    echo 'Example: monorepo_add.sh additional-repository package-alpha:packages/alpha package-beta:packages/beta'
    exit
fi
COMMIT_MSG="merge multiple repositories into a monorepo"$'\n'$'\n'"- merged using: 'monorepo_add.sh $@'"$'\n'"- see https://github.com/shopsys/monorepo-tools"
# Get directory of the other scripts
MONOREPO_SCRIPT_DIR=$(dirname "$0")
. $MONOREPO_SCRIPT_DIR/build_or_add_repos.sh $@
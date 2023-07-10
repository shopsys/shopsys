#!/bin/sh
set -euxo pipefail

WORKING_DIR_NAME=$1

SOURCE_URL=$2
SOURCE_BRANCH=$3

TARGET_URL=$4
TARGET_BRANCH=$5

if [[ -d "$WORKING_DIR_NAME" ]]; then
    rm -rf "$WORKING_DIR_NAME"
fi

git clone --bare --single-branch --branch="$SOURCE_BRANCH" "$SOURCE_URL" "$WORKING_DIR_NAME"
cd "$WORKING_DIR_NAME"


git push --force "$TARGET_URL" "$SOURCE_BRANCH":"$TARGET_BRANCH"

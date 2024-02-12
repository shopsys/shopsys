#!/bin/bash

set -eu -o pipefail

# ANSI color codes
RED="\e[31m"
GREEN="\e[32m"
BLUE="\e[34m"
NC="\e[0m"

# Lists packages that should be split
# If you modify this list do not forget updating:
#   \Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker
#   /docs/introduction/monorepo.md
#   /CHANGELOG.md
#   "replace" section in monorepo's composer.json
get_all_packages() {
    echo "framework \
        frontend-api \
        google-cloud-bundle \
        s3-bridge \
        category-feed-luigis-box \
        product-feed-zbozi \
        product-feed-google \
        product-feed-heureka \
        product-feed-heureka-delivery \
        product-feed-luigis-box \
        article-feed-luigis-box \
        plugin-interface \
        coding-standards \
        http-smoke-testing \
        form-types-bundle \
        migrations \
        monorepo-tools \
        php-image \
        persoo \
        project-base"
}

# Gets a subdirectory in which a package is located
get_package_subdirectory() {
    PACKAGE=$1

    if [[ "$PACKAGE" == "project-base" ]]; then
        echo $PACKAGE
    else
        echo "packages/$PACKAGE"
    fi
}

assert_remote_template_variable() {
    if [[ "$REMOTE_TEMPLATE" == "" ]]; then
        echo -e "${RED}You must provide a remote template!${NC}"
        exit 1
    fi
}

assert_split_branch_is_not_protected() {
    if [[ "$SPLIT_BRANCH" == "master" || "$SPLIT_BRANCH" == "main" || "$SPLIT_BRANCH" =~ ^[0-9]+\.[0-9]+$ ]]; then
        echo -e "${RED}You cannot work with master, main or version-like branch!${NC}"
        exit 1
    fi
}

assert_split_branch_variable() {
    if [[ "$SPLIT_BRANCH" == "" ]]; then
        echo -e "${RED}You must provide a branch name to work on!${NC}"
        exit 1
    fi
}

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
#   /CHANGELOG-XX.md
#   /packages/framework/src/Resources/config/packages_registry.yaml
#   project-base/app/config/bundles.php
#   "replace", "autoload", and "autoload-dev" sections in monorepo's composer.json
#   if the package has unit tests, add the configuration into the "tests-unit" and "tests-unit-single" targets in monorepo's build.xml
#   if the package has a .github/workflows/run-checks-tests.yaml, it must declare the "workflow_dispatch:" trigger so the nightly verification can dispatch it in the split repository
get_all_packages() {
    echo "administration \
        biome-config \
        framework \
        frontend-api \
        cli \
        google-cloud-bundle \
        s3-bridge \
        category-feed-luigis-box \
        product-feed-zbozi \
        product-feed-google \
        product-feed-mergado \
        product-feed-heureka \
        product-feed-heureka-delivery \
        product-feed-luigis-box \
        article-feed-luigis-box \
        brand-feed-luigis-box \
        plugin-interface \
        coding-standards \
        http-smoke-testing \
        form-types-bundle \
        mcp \
        mcp-attributes \
        maker \
        migrations \
        monorepo-tools \
        php-image \
        luigis-box \
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

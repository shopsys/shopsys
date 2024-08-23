#!/bin/bash

set -e -o pipefail

SPLIT_BRANCH=$1
REMOTE_TEMPLATE=$2
FORCE=${3:-false}

set -u

# Import functions
. $(dirname "$0")/monorepo_functions.sh

assert_split_branch_variable
assert_remote_template_variable

echo -e "${BLUE}Splitting branch '$SPLIT_BRANCH'...${NC}"

WORKSPACE=`pwd`

if [[ "$FORCE" == true ]]; then
    PUSH_OPTS="--force"
else
    PUSH_OPTS="--tags"
fi

for PACKAGE in $(get_all_packages); do
    cd ${WORKSPACE}

    echo -e "${BLUE}Start processing ${GREEN}\"${PACKAGE}\"${NC}"

    mkdir -p ${WORKSPACE}/split/${PACKAGE}
    git clone .git ${WORKSPACE}/split/${PACKAGE}
    cd ${WORKSPACE}/split/${PACKAGE}

    echo -e "${BLUE}Rewriting history of ${GREEN}\"${PACKAGE}\"${NC}"
    git filter-repo --subdirectory-filter $(get_package_subdirectory "$PACKAGE")

    if [[ "$FORCE" == true ]]; then
        if [[ "$PACKAGE" == "project-base" ]]; then
            COMPOSER_JSON_FILE="app/composer.json"
        else
            COMPOSER_JSON_FILE="composer.json"
        fi

        if [ -f "$COMPOSER_JSON_FILE" ]; then
            sed -r -i 's_("shopsys/[a-zA-Z0-9-]+")\s*:\s*"([0-9\.]+\.x-dev)"_\1: "dev-'"${SPLIT_BRANCH}"' as \2"_' ${COMPOSER_JSON_FILE}
            git config --global user.name 'ShopsysBot'
            git config --global user.email 'shopsysbot@users.noreply.github.com'
            if ! git diff --quiet; then
                git commit -am "Ensure ${SPLIT_BRANCH} branch dependencies in composer.json"
            fi
        fi
    fi

    echo -e "${BLUE}Check if branch ${GREEN}\"${SPLIT_BRANCH}\" ${BLUE}can be pushed to remote package ${GREEN}\"${PACKAGE}\"${NC}"
    git push "${REMOTE_TEMPLATE}${PACKAGE}.git" ${SPLIT_BRANCH} --dry-run ${PUSH_OPTS} --verbose
done

echo -e "${BLUE}Pushing to remotes${NC}"
for PACKAGE in $(get_all_packages); do
    echo -e "${BLUE}Push ${GREEN}\"${PACKAGE}\"${NC}"
    cd ${WORKSPACE}/split/${PACKAGE}
     git push "${REMOTE_TEMPLATE}${PACKAGE}.git" ${SPLIT_BRANCH} ${PUSH_OPTS} --verbose
done

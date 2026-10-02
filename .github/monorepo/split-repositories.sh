#!/bin/bash

set -e -o pipefail

SPLIT_BRANCH=$1
REMOTE_TEMPLATE=$2
FORCE=${3:-false}
# optional file where every pushed package is recorded as "<package> <commit sha>" for wait-for-split-checks.sh
SPLIT_HEADS_FILE=${SPLIT_HEADS_FILE:-}

set -u

# Import functions
. $(dirname "$0")/monorepo_functions.sh

assert_split_branch_variable
assert_split_branch_is_valid_ref
assert_remote_template_variable

if [[ "$FORCE" == true ]]; then
    assert_split_branch_is_not_protected
fi

echo -e "${BLUE}Splitting branch '$SPLIT_BRANCH'...${NC}"

WORKSPACE=`pwd`

if [[ -n "$SPLIT_HEADS_FILE" ]]; then
    : > "$SPLIT_HEADS_FILE"
fi

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
            # "~" as the delimiter cannot occur in a branch name, so the branch can never terminate the expression early
            sed -r -i 's~("shopsys/[a-zA-Z0-9-]+")\s*:\s*"([0-9\.]+\.x-dev)"~\1: "dev-'"$(escape_for_sed_replacement "${SPLIT_BRANCH}")"' as \2"~' ${COMPOSER_JSON_FILE}
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

    if [[ -n "$SPLIT_HEADS_FILE" ]]; then
        echo "${PACKAGE} $(git rev-parse "${SPLIT_BRANCH}")" >> "$SPLIT_HEADS_FILE"
    fi
done

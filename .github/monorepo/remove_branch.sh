#!/bin/bash

set -e -o pipefail

SPLIT_BRANCH=$1
REMOTE_TEMPLATE=$2

set -u

# Import functions
. $(dirname "$0")/monorepo_functions.sh

assert_split_branch_variable
assert_remote_template_variable

assert_split_branch_is_not_protected

echo -e "${BLUE}Removing branch '$SPLIT_BRANCH'...${NC}"

for PACKAGE in $(get_all_packages); do
    if git push --delete "${REMOTE_TEMPLATE}${PACKAGE}.git" ${SPLIT_BRANCH}; then
        echo -e "${GREEN}Branch \"${SPLIT_BRANCH}\" was removed from the package ${GREEN}\"${PACKAGE}\"${NC}"
    else
        echo -e "${BLUE}Branch \"${SPLIT_BRANCH}\" does not exist on the package \"${PACKAGE}\"!${NC}"

    fi
done

echo -e "${GREEN}Branches from all repositories are removed!${NC}"

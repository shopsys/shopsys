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

EXIT_STATUS=0
for PACKAGE in $(get_all_packages); do

    if git push --delete "${REMOTE_TEMPLATE}${PACKAGE}.git" ${SPLIT_BRANCH}; then
        echo -e "${BLUE}Branch ${GREEN}\"${SPLIT_BRANCH}\" ${BLUE}was removed from the package ${GREEN}\"${PACKAGE}\"${NC}"
    else
        echo -e "${RED}Branch \"${SPLIT_BRANCH}\" could not be removed from the package \"${PACKAGE}\"!${NC}"
        EXIT_STATUS=1
    fi
done

if [[ $EXIT_STATUS -eq 0 ]]; then
    echo -e "${GREEN}Branches from all repositories were removed!${NC}"
else
    echo -e "${RED}Some branches were not removed from their remotes due to an error${NC}"
fi

exit $EXIT_STATUS

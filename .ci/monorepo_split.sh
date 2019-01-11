#!/usr/bin/env bash

# Split master branches of all packages of the monorepo in parallel and push it into master branches in corresponding repositories.
#
# Logs of history rewriting can be found in $WORKSPACE/split/ along with the individual bare repositories.
#
# Usage: monorepo_split.sh

# ANSI color codes
RED="\e[31m"
GREEN="\e[32m"
BLUE="\e[34m"
NC="\e[0m"

WORKSPACE=${WORKSPACE:-.}

# Relatively new version of git must be installed
printf "\n${BLUE}Using $(git --version). The package shopsys/monorepo-tools was tested on 2.16.1.${NC}\n\n"

# Import functions
. $(dirname "$0")/monorepo_functions.sh

for PACKAGE in $(get_all_packages); do
    # Preparing the variables to be used for splitting
    LOG_FILE="$WORKSPACE/split/$PACKAGE.log"
    SUBDIRECTORY=$(get_package_subdirectory "$PACKAGE")

    # Cloning the repository as bare into separate directory, so it can be split in a parallel process
    cd $WORKSPACE
    git clone --bare .git $WORKSPACE/split/$PACKAGE
    cd $WORKSPACE/split/$PACKAGE

    printf "${BLUE}$(date +%T) > Splitting package '$PACKAGE' from directory '$SUBDIRECTORY'...${NC}\n"
    printf "The progress will be logged into '$LOG_FILE'.\n\n"

    # Running the splitting processes in parallel
    $WORKSPACE/packages/monorepo-tools/rewrite_history_from.sh $SUBDIRECTORY master $(git tag) > $LOG_FILE 2>&1 &&
        printf "${GREEN}$(date +%T) > Splitting package '$PACKAGE' from directory '$SUBDIRECTORY' finished!${NC}\n" ||
        printf "${RED}$(date +%T) > Splitting package '$PACKAGE' from directory '$SUBDIRECTORY' failed!${NC}\n" &
done

wait

# Checking the status of the split repositories
EXIT_STATUS=0
printf "\n${BLUE}$(date +%T) > Splitting of all packages finished. Checking the ability to push the split repositories...${NC}\n\n"
for PACKAGE in $(get_all_packages); do
    cd $WORKSPACE/split/$PACKAGE
    REMOTE="git@github.com:shopsys/$PACKAGE.git"

    git push --tags $REMOTE master --dry-run

    if [[ $? -eq 0 ]]; then
        printf "${GREEN}The split package '$PACKAGE' can be pushed into '$REMOTE'!${NC}\n"
    else
        printf "${RED}The split package '$PACKAGE' cannot be pushed into '$REMOTE'!${NC}\n"
        EXIT_STATUS=1
    fi
done

# Pushing all repositories at once if they are OK so they are released at one moment
if [[ $EXIT_STATUS -eq 0 ]]; then
    printf "\n${GREEN}$(date +%T) > All repositories can be pushed! Pushing them into their remotes now...${NC}\n\n"
    for PACKAGE in $(get_all_packages); do
        cd $WORKSPACE/split/$PACKAGE
        git push --tags git@github.com:shopsys/$PACKAGE.git master
    done
else
    printf "\n${RED}$(date +%T) > Repositories were not pushed into their remotes due to an error.${NC}\n\n"
fi

exit $EXIT_STATUS

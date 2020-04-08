#!/bin/bash

INSTALL_DIR="$( cd "$(dirname "$0")" ; pwd -P )"

PWD_PROJECT_BASE_PATH=`[ -d "$PWD/project-base" ] && echo "$PWD/project-base" || echo "$PWD"`

# ANSI color codes
RED="\e[31m"
GREEN="\e[32m"
ORANGE="\e[38;5;137m"
GREY="\e[90m"
NC="\e[0m"

if [ "$1" == "monorepo" ]
then
    PROJECT_BASE_PATH=`realpath ${INSTALL_DIR}/../../../project-base`
    if [ "$PWD_PROJECT_BASE_PATH" != "$PROJECT_BASE_PATH" ]
    then
        printf "${RED}You have to run install.sh from monorepo root. Eg. /var/www/html${NC}\n"
        exit 1
    fi
else
    PROJECT_BASE_PATH=`realpath ${INSTALL_DIR}/../../../../`
    if [ "$PWD_PROJECT_BASE_PATH" != "$PROJECT_BASE_PATH" ]
    then
        printf "${RED}You have to run uninstall.sh from the project root. Eg. /var/www/html{$NC}\n"
        exit 1
    fi
fi

rm -f ${PROJECT_BASE_PATH}/config/packages/fos_rest.yml
rm -f ${PROJECT_BASE_PATH}/config/packages/test/fos_rest.yml

rm -f ${PROJECT_BASE_PATH}/config/packages/trikoder_oauth2.yml
rm -rf ${PROJECT_BASE_PATH}/config/oauth2

rm -rf ${PROJECT_BASE_PATH}/src/Controller/Api

rm -f ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiTest.php
rm -f ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiCreateProductTest.php
rm -f ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiDeleteProductTest.php
rm -f ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiUpdateProductTest.php
rm -f ${PROJECT_BASE_PATH}/tests/App/Test/OauthTestCase.php

function apply_patch_reverse () {
    if [ -z $1 ]
    then
        printf "${RED}Please provide path as first parameter of apply_patch_reverse function${NC}\n"
        exit 1
    fi

    local FILE_PATH=$1
    local SOURCE_FILE_PATH=$1

    if [ ! -z $2 ]
    then
        SOURCE_FILE_PATH=$2
    fi

    echo "Reverting ${FILE_PATH}..."
    local PATCH_DRY_RUN=`patch -Rf --dry-run ${PROJECT_BASE_PATH}/${FILE_PATH} ${INSTALL_DIR}/${SOURCE_FILE_PATH}.patch`
    local PATCH_WARNING=`grep "Hunk #" <<< ${PATCH_DRY_RUN}`
    local PATCH_FAIL=`grep "FAILED" <<< ${PATCH_DRY_RUN}`

    if [ ! -z "${PATCH_FAIL}" ]
    then
        local PATCH_APPLY_DRY_RUN=`patch -t --dry-run ${PROJECT_BASE_PATH}/${FILE_PATH} ${INSTALL_DIR}/${SOURCE_FILE_PATH}.patch`
        if [ -z "${PATCH_APPLY_DRY_RUN}" ]
        then
            printf "${ORANGE}Applied patch detected: ${FILE_PATH} already reverted, Doing nothing${NC}\n"
        else
            echo ${PATCH_FAIL}
            printf "${RED}${FILE_PATH} cannot be reverted!${NC}\n"
            AT_LEAST_ONE_PATCH_FAILED=1
        fi
    elif [ ! -z "${PATCH_WARNING}" ]
    then
        printf "${RED}Patch can be reverted for ${FILE_PATH} however patching did not have perfect match!${NC}\n"
        printf "Verify patchfile and apply manually with ${GREY}'patch -Rf ${PROJECT_BASE_PATH}/${FILE_PATH} ${INSTALL_DIR}/${SOURCE_FILE_PATH}.patch'${NC}\n"
        AT_LEAST_ONE_PATCH_FAILED=1
    else
        patch -Rf ${PROJECT_BASE_PATH}/${FILE_PATH} ${INSTALL_DIR}/${SOURCE_FILE_PATH}.patch
        printf "${GREEN}Done${NC}\n"
    fi

    printf "\n"
}

apply_patch_reverse "src/Kernel.php"
apply_patch_reverse "config/parameters_common.yml"
rm -r ${PROJECT_BASE_PATH}/config/routes/backend-api.yml
apply_patch_reverse "config/packages/security.yml"
apply_patch_reverse "config/bundles.php"
apply_patch_reverse "build.xml"

if [ "$1" == "monorepo" ]
then
    printf "${GREY}Running from monorepo, not applying patch for project-base composer.json because it has no effect in monorepo.${NC}\n"
else
    apply_patch_reverse "composer.json"
fi

if [ -z $AT_LEAST_ONE_PATCH_FAILED ]
then
    printf "${GREEN}Backend API uninstallation was successful!${NC}\n"
    exit 0
else
    printf "${RED}Backend API uninstallation failed!${NC}\n"
    exit 1
fi

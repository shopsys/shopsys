#!/bin/bash -e

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
        printf "${RED}You have to run install.sh from the project root. Eg. /var/www/html{$NC}\n"
        exit 1
    fi
fi

printf "Copying FOS REST configuration..."
cp ${INSTALL_DIR}/config/packages/fos_rest.yaml ${PROJECT_BASE_PATH}/config/packages/fos_rest.yaml
cp ${INSTALL_DIR}/config/packages/test/fos_rest.yaml ${PROJECT_BASE_PATH}/config/packages/test/fos_rest.yaml
printf "${GREEN}Done${NC}\n"

printf "Copying OAuth2 configuration..."
cp ${INSTALL_DIR}/config/packages/trikoder_oauth2.yaml ${PROJECT_BASE_PATH}/config/packages/trikoder_oauth2.yaml
mkdir -p ${PROJECT_BASE_PATH}/config/oauth2
cp ${INSTALL_DIR}/config/oauth2/.gitignore ${PROJECT_BASE_PATH}/config/oauth2/.gitignore
cp ${INSTALL_DIR}/config/oauth2/parameters_oauth.yaml.dist ${PROJECT_BASE_PATH}/config/oauth2/parameters_oauth.yaml.dist
printf "${GREEN}Done${NC}\n"

printf "Creating directory src/Controller/Api/V1..."
mkdir -p ${PROJECT_BASE_PATH}/src/Controller/Api/V1/Product
touch ${PROJECT_BASE_PATH}/src/Controller/Api/V1/Product/.gitkeep
printf "${GREEN}Done${NC}\n"

printf "Copying tests..."
cp ${INSTALL_DIR}/tests/App/Smoke/BackendApiTest.php ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiTest.php
cp ${INSTALL_DIR}/tests/App/Smoke/BackendApiCreateProductTest.php ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiCreateProductTest.php
cp ${INSTALL_DIR}/tests/App/Smoke/BackendApiDeleteProductTest.php ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiDeleteProductTest.php
cp ${INSTALL_DIR}/tests/App/Smoke/BackendApiUpdateProductTest.php ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiUpdateProductTest.php
cp ${INSTALL_DIR}/tests/App/Test/OauthTestCase.php ${PROJECT_BASE_PATH}/tests/App/Test/OauthTestCase.php
printf "${GREEN}Done${NC}\n"

printf "\n"

function apply_patch () {
    if [ -z $1 ]
    then
        printf "${RED}Please provide path as first parameter of apply_patch function${NC}\n"
        exit 1
    fi

    local FILE_PATH=$1
    local SOURCE_FILE_PATH=$1

    if [ ! -z $2 ]
    then
        SOURCE_FILE_PATH=$2
    fi

    echo "Applying patch for ${FILE_PATH}..."
    local PATCH_DRY_RUN=`patch -t --dry-run ${PROJECT_BASE_PATH}/${FILE_PATH} ${INSTALL_DIR}/${SOURCE_FILE_PATH}.patch`
    local PATCH_REVERSED=`grep "Reversed" <<< ${PATCH_DRY_RUN}`
    local PATCH_WARNING=`grep "Hunk #" <<< ${PATCH_DRY_RUN}`
    local PATCH_FAIL=`grep "FAILED" <<< ${PATCH_DRY_RUN}`

    if [ ! -z "${PATCH_REVERSED}" ]
    then
        printf "${ORANGE}Applied patch detected: patch for ${FILE_PATH} already applied. Doing nothing${NC}\n"
    elif [ ! -z "${PATCH_FAIL}" ]
    then
        echo ${PATCH_FAIL}
        printf "${RED}Patch for ${FILE_PATH} cannot be applied!${NC}\n"
        AT_LEAST_ONE_PATCH_FAILED=1
    elif [ ! -z "${PATCH_WARNING}" ]
    then
        printf "${RED}Patch can be applied for ${FILE_PATH} however patching did not have perfect match!${NC}\n"
        printf "Verify patchfile and apply manually with ${GREY}'patch -t ${PROJECT_BASE_PATH}/${FILE_PATH} ${INSTALL_DIR}/${SOURCE_FILE_PATH}.patch'${NC}\n"
        AT_LEAST_ONE_PATCH_FAILED=1
    else
        patch -t ${PROJECT_BASE_PATH}/${FILE_PATH} ${INSTALL_DIR}/${SOURCE_FILE_PATH}.patch
        printf "${GREEN}Done${NC}\n"
    fi

    printf "\n"
}

apply_patch "src/Kernel.php"
apply_patch "config/parameters_common.yaml"
cp ${INSTALL_DIR}/config/routes/backend-api.yaml ${PROJECT_BASE_PATH}/config/routes/backend-api.yaml
apply_patch "config/packages/security.yaml"
apply_patch "config/bundles.php"
apply_patch "build.xml"

if [ "$1" == "monorepo" ]
then
    printf "${GREY}Running from monorepo, not applying patch for project-base composer.json because it has no effect in monorepo.${NC}\n"
else
    apply_patch "composer.json"
fi

if [ -z $AT_LEAST_ONE_PATCH_FAILED ]
then
    printf "${GREEN}Backend API installation was successful!${NC}\n"
    exit 0
else
    printf "${RED}Backend API installation failed!${NC}\n"
    exit 1
fi

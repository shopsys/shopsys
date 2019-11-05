#!/bin/bash -e

INSTALL_DIR="$( cd "$(dirname "$0")" ; pwd -P )"

PWD_PROJECT_BASE_PATH=`[ -d "$PWD/project-base" ] && echo "$PWD/project-base" || echo "$PWD"`

if [ "$1" == "monorepo" ]
then
    PROJECT_BASE_PATH=`realpath ${INSTALL_DIR}/../../../project-base`
    if [ "$PWD_PROJECT_BASE_PATH" != "$PROJECT_BASE_PATH" ]
    then
        echo "You have to run install.sh from monorepo root. Eg. /var/www/html"
        exit 1
    fi
else
    PROJECT_BASE_PATH=`realpath ${INSTALL_DIR}/../../../../`
    if [ "$PWD_PROJECT_BASE_PATH" != "$PROJECT_BASE_PATH" ]
    then
        echo "You have to run install.sh from the project root. Eg. /var/www/html"
        exit 1
    fi
fi

echo "Copying FOS REST configuration..."
cp ${INSTALL_DIR}/config/packages/fos_rest.yml ${PROJECT_BASE_PATH}/config/packages/fos_rest.yml
cp ${INSTALL_DIR}/config/packages/test/fos_rest.yml ${PROJECT_BASE_PATH}/config/packages/test/fos_rest.yml
echo "Done"

echo "Copying OAuth2 configuration..."
cp ${INSTALL_DIR}/config/packages/trikoder_oauth2.yml ${PROJECT_BASE_PATH}/config/packages/trikoder_oauth2.yml
mkdir -p ${PROJECT_BASE_PATH}/config/oauth2
cp ${INSTALL_DIR}/config/oauth2/.gitignore ${PROJECT_BASE_PATH}/config/oauth2/.gitignore
cp ${INSTALL_DIR}/config/oauth2/parameters_oauth.yml.dist ${PROJECT_BASE_PATH}/config/oauth2/parameters_oauth.yml.dist
echo "Done"

echo "Creating directory src/Controller/Api/V1..."
mkdir -p ${PROJECT_BASE_PATH}/src/Controller/Api/V1/Product
touch ${PROJECT_BASE_PATH}/src/Controller/Api/V1/Product/.gitkeep
echo "Done"

echo "Copying tests..."
cp ${INSTALL_DIR}/tests/App/Smoke/BackendApiTest.php ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiTest.php
cp ${INSTALL_DIR}/tests/App/Smoke/BackendApiCreateProductTest.php ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiCreateProductTest.php
cp ${INSTALL_DIR}/tests/App/Smoke/BackendApiDeleteProductTest.php ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiDeleteProductTest.php
cp ${INSTALL_DIR}/tests/App/Smoke/BackendApiUpdateProductTest.php ${PROJECT_BASE_PATH}/tests/App/Smoke/BackendApiUpdateProductTest.php
cp ${INSTALL_DIR}/tests/App/Test/OauthTestCase.php ${PROJECT_BASE_PATH}/tests/App/Test/OauthTestCase.php
echo "Done"

function apply_patch () {
    if [ -z $1 ]
    then
        echo "Please provide path as first parameter of apply_patch function"
        exit 1
    fi

    local FILE_PATH=$1
    local SOURCE_FILE_PATH=$1

    if [ ! -z $2 ]
    then
        SOURCE_FILE_PATH=$2
    fi

    echo "Applying patch for ${FILE_PATH}..."
    local PATCH_DRY_RUN=`patch -st --dry-run ${PROJECT_BASE_PATH}/${FILE_PATH} ${INSTALL_DIR}/${SOURCE_FILE_PATH}.patch`
    local PATCH_REVERSED=`grep "Reversed" <<< ${PATCH_DRY_RUN}`
    local PATCH_FAIL=`grep "FAILED" <<< ${PATCH_DRY_RUN}`

    if [ ! -z "${PATCH_REVERSED}" ]
    then
        echo "Applied patch detected: patch for ${FILE_PATH} already applied. Doing nothing"
    elif [ ! -z "${PATCH_FAIL}" ]
    then
        echo ${PATCH_FAIL}
        echo "Patch for ${FILE_PATH} cannot be applied!"
        AT_LEAST_ONE_PATCH_FAILED=1
    else
        patch -st ${PROJECT_BASE_PATH}/${FILE_PATH} ${INSTALL_DIR}/${SOURCE_FILE_PATH}.patch
        echo "Done"
    fi
}

apply_patch "src/Kernel.php"
apply_patch "config/parameters_common.yml"
cp ${INSTALL_DIR}/config/routes/backend-api.yml ${PROJECT_BASE_PATH}/config/routes/backend-api.yml
apply_patch "config/packages/security.yml"
apply_patch "config/bundles.php"
apply_patch "build.xml"

if [ "$1" == "monorepo" ]
then
    echo "Running from monorepo, not applying patch for project-base composer.json because it has no effect in monorepo."
else
    apply_patch "composer.json"
fi

if [ -z $AT_LEAST_ONE_PATCH_FAILED ]
then
    echo "Backend API installation was successful!"
    exit 0
else
    echo "Backend API installation failed!"
    exit 1
fi

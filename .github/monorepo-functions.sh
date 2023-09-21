#!/usr/bin/env bash

# Gets a subdirectory in which a package is located
get_package_subdirectory() {
    PACKAGE=$1

    if [[ "$PACKAGE" == "project-base" ]]; then
        echo $PACKAGE
    else
        echo "packages/$PACKAGE"
    fi
}

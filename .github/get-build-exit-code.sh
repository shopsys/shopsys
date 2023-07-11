#!/bin/sh

getBuildExitCodeBasedOnJobResults() {
    BUILD_FORK_RESULT=$1
    STANDARDS_RESULT=$2
    TESTS_RESULT=$3
    TESTS_ACCEPTANCE_RESULT=$4
    STANDARDS_STOREFRONT_RESULT=$5
    REVIEW_RESULT=$6

    if [[ "$BUILD_FORK_RESULT" == "success" ]]; then
        return 0
    fi

    if [[ "$STANDARDS_RESULT" == "success" && "$TESTS_RESULT" == "success" && "$TESTS_ACCEPTANCE_RESULT" == "success" && "$STANDARDS_STOREFRONT_RESULT" == "success" && "$REVIEW_RESULT" == "success" ]]; then
        return 0
    else
        return 1
    fi
}

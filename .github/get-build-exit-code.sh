#!/bin/sh

getBuildExitCodeBasedOnJobResults() {
    BUILD_FORK_RESULT=$1
    STANDARDS_RESULT=$2
    TESTS_RESULT=$3
    TESTS_ACCEPTANCE_RESULT=$4

    if [[ "$BUILD_FORK_RESULT" == "success" ]]; then
        return 0
    fi

    if [[ "$STANDARDS_RESULT" == "success" && "$TESTS_RESULT" == "success" && "$TESTS_ACCEPTANCE_RESULT" == "success" ]]; then
        return 0
    else
        return 1
    fi
}

#!/bin/sh

getBuildExitCodeBasedOnJobResults() {
    BUILD_FORK_RESULT=${1}
    STANDARDS_RESULT=${2}
    TESTS_RESULT=${3}
    TESTS_ACCEPTANCE_RESULT=${4}
    STANDARDS_STOREFRONT_RESULT=${5}
    TRANSLATIONS_DUMP_RESULT=${6}
    REVIEW_RESULT=${7}
    CHECK_CONSOLE_COMMANDS_RESULT=${8}
    TESTS_STOREFRONT_ACCEPTANCE_RESULT=${9}
    UNIT_TESTS_STOREFRONT_RESULT=${10}

    if [[ "$BUILD_FORK_RESULT" == "success" ]]; then
        return 0
    fi

    if [[ "$STANDARDS_RESULT" == "success" && "$TESTS_RESULT" == "success" && "$TESTS_ACCEPTANCE_RESULT" == "success" && "$STANDARDS_STOREFRONT_RESULT" == "success" && "$TRANSLATIONS_DUMP_RESULT" == "success" && "$REVIEW_RESULT" == "success" && "$CHECK_CONSOLE_COMMANDS_RESULT" == "success" && "$TESTS_STOREFRONT_ACCEPTANCE_RESULT" == "success" && "$UNIT_TESTS_STOREFRONT_RESULT" == "success" ]]; then
        return 0
    else
        return 1
    fi
}

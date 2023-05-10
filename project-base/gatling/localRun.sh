#!/bin/bash -e

GATLING_BASE_URL="http://localhost:8000/"
GATLING_RESULTS="$(pwd)/var/gatlingResults"
GATLING_PAGE_TEST_DURATION=10
GATLING_STRESS_TEST_USERS_COUNTS=50
GATLING_STRESS_TEST_DURATION=300
GATLING_PAGE_USERS_COUNTS_LVL1=10
GATLING_PAGE_USERS_COUNTS_LVL2=20
GATLING_PAGE_USERS_COUNTS_LVL3=50

source "$(pwd)/run.sh"

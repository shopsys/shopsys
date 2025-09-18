#!/bin/bash -e

# Progressive load testing simulations - matches K6 pattern
HOMEPAGE_TESTS=(
  "PageHomepage10to20"
  "PageHomepage25to50"
  "PageHomepage50to100"
  "PageHomepage75to150"
  "PageHomepage100to200"
)

authLoginName=${AUTH_LOGIN_NAME:-""}
authPassword=${AUTH_PASSWORD:-""}

SUMMARY_DIR="_summary"
SUMMARY_PATH="results/${SUMMARY_DIR}"
SUMMARY_LOG="${SUMMARY_PATH}/results.log"

echo $SUMMARY_PATH
mkdir -p $SUMMARY_PATH
touch $SUMMARY_LOG

echo "Starting progressive homepage load testing..."
echo "Running 5 tests with K6-style load patterns:"
echo "1. 10→20→0 users"
echo "2. 25→50→0 users"
echo "3. 50→100→0 users"
echo "4. 75→150→0 users"
echo "5. 100→200→0 users"
echo ""

for TEST_NAME in "${HOMEPAGE_TESTS[@]}"; do
  echo "Running ${TEST_NAME} simulation..."
  OUTPUT=$(docker run --rm \
    -e JAVA_OPTS="-DbaseUrl=${GATLING_BASE_URL} -DauthLoginName=${authLoginName} -DauthPassword=${authPassword}" \
    -v "$(pwd)/gatling:/opt/gatling/user-files" \
    -v "$(pwd)/results:/opt/gatling/results" \
    denvazh/gatling -s performance.${TEST_NAME}
  )
  RESULT_PATH=$(echo $OUTPUT | sed -E "s/.*Please open the following file: \/opt\/gatling\/results\/(.*)\/index\.html/\1/")
  echo "✅ ${TEST_NAME} completed - Result stored in ${RESULT_PATH}"
  echo $RESULT_PATH >> $SUMMARY_LOG
  echo ""
done

echo "Generating summary..."

docker run --rm \
  -v "$(pwd):/app" \
  -v "$(pwd)/results:/gatlingResults" \
  -e SUMMARY_DIR="${SUMMARY_DIR}" \
  -w /app \
  php:7.4-cli \
  php makeSummary.php

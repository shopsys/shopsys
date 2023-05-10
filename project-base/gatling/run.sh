#!/bin/bash -e

USERS_COUNTS=(
  1
  $GATLING_PAGE_USERS_COUNTS_LVL1
  $GATLING_PAGE_USERS_COUNTS_LVL2
  $GATLING_PAGE_USERS_COUNTS_LVL3
)

authLoginName=${AUTH_LOGIN_NAME:-""}
authPassword=${AUTH_PASSWORD:-""}

SUMMARY_DIR="_summary"
SUMMARY_PATH="results/${SUMMARY_DIR}"
SUMMARY_LOG="${SUMMARY_PATH}/results.log"
STRESS_LOG="${SUMMARY_PATH}/stress.log"

echo $SUMMARY_PATH
mkdir -p $SUMMARY_PATH
touch $SUMMARY_LOG
touch $STRESS_LOG

for users in "${USERS_COUNTS[@]}"; do
  for filename in ./gatling/simulations/Page*.scala; do
      TEST_NAME=$(basename $filename .scala)
      echo "Running ${TEST_NAME} simulation with ${users} users for ${GATLING_PAGE_TEST_DURATION} seconds..."
      OUTPUT=$(docker run --rm \
        -e JAVA_OPTS="-DbaseUrl=${GATLING_BASE_URL} -DauthLoginName=${authLoginName} -DauthPassword=${authPassword} -Dusers=${users} -Dduration=${GATLING_PAGE_TEST_DURATION}" \
        -v "$(pwd)/gatling:/opt/gatling/user-files" \
        -v "$(pwd)/results:/opt/gatling/results" \
        denvazh/gatling -s performance.${TEST_NAME}
      )
      RESULT_PATH=$(echo $OUTPUT | sed -E "s/.*Please open the following file: \/opt\/gatling\/results\/(.*)\/index\.html/\1/")
      echo "Result is stored in ${RESULT_PATH}"
      echo $RESULT_PATH >> $SUMMARY_LOG
    done
  for filename in ./gatling/simulations/graphql/*.scala; do
      TEST_NAME=$(basename $filename .scala)
      echo "Running ${TEST_NAME} simulation with ${users} users for ${GATLING_API_TEST_DURATION} seconds..."
      OUTPUT=$(docker run --rm \
        -e JAVA_OPTS="-DbaseUrl=${GATLING_BASE_URL} -DauthLoginName=${authLoginName} -DauthPassword=${authPassword} -Dusers=${users} -Dduration=${GATLING_API_TEST_DURATION}" \
        -v "$(pwd)/gatling:/opt/gatling/user-files" \
        -v "$(pwd)/results:/opt/gatling/results" \
        denvazh/gatling -s performance.${TEST_NAME}
      )
      RESULT_PATH=$(echo $OUTPUT | sed -E "s/.*Please open the following file: \/opt\/gatling\/results\/(.*)\/index\.html/\1/")
      echo "Result is stored in ${RESULT_PATH}"
      echo $RESULT_PATH >> $SUMMARY_LOG
    done
done

echo "Running stress simulation with ${GATLING_STRESS_TEST_USERS_COUNTS} users for ${GATLING_STRESS_TEST_DURATION} seconds..."
      OUTPUT=$(docker run --rm \
        -e JAVA_OPTS="-DbaseUrl=${GATLING_BASE_URL} -DauthLoginName=${authLoginName} -DauthPassword=${authPassword} -Dusers=${GATLING_STRESS_TEST_USERS_COUNTS} -Dduration=${GATLING_STRESS_TEST_DURATION}" \
        -v "$(pwd)/gatling:/opt/gatling/user-files" \
        -v "$(pwd)/results:/opt/gatling/results" \
        denvazh/gatling -s performance.Stress
      )
      RESULT_PATH=$(echo $OUTPUT | sed -E "s/.*Please open the following file: \/opt\/gatling\/results\/(.*)\/index\.html/\1/")
      echo "Result is stored in ${RESULT_PATH}"
      echo $RESULT_PATH >> $STRESS_LOG

echo "Generating summary..."

docker run --rm \
  -v "$(pwd):/app" \
  -v "$(pwd)/results:/gatlingResults" \
  -e SUMMARY_DIR="${SUMMARY_DIR}" \
  -w /app \
  php:7.4-cli \
  php makeSummary.php

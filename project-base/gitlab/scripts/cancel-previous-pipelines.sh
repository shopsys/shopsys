#!/bin/bash -e

API_URL="${CI_API_V4_URL}/projects/${CI_PROJECT_ID}"
MERGE_REQUEST_ID=${CI_MERGE_REQUEST_IID}

if [ -z "${MERGE_REQUEST_ID}" ]
then
    echo "Merge request is not created"
else
    PIPELINES="$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/merge_requests/${MERGE_REQUEST_ID}/pipelines" | jq '.[] | {id, status} | select(.status | contains("running")) | .id')"
    PIPELINES=(${PIPELINES}) # Transform to array

    for RUNNING_PIPELINE_ID in ${PIPELINES[@]:1}; do # Remove newest pipeline
        CURL_OUTPUT=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" -X POST "${API_URL}/pipelines/${RUNNING_PIPELINE_ID}/cancel")
        echo -e "Previous pipeline (${RUNNING_PIPELINE_ID}) has been canceled"
    done
fi

#!/bin/bash -e

## required environment variables
# JIRA_BASE_URL
# JIRA_ACCOUNT
# JIRA_TOKEN
# CI_MERGE_REQUEST_TITLE

MERGE_REQUEST_NAME=${CI_MERGE_REQUEST_TITLE}

JIRA_REVIEW_URL_FIELD='customfield_10032'

pattern="\[([^]]+)\]"  # pattern to match JIRA-ID in "[JIRA-ID] pull request title"

if [[ $MERGE_REQUEST_NAME =~ $pattern ]]; then
  JIRA_ISSUE_ID="${BASH_REMATCH[1]}"
else
  echo "Jira issue number is not set in merge request title [${MERGE_REQUEST_NAME}]"
  exit 0
fi

REVIEW_URL="$(curl -L --silent -u ${JIRA_ACCOUNT}:${JIRA_TOKEN} -X GET -H "Content-Type: application/json" ${JIRA_BASE_URL}issue/${JIRA_ISSUE_ID}?fields=${JIRA_REVIEW_URL_FIELD} | jq --raw-output '.fields.customfield_10032')"

if [ "${REVIEW_URL}" == "null" ];
then
    UPDATE_DATA="{\"fields\": {\"${JIRA_REVIEW_URL_FIELD}\":\"https://${HOST}\"}}"

    curl -u ${JIRA_ACCOUNT}:${JIRA_TOKEN} -X PUT -H "Content-Type: application/json" ${JIRA_BASE_URL}issue/${JIRA_ISSUE_ID} --data "${UPDATE_DATA}"
    echo "Setting Review URL to Jira issue [${JIRA_ISSUE_ID}]"
else
    echo "Review URL is already set in Jira issue [${JIRA_ISSUE_ID}]"
fi

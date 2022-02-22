#!/bin/bash -e

MERGE_REQUEST_NAME=${CI_MERGE_REQUEST_TITLE}

JIRA_BASE_URL='https://shopsys.atlassian.net/rest/api/3/'
JIRA_REVIEW_URL_FIELD='customfield_10032'

JIRA_ISSUE_ID=${MERGE_REQUEST_NAME#*[} # Remove characters before [
JIRA_ISSUE_ID=${JIRA_ISSUE_ID%]*} # Remove characters after ]

REVIEW_URL="$(curl -L --silent -u jirabot@shopsys.com:${JIRA_TOKEN} -X GET -H "Content-Type: application/json" ${JIRA_BASE_URL}issue/${JIRA_ISSUE_ID}?fields=${JIRA_REVIEW_URL_FIELD} | jq --raw-output '.fields.customfield_10032')"

if [ "${REVIEW_URL}" == "null" ];
then
    UPDATE_DATA="{\"fields\": {\"${JIRA_REVIEW_URL_FIELD}\":\"https://${HOST}\"}}"

    curl -u jirabot@shopsys.com:${JIRA_TOKEN} -X PUT -H "Content-Type: application/json" ${JIRA_BASE_URL}issue/${JIRA_ISSUE_ID} --data "${UPDATE_DATA}"
    echo "Setting Review URL to Jira issue [${JIRA_ISSUE_ID}]"
else
    echo "Review URL is already set in Jira issue [${JIRA_ISSUE_ID}]"
fi

#!/bin/bash

# Set here rather than only on the shebang, so the failure handling also holds when the script is run as
# "bash <path>" - which ignores the shebang and would otherwise let a failed request report success.
set -e

## required environment variables
# API_TOKEN
# CI_API_V4_URL
# CI_MERGE_REQUEST_PROJECT_ID
# CI_MERGE_REQUEST_IID
# CI_MERGE_REQUEST_DIFF_BASE_SHA
# CI_MERGE_REQUEST_TARGET_BRANCH_NAME

# Narrow wrapper for the AI security audit of a merge request. The agent reads diff text written by other
# people, so it must not be given a general purpose HTTP client - every request it can make goes to this
# project's own merge request and nowhere else.

STICKY_MARKER='<!-- claude-security-audit -->'
MERGE_REQUEST_URL="${CI_API_V4_URL}/projects/${CI_MERGE_REQUEST_PROJECT_ID}/merge_requests/${CI_MERGE_REQUEST_IID}"

callApi() {
    curl --silent --show-error --fail --header "PRIVATE-TOKEN: ${API_TOKEN}" "$@"
}

findStickyNoteId() {
    # Only a note this token itself posted counts - anyone can start a note with the marker, and the report must
    # never be written into somebody else's note.
    TOKEN_USER_ID="$(callApi "${CI_API_V4_URL}/user" | jq -r '.id')"

    callApi "${MERGE_REQUEST_URL}/notes?per_page=100" \
        | jq -r --arg marker "${STICKY_MARKER}" --argjson authorId "${TOKEN_USER_ID}" \
            '[.[] | select(.author.id == $authorId and (.body | startswith($marker)))][0].id // empty'
}

case "$1" in
    diff)
        # A real unified diff from git rather than the per-file fragments of the "/changes" API endpoint, because
        # the built-in /security-review baseline and the agent both reason about hunks with proper file headers.
        # The diff base is the merge base with the target branch - the same commit the merge request view shows.
        BASE_SHA="${CI_MERGE_REQUEST_DIFF_BASE_SHA}"

        if [ -z "${BASE_SHA}" ] || ! git cat-file -e "${BASE_SHA}^{commit}" 2> /dev/null; then
            BASE_SHA="$(git merge-base "origin/${CI_MERGE_REQUEST_TARGET_BRANCH_NAME}" HEAD)"
        fi

        git --no-pager diff --no-color "${BASE_SHA}" HEAD
        ;;
    report)
        REPORT_FILE="$2"

        if [ ! -f "${REPORT_FILE}" ]; then
            echo "Report file [${REPORT_FILE}] does not exist"
            exit 1
        fi

        if ! head -n 1 "${REPORT_FILE}" | grep -qF "${STICKY_MARKER}"; then
            echo "Report must start with the sticky marker so re-runs update one note instead of piling up"
            exit 1
        fi

        # The agent reads text other people wrote, so an injected instruction could try to copy a credential out of
        # its environment into this report. Refuse rather than post. This catches a verbatim copy, not an encoded
        # one, so it is the last line of defence and not the only one.
        # TEST_CANARY is a tripwire with no other purpose: a long random CI variable that exists only to be planted
        # into a report on a test branch, proving this refusal actually fires. Unset, it is skipped by the length check.
        for SECRET in "${API_TOKEN:-}" "${CLAUDE_CODE_OAUTH_TOKEN:-}" "${TEST_CANARY:-}"; do
            if [ ${#SECRET} -gt 8 ] && grep -qF -- "${SECRET}" "${REPORT_FILE}"; then
                echo "The report repeats a secret value verbatim, refusing to post it"
                exit 1
            fi
        done

        NOTE_ID="$(findStickyNoteId)"

        if [ -n "${NOTE_ID}" ]; then
            callApi -X PUT --form "body=<${REPORT_FILE}" "${MERGE_REQUEST_URL}/notes/${NOTE_ID}" > /dev/null
            echo "Updated the existing report note [${NOTE_ID}]"
        else
            callApi -X POST --form "body=<${REPORT_FILE}" "${MERGE_REQUEST_URL}/notes" > /dev/null
            echo "Created a new report note"
        fi
        ;;
    *)
        echo "Usage: $0 diff | report <file>"
        exit 1
        ;;
esac

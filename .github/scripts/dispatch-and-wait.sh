#!/bin/bash
# Dispatches a workflow in a target repository and waits for its conclusion.
#
# The script ALWAYS exits 0 — every outcome (including timeouts and dispatch
# failures) must reach the aggregation job as structured output; severity is
# decided there, not here.
#
# Inputs (env):
#   TARGET_REPO      - e.g. "shopsys/framework"
#   TARGET_WORKFLOW  - workflow file name, e.g. "run-checks-tests.yaml"
#   TARGET_REF       - branch to dispatch on, e.g. "20.0"
#   WAIT_TIMEOUT_MIN - soft deadline in minutes (the job's timeout-minutes must be higher)
#   GH_TOKEN         - token with Actions read/write on TARGET_REPO
#
# Outputs ($GITHUB_OUTPUT):
#   conclusion - success | failure | cancelled | superseded | timed_out
#                | not_dispatchable | dispatch_failed | correlation_failed
#   run-id     - correlated run databaseId (empty when correlation never happened)
#   run-url    - correlated run URL (empty when correlation never happened)

set -u

GITHUB_OUTPUT="${GITHUB_OUTPUT:-/dev/stdout}"

RUN_ID=""
RUN_URL=""

write_outputs() {
    CONCLUSION=$1
    echo "conclusion=${CONCLUSION}" >> "$GITHUB_OUTPUT"
    echo "run-id=${RUN_ID}" >> "$GITHUB_OUTPUT"
    echo "run-url=${RUN_URL}" >> "$GITHUB_OUTPUT"
    echo "${TARGET_REPO} / ${TARGET_WORKFLOW} @ ${TARGET_REF}: ${CONCLUSION} ${RUN_URL}"
    exit 0
}

for VARIABLE in TARGET_REPO TARGET_WORKFLOW TARGET_REF WAIT_TIMEOUT_MIN GH_TOKEN; do
    if [[ -z "${!VARIABLE:-}" ]]; then
        echo "::error::dispatch-and-wait.sh: missing required env variable ${VARIABLE}"
        write_outputs "dispatch_failed"
    fi
done

DEADLINE=$(( $(date +%s) + WAIT_TIMEOUT_MIN * 60 ))

# Watermark BEFORE dispatching: run IDs are monotonically increasing within a repo,
# so "databaseId > watermark" identifies our run without relying on runner clocks
# (the same property merge-notification.yaml relies on).
LAST_ID=$(gh run list --repo "$TARGET_REPO" --workflow "$TARGET_WORKFLOW" \
    --limit 1 --json databaseId --jq '.[0].databaseId // 0' 2>/dev/null)

if [[ -z "$LAST_ID" ]]; then
    echo "::error::${TARGET_REPO}: cannot list runs of ${TARGET_WORKFLOW} (repo missing, Actions disabled, or token lacks access)"
    write_outputs "not_dispatchable"
fi

echo "Watermark for ${TARGET_REPO}/${TARGET_WORKFLOW}: run ${LAST_ID}"

DISPATCH_ERROR=$(gh workflow run "$TARGET_WORKFLOW" --repo "$TARGET_REPO" --ref "$TARGET_REF" 2>&1)
DISPATCH_EXIT_CODE=$?

if [[ $DISPATCH_EXIT_CODE -ne 0 ]]; then
    echo "::error::${TARGET_REPO}: dispatch failed: ${DISPATCH_ERROR}"
    # 404 = workflow/repo not found, 422 = no workflow_dispatch trigger on the default branch
    if echo "$DISPATCH_ERROR" | grep -qE 'HTTP 404|HTTP 422|does not have.*workflow_dispatch|could not find any workflows'; then
        write_outputs "not_dispatchable"
    fi
    write_outputs "dispatch_failed"
fi

echo "Dispatched, correlating…"

# Correlation: the run created by our dispatch is the single workflow_dispatch run
# on TARGET_REF with databaseId above the watermark. More than one candidate means
# someone else dispatched concurrently — report correlation_failed, never guess.
CORRELATION_DEADLINE=$(( $(date +%s) + 120 ))

while true; do
    CANDIDATES=$(gh run list --repo "$TARGET_REPO" --workflow "$TARGET_WORKFLOW" \
        --branch "$TARGET_REF" --event workflow_dispatch --limit 10 \
        --json databaseId,url \
        --jq "[.[] | select(.databaseId > ${LAST_ID})]")

    CANDIDATE_COUNT=$(echo "$CANDIDATES" | jq 'length')

    if [[ "$CANDIDATE_COUNT" -gt 1 ]]; then
        echo "::error::${TARGET_REPO}: ${CANDIDATE_COUNT} new workflow_dispatch runs above watermark ${LAST_ID}, cannot tell which one is ours"
        write_outputs "correlation_failed"
    fi

    if [[ "$CANDIDATE_COUNT" -eq 1 ]]; then
        RUN_ID=$(echo "$CANDIDATES" | jq -r '.[0].databaseId')
        RUN_URL=$(echo "$CANDIDATES" | jq -r '.[0].url')
        echo "Correlated run: ${RUN_URL}"
        break
    fi

    if (( $(date +%s) > CORRELATION_DEADLINE )); then
        echo "::error::${TARGET_REPO}: dispatched run did not appear within 2 minutes"
        write_outputs "correlation_failed"
    fi

    sleep 10
done

# Poll with adaptive backoff (15 s for the first 2 min, 30 s until 5 min, then 60 s):
# ~39 API calls for a 30-minute run instead of `gh run watch`'s 600.
POLL_START=$(date +%s)

while true; do
    if (( $(date +%s) > DEADLINE )); then
        echo "::error::${TARGET_REPO}: run ${RUN_ID} did not finish within ${WAIT_TIMEOUT_MIN} minutes"
        write_outputs "timed_out"
    fi

    RUN_STATE=$(gh run view "$RUN_ID" --repo "$TARGET_REPO" --json status,conclusion \
        --jq '{status: .status, conclusion: .conclusion}' 2>/dev/null)

    if [[ -n "$RUN_STATE" && $(echo "$RUN_STATE" | jq -r '.status') == "completed" ]]; then
        CONCLUSION=$(echo "$RUN_STATE" | jq -r '.conclusion')
        break
    fi

    ELAPSED=$(( $(date +%s) - POLL_START ))
    if (( ELAPSED < 120 )); then
        sleep 15
    elif (( ELAPSED < 300 )); then
        sleep 30
    else
        sleep 60
    fi
done

if [[ "$CONCLUSION" == "cancelled" ]]; then
    # A newer run on the same branch means our run was superseded by concurrency
    # cancel-in-progress (a real push arrived) — the branch got a fresher
    # verification, so this is neither a pass nor a fail.
    NEWER_RUN_COUNT=$(gh run list --repo "$TARGET_REPO" --workflow "$TARGET_WORKFLOW" \
        --branch "$TARGET_REF" --limit 10 --json databaseId \
        --jq "[.[] | select(.databaseId > ${RUN_ID})] | length")

    if [[ "$NEWER_RUN_COUNT" -gt 0 ]]; then
        write_outputs "superseded"
    fi
fi

write_outputs "$CONCLUSION"

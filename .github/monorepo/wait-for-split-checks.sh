#!/bin/bash
# Waits for the "run-checks-tests.yaml" runs that a split of a branch triggered in the split repositories and reports
# their conclusions. Exits 1 when any run did not succeed or never appeared.
#
# Arguments:
#   $1 SPLIT_BRANCH     - branch that was pushed to the split repositories
#   $2 SPLIT_HEADS_FILE - file with "<package> <commit sha>" lines written by split-repositories.sh
#   $3 WAIT_TIMEOUT_MIN - how long to wait for all runs to finish (set the job timeout-minutes higher), default 60
#
# Environment:
#   BASE_COMMIT - optional; the package list, the package directories and the expected CI workflows are then taken
#                 from this commit, so the branch cannot pass the check by removing a package or its workflow
#   GH_TOKEN    - token with read access to the workflow runs of the split repositories
#
# A run is correlated by the pushed commit, not by "the newest run on the branch" - a re-run that pushes an unchanged
# commit triggers no new run in the split repository, but the run of that commit is still the right one to report.

set -e -o pipefail

SPLIT_BRANCH=$1
SPLIT_HEADS_FILE=$2
WAIT_TIMEOUT_MIN=${3:-60}
BASE_COMMIT=${BASE_COMMIT:-}

set -u

MONOREPO_ROOT=$(cd "$(dirname "$0")/../.." && pwd)
WORKFLOW_FILE="run-checks-tests.yaml"
GITHUB_STEP_SUMMARY="${GITHUB_STEP_SUMMARY:-/dev/null}"
# the pushes are done when this script starts, so the runs must show up within this time
RUN_APPEARANCE_TIMEOUT_SEC=300

# Import functions - from the base commit when given, the branch must not be able to change what is expected of it
FUNCTIONS_FILE="$(dirname "$0")/monorepo_functions.sh"
if [[ -n "$BASE_COMMIT" ]]; then
    git -C "$MONOREPO_ROOT" cat-file -e "${BASE_COMMIT}^{commit}" 2>/dev/null || git -C "$MONOREPO_ROOT" fetch --no-tags --depth=1 origin "$BASE_COMMIT"
    FUNCTIONS_FILE=$(mktemp)
    git -C "$MONOREPO_ROOT" show "${BASE_COMMIT}:.github/monorepo/monorepo_functions.sh" > "$FUNCTIONS_FILE"
fi
. "$FUNCTIONS_FILE"

assert_split_branch_variable

if [[ ! -f "$SPLIT_HEADS_FILE" ]]; then
    echo -e "${RED}You must provide the file with pushed commits written by split-repositories.sh!${NC}"
    exit 1
fi

declare -A PENDING_SHA=()
declare -A RUN_URL=()
declare -A CONCLUSION=()

# finish_package <package> <conclusion> [<error detail>]
finish_package() {
    PACKAGE=$1
    RESULT=$2
    DETAIL=${3:-"${RUN_URL[$PACKAGE]:-}"}

    CONCLUSION[$PACKAGE]=$RESULT
    unset "PENDING_SHA[$PACKAGE]"

    if [[ "$RESULT" == "success" ]]; then
        echo -e "${GREEN}✔ shopsys/${PACKAGE}: success ${DETAIL}${NC}"
    else
        echo "::error::shopsys/${PACKAGE}: ${RESULT} - ${DETAIL}"
    fi
}

for PACKAGE in $(get_all_packages); do
    WORKFLOW_PATH="$(get_package_subdirectory "$PACKAGE")/.github/workflows/${WORKFLOW_FILE}"
    SHA=$(awk -v package="$PACKAGE" '$1 == package { print $2 }' "$SPLIT_HEADS_FILE")

    if [[ -z "$SHA" ]]; then
        finish_package "$PACKAGE" "not_split" "the package was not pushed by the split"
    elif [[ -f "${MONOREPO_ROOT}/${WORKFLOW_PATH}" ]]; then
        PENDING_SHA[$PACKAGE]=$SHA
    elif [[ -n "$BASE_COMMIT" ]] && git -C "$MONOREPO_ROOT" cat-file -e "${BASE_COMMIT}:${WORKFLOW_PATH}" 2>/dev/null; then
        finish_package "$PACKAGE" "workflow_missing" "${WORKFLOW_FILE} exists in the base commit but not in the branch, so the package has no CI"
    else
        echo -e "${BLUE}Skipping ${GREEN}\"${PACKAGE}\"${BLUE} - it has no ${WORKFLOW_FILE} workflow${NC}"
    fi
done

if (( ${#PENDING_SHA[@]} == 0 && ${#CONCLUSION[@]} == 0 )); then
    echo -e "${RED}No split repository with a ${WORKFLOW_FILE} workflow was pushed - nothing to wait for${NC}"
    exit 1
fi

echo -e "${BLUE}Waiting for ${WORKFLOW_FILE} runs of branch ${GREEN}\"${SPLIT_BRANCH}\"${BLUE} in ${#PENDING_SHA[@]} split repositories: ${GREEN}${!PENDING_SHA[*]}${NC}"

START=$(date +%s)
DEADLINE=$(( START + WAIT_TIMEOUT_MIN * 60 ))
RUN_APPEARANCE_DEADLINE=$(( START + RUN_APPEARANCE_TIMEOUT_SEC ))

while (( ${#PENDING_SHA[@]} > 0 )); do
    for PACKAGE in "${!PENDING_SHA[@]}"; do
        SHA=${PENDING_SHA[$PACKAGE]}

        # the newest run of the pushed commit wins, an older one was cancelled by the concurrency group of the split repository
        # (the fields are joined by "|" because the conclusion of a running run is empty and "read" would collapse repeated tabs)
        RUN=$(gh run list --repo "shopsys/${PACKAGE}" --workflow "$WORKFLOW_FILE" --branch "$SPLIT_BRANCH" --limit 30 \
            --json databaseId,headSha,status,conclusion,url \
            --jq "[.[] | select(.headSha == \"${SHA}\")] | sort_by(.databaseId) | last // empty | [.status, .conclusion, .url] | join(\"|\")" 2>/dev/null || true)

        if [[ -z "$RUN" ]]; then
            # a transient API error must not be mistaken for a missing run once the run has already been seen
            if [[ -z "${RUN_URL[$PACKAGE]:-}" ]] && (( $(date +%s) > RUN_APPEARANCE_DEADLINE )); then
                finish_package "$PACKAGE" "run_not_found" "no ${WORKFLOW_FILE} run of commit ${SHA} on branch ${SPLIT_BRANCH} appeared within $(( RUN_APPEARANCE_TIMEOUT_SEC / 60 )) minutes"
            fi
            continue
        fi

        IFS='|' read -r STATUS RUN_CONCLUSION URL <<< "$RUN"

        if [[ -z "${RUN_URL[$PACKAGE]:-}" ]]; then
            RUN_URL[$PACKAGE]=$URL
            echo -e "${BLUE}shopsys/${PACKAGE}: run ${URL}${NC}"
        fi

        if [[ "$STATUS" == "completed" ]]; then
            finish_package "$PACKAGE" "$RUN_CONCLUSION"
        fi
    done

    (( ${#PENDING_SHA[@]} == 0 )) && break

    if (( $(date +%s) > DEADLINE )); then
        for PACKAGE in "${!PENDING_SHA[@]}"; do
            finish_package "$PACKAGE" "timed_out" "run did not finish within ${WAIT_TIMEOUT_MIN} minutes ${RUN_URL[$PACKAGE]:-}"
        done
        break
    fi

    # package runs take a few minutes, project-base tens of minutes - poll less often once the quick ones are gone
    if (( $(date +%s) - START < 300 )); then
        sleep 30
    else
        sleep 60
    fi
done

FAILED_PACKAGES=()
{
    echo "## Checks of split repositories for branch \`${SPLIT_BRANCH}\`"
    echo ""
    echo "| Package | Conclusion | Run |"
    echo "|---|---|---|"
    for PACKAGE in $(printf '%s\n' "${!CONCLUSION[@]}" | sort); do
        RESULT=${CONCLUSION[$PACKAGE]}
        if [[ "$RESULT" == "success" ]]; then
            ICON="✅"
        else
            ICON="❌"
            FAILED_PACKAGES+=("$PACKAGE")
        fi
        if [[ -n "${RUN_URL[$PACKAGE]:-}" ]]; then
            LINK="[run](${RUN_URL[$PACKAGE]})"
        else
            LINK="-"
        fi
        echo "| shopsys/${PACKAGE} | ${ICON} ${RESULT} | ${LINK} |"
    done
} >> "$GITHUB_STEP_SUMMARY"

if (( ${#FAILED_PACKAGES[@]} > 0 )); then
    echo -e "${RED}Checks failed in ${#FAILED_PACKAGES[@]} split repositories: ${FAILED_PACKAGES[*]}${NC}"
    exit 1
fi

echo -e "${GREEN}Checks of all split repositories passed${NC}"

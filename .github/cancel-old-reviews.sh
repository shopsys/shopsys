#!/bin/bash

DEFAULT_BRANCH=$1
ACTIONS_TOKEN=$2

current_date=$(date +%Y-%m-%d)
fourteen_days_ago=$(date -d "14 days ago" +%Y-%m-%d)
seven_days_ago=$(date -d "7 days ago" +%Y-%m-%d)

api_url_between_fourteen_and_seven_days_ago="https://api.github.com/repos/shopsys/shopsys/actions/runs?created=$fourteen_days_ago..$seven_days_ago"
response_between_fourteen_and_seven_days_ago=$(curl -L -H "Accept: application/vnd.github+json" -H "Authorization: Bearer $ACTIONS_TOKEN" -H "X-GitHub-Api-Version: 2022-11-28" "$api_url_between_fourteen_and_seven_days_ago")
branches_between_fourteen_and_seven_days_ago=$(echo "$response_between_fourteen_and_seven_days_ago" | jq -r '.workflow_runs[].head_branch' | sort -u)

api_url_last_seven_days="https://api.github.com/repos/shopsys/shopsys/actions/runs?created=$seven_days_ago..$current_date"
response_last_seven_days=$(curl -L -H "Accept: application/vnd.github+json" -H "Authorization: Bearer $ACTIONS_TOKEN" -H "X-GitHub-Api-Version: 2022-11-28" "$api_url_last_seven_days")
branches_last_seven_days=$(echo "$response_last_seven_days" | jq -r '.workflow_runs[].head_branch' | sort -u)

# convert strings to arrays
SAVEIFS=$IFS
IFS=$'\n'

branches_last_seven_days=($branches_last_seven_days)
branches_between_fourteen_and_seven_days_ago=($branches_between_fourteen_and_seven_days_ago)

IFS=$SAVEIFS

# add main branch to branches between 14 and 7 days ago in order to prevent its cancellation
branches_between_fourteen_and_seven_days_ago+=($DEFAULT_BRANCH)

filtered_branches=()

# check if branch from 14-7 days ago has not been built in the last 7 days
for (( i=0; i<${#branches_between_fourteen_and_seven_days_ago[@]}; i++ )) do
    if [[ ! " ${branches_last_seven_days[*]} " =~ " ${branches_between_fourteen_and_seven_days_ago[$i]} " ]]; then
        filtered_branches+=("${branches_between_fourteen_and_seven_days_ago[$i]}")
    fi
done

for BRANCH_NAME in "${filtered_branches[@]}"; do
    /bin/bash ./.github/cancel-review.sh "$BRANCH_NAME"
done

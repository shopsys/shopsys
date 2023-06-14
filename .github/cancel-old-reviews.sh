#!/bin/bash

current_date=$(date +%Y-%m-%d)
eight_days_ago=$(date -d "8 days ago" +%Y-%m-%d)
seven_days_ago=$(date -d "7 days ago" +%Y-%m-%d)

api_url_eight_days_ago="https://api.github.com/repos/shopsys/shopsys/actions/runs?created=$eight_days_ago"
response_eight_days_ago=$(curl -L -H "Accept: application/vnd.github+json" -H "Authorization: Bearer $ACTIONS_TOKEN" -H "X-GitHub-Api-Version: 2022-11-28" "$api_url_eight_days_ago")
branches_eight_days_ago=($(echo "$response_eight_days_ago" | jq -r '.workflow_runs[].head_branch' | sort -u))

api_url_last_seven_days="https://api.github.com/repos/shopsys/shopsys/actions/runs?created=$seven_days_ago..$current_date"
response_last_seven_days=$(curl -L -H "Accept: application/vnd.github+json" -H "Authorization: Bearer $ACTIONS_TOKEN" -H "X-GitHub-Api-Version: 2022-11-28" "$api_url_last_seven_days")
branches_last_seven_days=($(echo "$response_last_seven_days" | jq -r '.workflow_runs[].head_branch' | sort -u))

filtered_branches=()
for branch in "${branches_eight_days_ago[@]}"; do
    if [[ ! " ${branches_last_seven_days[@]} " =~ " ${branch} " ]]; then
        filtered_branches+=("$branch")
    fi
done

filtered_branches=($(echo "${filtered_branches[@]}" | tr ' ' '\n' | grep -v "master"))

for branch in "${filtered_branches[@]}"; do
    if [ -d "$branch" ]; then
        cd "$branch"
        docker compose down -v --remove-orphans
        docker system prune -a
        cd ..
        rm -rf "$branch"
    fi
done

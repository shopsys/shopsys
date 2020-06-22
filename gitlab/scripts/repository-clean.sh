#!/bin/bash -e

RED='\e[31m'
YELLOW='\e[33m'
NO_COLOR='\e[39m'

declare -A REPOSITORY_NAME_MAP_TO_EVIRONMENT=(
  ["master"]="production"
  ["devel"]="devel"
  ["beta"]="beta"
  ["clear-stabil"]="clear-stabil"
)

containsElement () {
  local e match="$1"
  shift
  for e; do
     echo $match |grep -qi ^$e \
    && return 0
  done
  return 1
}

API_URL="${CI_API_V4_URL}/projects/${CI_PROJECT_ID}"

echo -ne "cleaning merged branches: "
curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" -X DELETE "${API_URL}/repository/merged_branches" | jq -r '.message'

echo "cleaning container registry for deleted branches: "
PROJECT_BRANCHES="$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/repository/branches" | jq -r '.[].name' )"
REGISTRY_REPOSITORIES="$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/registry/repositories")"

for REGISTRY_REPOSITORY in $(echo "${REGISTRY_REPOSITORIES}" | jq -rc '.[]'); do
  REPOSITORY_ID=$(echo "${REGISTRY_REPOSITORY}" | jq -r '.id')
  REPOSITORY_NAME=$(echo "${REGISTRY_REPOSITORY}" | jq -r '.name')
  if  ! containsElement "${REPOSITORY_NAME}" ${PROJECT_BRANCHES} && [ -z "${REPOSITORY_NAME_MAP_TO_EVIRONMENT[${REPOSITORY_NAME}]}" ]; then

    CURL_OUTPUT=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" \
    -X DELETE "${API_URL}/registry/repositories/${REPOSITORY_ID}")

    echo -e "    ${REPOSITORY_NAME} --> ${RED}GONE!${NO_COLOR} (${CURL_OUTPUT})"
  else

    if [ ! -z "${REPOSITORY_NAME_MAP_TO_EVIRONMENT[${REPOSITORY_NAME}]}" ]; then
      ENVIRONMENT=${REPOSITORY_NAME_MAP_TO_EVIRONMENT[${REPOSITORY_NAME}]}
      DEPLOYED_TAG=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/deployments?environment=${ENVIRONMENT}&status=success&sort=desc" | jq -r '.[0].sha')

      if [ ! -z "${DEPLOYED_TAG}" ]; then
        CURL_OUTPUT=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" \
          -X DELETE \
          --data 'keep_n=3' \
          --data 'name_regex_delete=.*' \
          --data "'name_regex_keep=${DEPLOYED_TAG}'" \
          "${API_URL}/registry/repositories/${REPOSITORY_ID}/tags")

        echo -e "    ${REPOSITORY_NAME} --> ${YELLOW}DELETED OLD TAGS!${NO_COLOR} (${CURL_OUTPUT})"
      fi

    else
      CURL_OUTPUT=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" \
      -X DELETE \
      --data 'keep_n=3' \
      --data 'name_regex_delete=.*' \
      "${API_URL}/registry/repositories/${REPOSITORY_ID}/tags")

      echo -e "    ${REPOSITORY_NAME} --> ${YELLOW}DELETED OLD TAGS!${NO_COLOR} (${CURL_OUTPUT})"
    fi
  fi
  CURL_OUTPUT=''
done

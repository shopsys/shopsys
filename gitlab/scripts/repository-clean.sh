#!/bin/bash -e

RED='\e[31m'
GREEN='\e[32m'
YELLOW='\e[33m'
NO_COLOR='\e[39m'

declare -A REPOSITORY_NAME_MAP_TO_ENVIRONMENT=(
  ["master"]="production"
  ["devel"]="devel"
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

echo -e "cleaning merged branches: "

BRANCHES="$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/repository/branches" | jq -c '.[] | {name: .name, merged: .merged}')"

for BRANCH in $BRANCHES; do
  BRANCH_NAME=$(echo "${BRANCH}" | jq -r '.name')
  BRANCH_MERGED=$(echo "${BRANCH}" | jq -r '.merged')

  if [ -z "${REPOSITORY_NAME_MAP_TO_ENVIRONMENT[${BRANCH_NAME}]}" ] && ${BRANCH_MERGED} == "true"; then
  	CURL_OUTPUT=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" \
          -X DELETE \
          "${API_URL}/repository/branches/${BRANCH_NAME}")

    echo -e "    ${BRANCH_NAME} --> ${RED}DELETED!${NO_COLOR}"
    CURL_OUTPUT=''
  else
    echo -e "    ${BRANCH_NAME} --> ${GREEN}NOTHING TO DO!${NO_COLOR}"
  fi
done

echo "cleaning container registry for deleted branches: "
PROJECT_BRANCHES="$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/repository/branches" | jq -r '.[].name' )"
REGISTRY_REPOSITORIES="$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/registry/repositories")"

for REGISTRY_REPOSITORY in $(echo "${REGISTRY_REPOSITORIES}" | jq -rc '.[]'); do
  REPOSITORY_ID=$(echo "${REGISTRY_REPOSITORY}" | jq -r '.id')
  REPOSITORY_NAME=$(echo "${REGISTRY_REPOSITORY}" | jq -r '.name')
  if ! containsElement "${REPOSITORY_NAME}" ${PROJECT_BRANCHES} && [ -z "${REPOSITORY_NAME_MAP_TO_ENVIRONMENT[${REPOSITORY_NAME}]}" ]; then

      CURL_OUTPUT=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" \
      -X DELETE "${API_URL}/registry/repositories/${REPOSITORY_ID}")

      echo -e "    ${REPOSITORY_NAME} --> ${RED}GONE!${NO_COLOR} (${CURL_OUTPUT})"
      continue
  else

    if [ ! -z "${REPOSITORY_NAME_MAP_TO_ENVIRONMENT[${REPOSITORY_NAME%%-*}]}" ]; then
      ENVIRONMENT=${REPOSITORY_NAME_MAP_TO_ENVIRONMENT[${REPOSITORY_NAME%%-*}]}
      DEPLOYED_TAG=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/deployments?environment=${ENVIRONMENT}&status=success&sort=desc" | jq -r '.[0].sha')
      DEPLOYED_TAG_CREATED_DATE=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/registry/repositories/${REPOSITORY_ID}/tags/${DEPLOYED_TAG}" | jq -r '.created_at')

      if [ ! -z "${DEPLOYED_TAG}" ] && [ "${DEPLOYED_TAG_CREATED_DATE}" != "null" ]; then

      	TAGS_FOR_BRANCH=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/registry/repositories/${REPOSITORY_ID}/tags")

      	for TAG in $(echo "${TAGS_FOR_BRANCH}" | jq -r '.[].name'); do
      		TAG_DETAIL_CREATED_DATE=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" "${API_URL}/registry/repositories/${REPOSITORY_ID}/tags/${TAG}" | jq -r '.created_at')

      		if [[ ${DEPLOYED_TAG_CREATED_DATE} > ${TAG_DETAIL_CREATED_DATE} ]]; then
      			CURL_OUTPUT=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" -X DELETE "${API_URL}/registry/repositories/${REPOSITORY_ID}/tags/${TAG}")
      		fi
      	done

      	echo -e "    ${REPOSITORY_NAME} --> ${YELLOW}DELETED OLD TAGS FOR DEPLOYED BRANCH ON ${ENVIRONMENT} ENVIRONMENT!${NO_COLOR} (${CURL_OUTPUT})"
      	continue
      fi
    fi
  fi

  CURL_OUTPUT=$(curl -L --silent --header "PRIVATE-TOKEN: ${API_TOKEN}" \
    -X DELETE \
    --data 'keep_n=2' \
    --data 'name_regex_delete=.*' \
    "${API_URL}/registry/repositories/${REPOSITORY_ID}/tags")

  echo -e "    ${REPOSITORY_NAME} --> ${YELLOW}DELETED OLD TAGS!${NO_COLOR} (${CURL_OUTPUT})"
  CURL_OUTPUT=''
done

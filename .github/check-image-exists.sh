#!/bin/sh

checkImageExists() {
    USER=$1
    IMAGE=$2
    IMAGE_TAG=$3
    GITHUB_TOKEN=$4

    GHCR_TOKEN=$(echo ${GITHUB_TOKEN} | base64)

    imageTagPageHttpResponseCode=$(curl -o /dev/null -s -w "%{http_code}\n" -H "Authorization: Bearer ${GHCR_TOKEN}" https://ghcr.io/v2/${USER}/${IMAGE}/manifests/${IMAGE_TAG})

    if [[ $imageTagPageHttpResponseCode == 200 ]]; then
        return 1
    else
        return 0
    fi
}

#!/bin/sh

checkImageExists() {
    USER=$1
    REPOSITORY=$2
    IMAGE_TAG=$3

    imageTagPageHttpResponseCode=$(curl -o /dev/null -s -w "%{http_code}\n" https://index.docker.io/v1/repositories/${USER}/${REPOSITORY}/tags/${IMAGE_TAG})

    if [[ $imageTagPageHttpResponseCode == 200 ]]; then
        return 1
    else
        return 0
    fi
}

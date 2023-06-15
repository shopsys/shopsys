#!/bin/sh

DOCKER_STOREFRONT_REPOSITORY_TAG=$1

docker image build \
    --tag ${DOCKER_STOREFRONT_REPOSITORY_TAG} \
    --target development \
    --no-cache \
    --compress \
    -f ./project-base/storefront/docker/dev.Dockerfile \
    ./project-base/storefront

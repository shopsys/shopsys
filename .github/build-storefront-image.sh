#!/bin/sh

DOCKER_STOREFRONT_REPOSITORY_TAG=$1

docker image build \
    --tag ${DOCKER_STOREFRONT_REPOSITORY_TAG} \
    --target production \
    --no-cache \
    --compress \
    --build-arg CYPRESS_KEEP_TID=1 \
    -f project-base/storefront/docker/Dockerfile \
    ./project-base/storefront

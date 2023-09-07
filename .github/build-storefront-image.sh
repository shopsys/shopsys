#!/bin/sh

DOCKER_STOREFRONT_REPOSITORY_TAG=$1

docker image build \
    --tag ${DOCKER_STOREFRONT_REPOSITORY_TAG} \
    --target production \
    --no-cache \
    --compress \
    -f project-base/storefront/docker/Dockerfile \
    --output type=tar,dest=/tmp/storefront-image.tar \
    ./project-base/storefront

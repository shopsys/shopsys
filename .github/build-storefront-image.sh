#!/bin/sh

DOCKER_STOREFRONT_REPOSITORY_TAG=${DOCKER_USERNAME}/storefront:${DOCKER_STOREFRONT_IMAGE_TAG}

docker image build \
    --tag ${DOCKER_STOREFRONT_REPOSITORY_TAG} \
    --target production \
    --no-cache \
    --compress \
    -f project-base/storefront/docker/Dockerfile \
    ./project-base/storefront
docker image push ${DOCKER_STOREFRONT_REPOSITORY_TAG}

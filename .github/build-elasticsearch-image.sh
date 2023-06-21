#!/bin/sh

DOCKER_ELASTICSEARCH_REPOSITORY_TAG=ghcr.io/${DOCKER_USERNAME}/elasticsearch:${DOCKER_ELASTICSEARCH_IMAGE_TAG}

docker image build \
    --tag ${DOCKER_ELASTICSEARCH_REPOSITORY_TAG} \
    --no-cache \
    --compress \
    -f project-base/app/docker/elasticsearch/Dockerfile \
    .
docker image push ${DOCKER_ELASTICSEARCH_REPOSITORY_TAG}

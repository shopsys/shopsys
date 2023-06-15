#!/bin/sh

DOCKER_ELASTICSEARCH_REPOSITORY_TAG=$1

docker image build \
    --tag ${DOCKER_ELASTICSEARCH_REPOSITORY_TAG} \
    --no-cache \
    --compress \
    -f project-base/app/docker/elasticsearch/Dockerfile \
    .

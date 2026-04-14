#!/bin/sh

DOCKER_STOREFRONT_REPOSITORY_TAG=$1

docker image build \
    --tag ${DOCKER_STOREFRONT_REPOSITORY_TAG} \
    --target production \
    --no-cache \
    --compress \
    --build-arg CYPRESS_KEEP_TID=1 \
    --build-arg SENTRY_RELEASE="${SENTRY_RELEASE}" \
    --build-arg SENTRY_URL="${SENTRY_URL}" \
    --build-arg SENTRY_ORG="${SENTRY_ORG}" \
    --build-arg SENTRY_AUTH_TOKEN="${SENTRY_AUTH_TOKEN}" \
    --build-arg SENTRY_PROJECT="${SENTRY_PROJECT}" \
    --build-arg node_uid=1000 \
    -f project-base/storefront/docker/Dockerfile \
    ./project-base/storefront

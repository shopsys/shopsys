#!/bin/sh

DOCKER_PHP_FPM_REPOSITORY_TAG=ghcr.io/${DOCKER_USERNAME}/php-fpm:${DOCKER_PHP_FPM_IMAGE_TAG}

docker image build \
    --build-arg project_root=project-base/app \
    --build-arg www_data_uid=$(id -u) \
    --build-arg www_data_gid=$(id -g) \
    --tag ${DOCKER_PHP_FPM_REPOSITORY_TAG} \
    --target production \
    --no-cache \
    --compress \
    -f project-base/app/docker/php-fpm/Dockerfile \
    .
docker image push ${DOCKER_PHP_FPM_REPOSITORY_TAG}

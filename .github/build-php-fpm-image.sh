#!/bin/sh

DOCKER_PHP_FPM_REPOSITORY_TAG=${DOCKER_USERNAME}/php-fpm:${DOCKER_PHP_FPM_IMAGE_TAG}

docker image build \
    --build-arg project_root=project-base \
    --build-arg www_data_uid=$(id -u) \
    --build-arg www_data_gid=$(id -g) \
    --tag ${DOCKER_PHP_FPM_REPOSITORY_TAG} \
    --target development \
    --no-cache \
    --compress \
    -f project-base/docker/php-fpm/Dockerfile \
    .
docker image push ${DOCKER_PHP_FPM_REPOSITORY_TAG}

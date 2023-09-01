#!/bin/sh

DOCKER_PHP_FPM_REPOSITORY_TAG=$1
TARGET_STAGE=${2:-base}

docker image build \
    --build-arg project_root=project-base/app \
    --build-arg www_data_uid=$(id -u) \
    --build-arg www_data_gid=$(id -g) \
    --tag ${DOCKER_PHP_FPM_REPOSITORY_TAG} \
    --target ${TARGET_STAGE} \
    --no-cache \
    --compress \
    -f project-base/app/docker/php-fpm/Dockerfile \
    .

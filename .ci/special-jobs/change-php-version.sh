#!/bin/sh -xe

# set default value for php version
PHP_VERSION=${PHP_VERSION:-7.2}

# change php-fpm version in docker images of php-fpm and all microservice containers
sed -ri "s/FROM php:[0-9]\.[0-9]/FROM php:$PHP_VERSION/" project-base/docker/php-fpm/Dockerfile $WORKSPACE/project-base/docker/php-fpm/Dockerfile
sed -ri "s/FROM php:[0-9]\.[0-9]/FROM php:$PHP_VERSION/" $WORKSPACE/microservices/*/docker/Dockerfile

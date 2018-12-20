#!/bin/sh -xe
# change php-fpm version in docker images of php-fpm and all microservice containers
sed -i "s/php\:7\.2\-fpm\-alpine*/php\:7\.1\-fpm\-alpine/" $WORKSPACE/project-base/docker/php-fpm/Dockerfile
sed -i "s/php\:7\.2\-fpm\-alpine*/php\:7\.1\-fpm\-alpine/" $WORKSPACE/microservices/*/docker/Dockerfile

# DOCKER_IMAGE_TAG=ci-commit-php71-${GIT_COMMIT}
docker run \
	-v $WORKSPACE:/tmp \
	-v ~/.kube/config:/root/.kube/config \
	-v /var/run/docker.sock:/var/run/docker.sock \
	-e DEVELOPMENT_SERVER_DOMAIN \
	-e DOCKER_USERNAME \
	-e DOCKER_PASSWORD \
	-e GIT_COMMIT=php71-$GIT_COMMIT \
	-e JOB_NAME \
	-e NGINX_INGRESS_CONTROLLER_HOST_PORT \
	--rm \
	shopsys/kubernetes-buildpack \
	.ci/build_kubernetes.sh

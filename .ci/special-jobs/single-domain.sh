#!/bin/sh -xe
# Make SSFW single domain

# remove all domain URLs except URLs for domain with ID more than 1 in app/config/domains.yml
sed '/id: 2/{:a;Q}' $WORKSPACE/project-base/app/config/domains_urls.yml.dist > $WORKSPACE/project-base/app/config/domains_urls.yml
sed -i '/id: 2/{:a;Q}' $WORKSPACE/project-base/app/config/domains.yml

# set "is-multidomain" property to "false" in build.xml
sed -i 's/<property name="is-multidomain".*/<property name="is-multidomain" value="false" \/>/' $WORKSPACE/project-base/build.xml

# remove all domains with id more than 1 from build_kubernetes.sh
sed -i "/SECOND_DOMAIN_HOSTNAME/d" $WORKSPACE/.ci/build_kubernetes.sh
sed -i "/SECOND_DOMAIN_HOSTNAME/{:a;Q}" $WORKSPACE/project-base/kubernetes/ingress.yml

# DOCKER_IMAGE_TAG=ci-commit-master-single-domain-${GIT_COMMIT}
docker run \
	-v $WORKSPACE:/tmp \
	-v ~/.kube/config:/root/.kube/config \
	-v /var/run/docker.sock:/var/run/docker.sock \
	-e DEVELOPMENT_SERVER_DOMAIN \
	-e DOCKER_USERNAME \
	-e DOCKER_PASSWORD \
	-e GIT_COMMIT=master-single-domain-$GIT_COMMIT \
	-e JOB_NAME \
	-e NGINX_INGRESS_CONTROLLER_HOST_PORT \
	--rm \
	shopsys/kubernetes-buildpack \
	.ci/build_kubernetes.sh

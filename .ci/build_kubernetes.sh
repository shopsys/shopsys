#!/bin/bash -ex

# For details about this script, see /docs/kubernetes/continuous-integration-using-kubernetes.md

# Login to Docker Hub for pushing images into register
echo ${DOCKER_PASSWORD} | docker login --username ${DOCKER_USERNAME} --password-stdin

# Set domain names for 2 domains by git branch name and server domain
FIRST_DOMAIN_HOSTNAME=${JOB_NAME}.${DEVELOPMENT_SERVER_DOMAIN}
SECOND_DOMAIN_HOSTNAME=2.${JOB_NAME}.${DEVELOPMENT_SERVER_DOMAIN}

# Set parameters.yml file and domains_urls
cp project-base/app/config/domains_urls.yml.dist project-base/app/config/domains_urls.yml
cp project-base/app/config/parameters_test.yml.dist project-base/app/config/parameters_test.yml
cp project-base/app/config/parameters.yml.dist project-base/app/config/parameters.yml
yq write --inplace project-base/app/config/domains_urls.yml domains_urls[0].url http://${FIRST_DOMAIN_HOSTNAME}:${NGINX_INGRESS_CONTROLLER_HOST_PORT}
yq write --inplace project-base/app/config/domains_urls.yml domains_urls[1].url http://${SECOND_DOMAIN_HOSTNAME}:${NGINX_INGRESS_CONTROLLER_HOST_PORT}

# Change "overwrite_domain_url" parameter for Selenium tests as containers "webserver" and "php-fpm" are bundled together in a pod "webserver-php-fpm"
yq write --inplace project-base/app/config/parameters_test.yml parameters.overwrite_domain_url http://webserver-php-fpm:8080

# Pull or build Docker images for the current commit
DOCKER_IMAGE_TAG=ci-commit-${GIT_COMMIT}
DOCKER_ELASTIC_IMAGE_TAG=ci-elasticsearch

## Docker image for application php-fpm container
docker image pull ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG} || (
    echo "Image not found (see warning above), building it instead..." &&
    docker image build \
        --build-arg project_root=project-base \
        --tag ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG} \
        --target ci \
        -f project-base/docker/php-fpm/Dockerfile \
        . &&
    docker image push ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
)

## Docker image for application elasticsearch container
docker image pull ${DOCKER_USERNAME}/elasticsearch:${DOCKER_ELASTIC_IMAGE_TAG} || (
    echo "Image not found (see warning above), building it instead..." &&
    docker image build \
        --tag ${DOCKER_USERNAME}/elasticsearch:${DOCKER_ELASTIC_IMAGE_TAG} \
        -f project-base/docker/elasticsearch/Dockerfile \
        . &&
    docker image push ${DOCKER_USERNAME}/elasticsearch:${DOCKER_ELASTIC_IMAGE_TAG}
)

DOCKER_PHP_FPM_IMAGE=${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
DOCKER_ELASTIC_IMAGE=${DOCKER_USERNAME}/elasticsearch:${DOCKER_ELASTIC_IMAGE_TAG}
PATH_CONFIG_DIRECTORY='/var/www/html/project-base/app/config'
GOOGLE_CLOUD_STORAGE_BUCKET_NAME=''
GOOGLE_CLOUD_PROJECT_ID=''

FILES=(
    project-base/kubernetes/ingress.yml
    project-base/kubernetes/kustomize/overlays/ci/ingress-patch.yaml
    project-base/kubernetes/deployments/webserver-php-fpm.yml
    project-base/kubernetes/deployments/elasticsearch.yml

)
VARS=(
    FIRST_DOMAIN_HOSTNAME
    SECOND_DOMAIN_HOSTNAME
    DOCKER_PHP_FPM_IMAGE
    DOCKER_ELASTIC_IMAGE
    PATH_CONFIG_DIRECTORY
    GOOGLE_CLOUD_STORAGE_BUCKET_NAME
    GOOGLE_CLOUD_PROJECT_ID
)

for FILE in "${FILES[@]}"
do
    for VAR in ${VARS[@]}
	do
        sed -i "s|{{$VAR}}|${!VAR}|" "$FILE"
    done
done
unset FILES
unset VARS

# Deploy application using kubectl
kubectl delete namespace ${JOB_NAME} || true
kubectl create namespace ${JOB_NAME}
cd project-base/kubernetes/kustomize

# Echo Kustomize build for debugging
kustomize build overlays/ci

# Apply kubernetes manifests by output of Kustomize Build
kustomize build overlays/ci | kubectl apply -f - --namespace=${JOB_NAME}

# Wait for containers to rollout
kubectl rollout status --namespace=${JOB_NAME} deployment/adminer --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/elasticsearch --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/postgres --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/redis --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/redis-admin --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/selenium-server --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/smtp-server --watch
kubectl rollout status --namespace=${JOB_NAME} deployment/webserver-php-fpm --watch

# Find running php-fpm container
PHP_FPM_POD=$(kubectl get pods --namespace=${JOB_NAME} -l app=webserver-php-fpm -o=jsonpath='{.items[0].metadata.name}')

# Run phing build targets for build of the application
kubectl exec ${PHP_FPM_POD} --namespace=${JOB_NAME} ./phing backend-api-install backend-api-oauth-keys-generate test-db-create test-dirs-create checks-ci

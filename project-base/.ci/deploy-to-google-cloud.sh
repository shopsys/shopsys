#!/bin/bash -ex

# Login to Docker Hub for pushing images into register
echo ${DOCKER_PASSWORD} | docker login --username ${DOCKER_USERNAME} --password-stdin

# Create unique docker image tag with commit hash
DOCKER_IMAGE_TAG=production-commit-${GIT_COMMIT}
DOCKER_ELASTIC_IMAGE_TAG=ci-elasticsearch

# Authenticate yourself with service.account.json file.
export GOOGLE_APPLICATION_CREDENTIALS=/tmp/infrastructure/google-cloud/service-account.json

## Docker image for application php-fpm container
docker image pull ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG} || (
    echo "Image not found (see warning above), building it instead..." &&
    docker image build \
        --tag ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG} \
        --target production \
        --no-cache \
        -f docker/php-fpm/Dockerfile \
        . &&
    docker image push ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
)

## Docker image for application elasticsearch container
docker image pull ${DOCKER_USERNAME}/elasticsearch:${DOCKER_ELASTIC_IMAGE_TAG} || (
    echo "Image not found (see warning above), building it instead..." &&
    docker image build \
        --tag ${DOCKER_USERNAME}/elasticsearch:${DOCKER_ELASTIC_IMAGE_TAG} \
        -f docker/elasticsearch/Dockerfile \
        . &&
    docker image push ${DOCKER_USERNAME}/elasticsearch:${DOCKER_ELASTIC_IMAGE_TAG}
)

# Set proxy url address to google cloud storage bucket API
sed -i "s/{{GOOGLE_CLOUD_STORAGE_BUCKET_NAME}}/${GOOGLE_CLOUD_STORAGE_BUCKET_NAME}/g" docker/nginx/google-cloud/nginx.conf

# Create real parameters files to be modified and applied to the cluster as configmaps
cp config/domains_urls.yaml.dist config/domains_urls.yaml
cp config/parameters.yaml.dist config/parameters.yaml

DOCKER_PHP_FPM_IMAGE=${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
DOCKER_ELASTIC_IMAGE=${DOCKER_USERNAME}/elasticsearch:${DOCKER_ELASTIC_IMAGE_TAG}
PATH_CONFIG_DIRECTORY='/var/www/html/config'
GOOGLE_CLOUD_PROJECT_ID=${PROJECT_ID}

FILES=$( find kubernetes -type f )
VARS=(
    FIRST_DOMAIN_HOSTNAME
    SECOND_DOMAIN_HOSTNAME
    DOCKER_PHP_FPM_IMAGE
    DOCKER_ELASTIC_IMAGE
    PATH_CONFIG_DIRECTORY
    GOOGLE_CLOUD_STORAGE_BUCKET_NAME
    GOOGLE_CLOUD_PROJECT_ID
)

for FILE in $FILES; do
    for VAR in ${VARS[@]}; do
        sed -i "s|{{$VAR}}|${!VAR}|g" "$FILE"
    done
done
unset FILES
unset VARS

# Set domain urls
yq write --inplace config/domains_urls.yaml domains_urls[0].url https://${FIRST_DOMAIN_HOSTNAME}
yq write --inplace config/domains_urls.yaml domains_urls[1].url https://${SECOND_DOMAIN_HOSTNAME}

# Add a mask for trusted proxies so that load balanced traffic is trusted and headers from outside of the network are not lost
yq write --inplace config/parameters.yaml parameters.trusted_proxies[+] 10.0.0.0/8

cd /tmp/infrastructure/google-cloud

gcloud config set container/use_application_default_credentials true

# Activate Service Account
gcloud auth activate-service-account --key-file=service-account.json

# Set project by ID into gcloud config
gcloud config set project ${PROJECT_ID}

gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member serviceAccount:$(gcloud config get-value account) \
    --role roles/owner

# Initialize terraform, installs all the providers
terraform init

# Google cloud storage account id
# e.g. gcs-service-account from gcs-service-account@project-224805.iam.gserviceaccount.com
export TF_VAR_GOOGLE_CLOUD_ACCOUNT_ID=$(gcloud config get-value account | sed "s#@.*##")
export TF_VAR_GOOGLE_CLOUD_PROJECT_ID=${PROJECT_ID}
export TF_VAR_GOOGLE_CLOUD_STORAGE_BUCKET_NAME=${GOOGLE_CLOUD_STORAGE_BUCKET_NAME}

# Get credentials to the kubernetes cluster for "kubectl" command from gcloud if the cluster is already provisioned
if [ -n "$(terraform output google-cluster-primary-name 2> /dev/null)" ]; then
    gcloud container clusters get-credentials $(terraform output google-cluster-primary-name) --zone $(terraform output google-cluster-primary-zone)
fi

# Apply changes in infrastructure
terraform apply --auto-approve

LOAD_BALANCER_IP=$(terraform output loadbalancer-ip)

# Jump to kustomize folder to choose which overlay will be built
cd /tmp/kubernetes/kustomize

kustomize build overlays/production | kubectl apply -f -

echo "Cluster and containers are ready."
echo "IP adderss to loadbalancer is: ${LOAD_BALANCER_IP}"
echo -e "Hosts set to domain are: 1. https://${FIRST_DOMAIN_HOSTNAME} 2. https://${SECOND_DOMAIN_HOSTNAME}"

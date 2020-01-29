#!/bin/bash -e

__DIR__="$(dirname "$(realpath "$0")")"
BASE_PATH="$(realpath "$(dirname "$0")/..")"
CONFIGURATION_TARGET_PATH="${BASE_PATH}/kubernetes"

DOMAINS=(
    DOMAIN_HOSTNAME_1
    DOMAIN_HOSTNAME_2
)

declare -A PARAMETERS=(
    ["parameters.database_host"]=${POSTGRES_DATABASE_IP_ADDRESS}
    ["parameters.database_name"]=${POSTGRES_DATABASE_NAME}
    ["parameters.database_password"]=${POSTGRES_DATABASE_PASSWORD}
    ["parameters.database_port"]=${POSTGRES_DATABASE_PORT}
    ["parameters.database_user"]=${POSTGRES_DATABASE_USER}
    ["parameters.elasticsearch_host"]=${ELASTICSEARCH_IP_ADDRESS_HOST}:${ELASTICSEARCH_HOST_PORT}
    ["parameters.trusted_proxies[+]"]=10.0.0.0/8
)

VARS=(
    POSTGRES_DATABASE_IP_ADDRESS
    ELASTICSEARCH_IP_ADDRESS_HOST
    ELASTICSEARCH_HOST_PORT
    TAG
    PROJECT_NAME
    BASE_PATH

    S3_API_HOST
    S3_API_USERNAME
    S3_API_PASSWORD
    S3_API_BUCKET_NAME

    REDIS_PREFIX
    ELASTIC_SEARCH_INDEX_PREFIX
)

source "${__DIR__}/functions.sh"
source "${__DIR__}/parts/parameters.sh"
source "${__DIR__}/parts/domains.sh"
source "${__DIR__}/parts/kubernetes-variables.sh"
source "${__DIR__}/parts/deploy.sh"

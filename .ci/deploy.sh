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
    ["parameters.sconto_bridge_config.enabled"]=${SCONTO_BRIDGE_DEV_ENABLED}
    ["parameters.sconto_bridge_config.base_uri"]=${SCONTO_BRIDGE_DEV_BASE_URI}
    ["parameters.sconto_bridge_config.user"]=${SCONTO_BRIDGE_DEV_USER}
    ["parameters.sconto_bridge_config.password"]=${SCONTO_BRIDGE_DEV_PASSWORD}
    ["parameters.akeneo_config.enabled"]=${AKENEO_DEV_ENABLED}
    ["parameters.akeneo_config.base_uri"]=${AKENEO_DEV_BASE_URI}
    ["parameters.akeneo_config.client_id"]=${AKENEO_DEV_CLIENT_ID}
    ["parameters.akeneo_config.secret"]=${AKENEO_DEV_SECRET}
    ["parameters.akeneo_config.user"]=${AKENEO_DEV_USER}
    ["parameters.akeneo_config.password"]=${AKENEO_DEV_PASSWORD}
    ["parameters.disable_form_fields_from_transfer"]=${DISABLE_FROM_FIELDS_FROM_TRANSFER}
    ["parameters.kraken_config.enabled"]=${KRAKEN_DEV_ENABLED}
    ["parameters.kraken_config.lossy"]=${KRAKEN_DEV_LOSSY}
    ["parameters.kraken_config.api_key"]=${KRAKEN_DEV_API_KEY}
    ["parameters.kraken_config.api_secret"]=${KRAKEN_DEV_API_SECRET}
    ["parameters.env(CDN_DOMAIN)"]=${CDN_DOMAIN}
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

    CDN_DOMAIN

    REDIS_PREFIX
    ELASTIC_SEARCH_INDEX_PREFIX
)

source "${__DIR__}/functions.sh"
source "${__DIR__}/parts/parameters.sh"
source "${__DIR__}/parts/domains.sh"
source "${__DIR__}/parts/kubernetes-variables.sh"
source "${__DIR__}/parts/deploy.sh"

#!/bin/bash -e

BASE_PATH="$(realpath "$(dirname "$0")/..")"
CONFIGURATION_TARGET_PATH="${BASE_PATH}/var/deployment/kubernetes"
BASIC_AUTH_PATH="${BASE_PATH}/deploy/basicHttpAuth"
DEPLOY_TARGET_PATH="${BASE_PATH}/var/deployment/deploy"
CI_ENVIRONMENT_SLUG=${CI_ENVIRONMENT_SLUG}
SENTRY_DSN=${SENTRY_DSN}
S3_REGION=${S3_REGION:-'us-east-1'}

function deploy() {
    DOMAINS=(
        DOMAIN_HOSTNAME_1
        DOMAIN_HOSTNAME_2
    )

    ENABLE_AUTOSCALING=true

    declare -A PARAMETERS=(
        ["parameters.trusted_proxies[+]"]=10.0.0.0/8
    )

    declare -A ENVIRONMENT_VARIABLES=(
        ["APP_SECRET"]=${APP_SECRET}
        ["DATABASE_HOST"]=${POSTGRES_DATABASE_IP_ADDRESS}
        ["DATABASE_PORT"]=${POSTGRES_DATABASE_PORT}
        ["DATABASE_NAME"]=${PROJECT_NAME}
        ["DATABASE_USER"]=${PROJECT_NAME}
        ["DATABASE_PASSWORD"]=${POSTGRES_DATABASE_PASSWORD}
        ["ELASTICSEARCH_HOST"]=${ELASTICSEARCH_URLS}
        ["ELASTIC_SEARCH_INDEX_PREFIX"]=${PROJECT_NAME}
        ["REDIS_PREFIX"]=${PROJECT_NAME}
        ["MAILER_DSN"]=${MAILER_DSN}
        ["MESSENGER_TRANSPORT_DSN"]=${MESSENGER_TRANSPORT_DSN}

        ["GOPAY_IS_PRODUCTION_MODE"]=${GOPAY_IS_PRODUCTION_MODE}
        ["GOPAY_EN_GOID"]=${GOPAY_EN_GOID}
        ["GOPAY_EN_CLIENTID"]=${GOPAY_EN_CLIENTID}
        ["GOPAY_EN_CLIENTSECRET"]=${GOPAY_EN_CLIENTSECRET}
        ["GOPAY_CS_GOID"]=${GOPAY_CS_GOID}
        ["GOPAY_CS_CLIENTID"]=${GOPAY_CS_CLIENTID}
        ["GOPAY_CS_CLIENTSECRET"]=${GOPAY_CS_CLIENTSECRET}

        ["AKENEO_ENABLED"]=${AKENEO_ENABLED}
        ["AKENEO_BASE_URI"]=${AKENEO_BASE_URI}
        ["AKENEO_CLIENT_ID"]=${AKENEO_CLIENT_ID}
        ["AKENEO_SECRET"]=${AKENEO_SECRET}
        ["AKENEO_USER"]=${AKENEO_USER}
        ["AKENEO_PASSWORD"]=${AKENEO_PASSWORD}

        ["DATA_BRIDGE_ENABLED"]=${DATA_BRIDGE_ENABLED}
        ["DATA_BRIDGE_BASE_URI"]=${DATA_BRIDGE_BASE_URI}
        ["DATA_BRIDGE_USER"]=${DATA_BRIDGE_USER}
        ["DATA_BRIDGE_PASSWORD"]=${DATA_BRIDGE_PASSWORD}

        ["S3_ENDPOINT"]=${S3_ENDPOINT}
        ["S3_ACCESS_KEY"]=${S3_ACCESS_KEY}
        ["S3_SECRET"]=${S3_SECRET}
        ["S3_REGION"]=${S3_REGION}
        ["S3_BUCKET_NAME"]=${PROJECT_NAME}

        ["CDN_DOMAIN"]=${CDN_DOMAIN}
        ["CDN_API_KEY"]=${CDN_API_KEY}
        ["CDN_API_SALT"]=${CDN_API_SALT}

        ["PACKETERY_ENABLED"]=${PACKETERY_ENABLED}
        ["PACKETERY_REST_API_URL"]=${PACKETERY_REST_API_URL}
        ["PACKETERY_API_PASSWORD"]=${PACKETERY_API_PASSWORD}
        ["PACKETERY_SENDER"]=${PACKETERY_SENDER}

        ["SENTRY_DSN"]=${SENTRY_DSN}
        ["SENTRY_ENVIRONMENT"]=${CI_ENVIRONMENT_SLUG}
        ["SENTRY_RELEASE"]=${CI_COMMIT_SHORT_SHA}
        ["FORCE_ELASTIC_LIMITS"]=${FORCE_ELASTIC_LIMITS:-false}
    )

    declare -A STOREFRONT_ENVIRONMENT_VARIABLES=(
        ["GTM_ID"]=${GTM_ID}
        ["SENTRY_DSN"]=${SENTRY_DSN}
        ["SENTRY_ENVIRONMENT"]=${CI_ENVIRONMENT_SLUG}
        ["PACKETERY_API_KEY"]=${PACKETERY_API_KEY}
        ["REDIS_PREFIX"]=${PROJECT_NAME}

        ["CDN_DOMAIN"]=${CDN_DOMAIN}
    )

    declare -A CRON_INSTANCES=(
        ["cron"]='*/5 * * * *'
        ["cron-service"]='*/5 * * * *'
        ["cron-export"]='*/5 * * * *'
        ["cron-products"]='*/5 * * * *'
    )

    VARS=(
        TAG
        STOREFRONT_TAG
        PROJECT_NAME
        BASE_PATH
        CI_ENVIRONMENT_SLUG
        SENTRY_DSN
        RABBITMQ_DEFAULT_USER
        RABBITMQ_DEFAULT_PASS
        RABBITMQ_IP_WHITELIST
    )

    source "${DEPLOY_TARGET_PATH}/functions.sh"
    source "${DEPLOY_TARGET_PATH}/parts/domains.sh"
    source "${DEPLOY_TARGET_PATH}/parts/domain-rabbitmq-management.sh"
    source "${BASE_PATH}/deploy/parts/whitelist-ip.sh"
    source "${DEPLOY_TARGET_PATH}/parts/environment-variables.sh"
    source "${DEPLOY_TARGET_PATH}/parts/kubernetes-variables.sh"
    source "${DEPLOY_TARGET_PATH}/parts/cron.sh"
    source "${DEPLOY_TARGET_PATH}/parts/autoscaling.sh"
    source "${DEPLOY_TARGET_PATH}/parts/deploy.sh"
}

function merge() {
    # Specify consumers configuration with the default configuration in the format:
    # <consumer-name>:<transport-names-separated-by-space>:<number-of-consumers>
    DEFAULT_CONSUMERS=(
        "example:example_transport:1"
        "placed_order:placed_order_transport:1"
    )

    source "${BASE_PATH}/vendor/shopsys/deployment/deploy/functions.sh"
    merge_configuration
    create_consumer_manifests $DEFAULT_CONSUMERS
}

case "$1" in
"deploy")
    deploy
    ;;
"merge")
    merge
    ;;
*)
    echo "invalid option"
    exit 1
    ;;
esac

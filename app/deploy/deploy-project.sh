#!/bin/bash -e

BASE_PATH="$(realpath "$(dirname "$0")/..")"
CONFIGURATION_TARGET_PATH="${BASE_PATH}/var/deployment/kubernetes"
BASIC_AUTH_PATH="${BASE_PATH}/deploy/basicHttpAuth"
DEPLOY_TARGET_PATH="${BASE_PATH}/var/deployment/deploy"
CI_ENVIRONMENT_SLUG=${CI_ENVIRONMENT_SLUG}
SENTRY_DSN=${SENTRY_DSN}

function deploy() {
    DOMAINS=(
        DOMAIN_HOSTNAME_1
        DOMAIN_HOSTNAME_2
    )

    ENABLE_AUTOSCALING=true
    USING_STOREFRONT=true

    declare -A PARAMETERS=(
        ["parameters.trusted_proxies[+]"]=10.0.0.0/8
    )

    declare -A ENVIRONMENT_VARIABLES=(
        ["DATABASE_HOST"]=${POSTGRES_DATABASE_IP_ADDRESS}
        ["DATABASE_PORT"]=${POSTGRES_DATABASE_PORT}
        ["DATABASE_NAME"]=${PROJECT_NAME}
        ["DATABASE_USER"]=${PROJECT_NAME}
        ["DATABASE_PASSWORD"]=${POSTGRES_DATABASE_PASSWORD}
        ["ELASTICSEARCH_HOST"]=${ELASTICSEARCH_URLS}
        ["ELASTIC_SEARCH_INDEX_PREFIX"]=${PROJECT_NAME}
        ["REDIS_PREFIX"]=${PROJECT_NAME}
        ["MAILER_HOST"]='shopmail.shopsys.cz'

        ["GOOGLE_MAP_API_KEY"]=${GOOGLE_MAP_API_KEY}

        ["GOPAY_IS_PRODUCTION_MODE"]=${GOPAY_IS_PRODUCTION_MODE}
        ["GOPAY_CS_GOID"]=${GOPAY_CS_GOID}
        ["GOPAY_CS_CLIENTID"]=${GOPAY_CS_CLIENTID}
        ["GOPAY_CS_CLIENTSECRET"]=${GOPAY_CS_CLIENTSECRET}
        ["GOPAY_SK_GOID"]=${GOPAY_SK_GOID}
        ["GOPAY_SK_CLIENTID"]=${GOPAY_SK_CLIENTID}
        ["GOPAY_SK_CLIENTSECRET"]=${GOPAY_SK_CLIENTSECRET}

        ["GTM_CS_ENABLED"]=${GTM_CS_ENABLED}
        ["GTM_CS_CONTAINER_ID"]=${GTM_CS_CONTAINER_ID}
        ["GTM_CS_CONTAINER_ENV"]=${GTM_CS_CONTAINER_ENV}
        ["GTM_SK_ENABLED"]=${GTM_SK_ENABLED}
        ["GTM_SK_CONTAINER_ID"]=${GTM_SK_CONTAINER_ID}
        ["GTM_SK_CONTAINER_ENV"]=${GTM_SK_CONTAINER_ENV}

        ["AKENEO_ENABLED"]=${AKENEO_ENABLED}
        ["AKENEO_BASE_URI"]=${AKENEO_BASE_URI}
        ["AKENEO_CLIENT_ID"]=${AKENEO_CLIENT_ID}
        ["AKENEO_SECRET"]=${AKENEO_SECRET}
        ["AKENEO_USER"]=${AKENEO_USER}
        ["AKENEO_PASSWORD"]=${AKENEO_PASSWORD}

        ["SSFWCC_BRIDGE_ENABLED"]=${SSFWCC_BRIDGE_ENABLED}
        ["SSFWCC_BRIDGE_BASE_URI"]=${SSFWCC_BRIDGE_BASE_URI}
        ["SSFWCC_BRIDGE_USER"]=${SSFWCC_BRIDGE_USER}
        ["SSFWCC_BRIDGE_PASSWORD"]=${SSFWCC_BRIDGE_PASSWORD}

        ["S3_API_HOST"]=${S3_API_HOST}
        ["S3_API_USERNAME"]=${S3_API_USERNAME}
        ["S3_API_PASSWORD"]=${S3_API_PASSWORD}
        ["S3_API_BUCKET_NAME"]=${PROJECT_NAME}

        ["CDN_DOMAIN"]=${CDN_DOMAIN}

        ["PACKETERY_ENABLED"]=${PACKETERY_ENABLED}
        ["PACKETERY_REST_API_URL"]=${PACKETERY_REST_API_URL}
        ["PACKETERY_API_PASSWORD"]=${PACKETERY_API_PASSWORD}
        ["PACKETERY_SENDER"]=${PACKETERY_SENDER}

        ["SENTRY_DSN"]=${SENTRY_DSN}
        ["SENTRY_ENVIRONMENT"]=${CI_ENVIRONMENT_SLUG}
        ["SENTRY_RELEASE"]=${CI_COMMIT_SHORT_SHA}
    )

    declare -A STOREFRONT_ENVIRONMENT_VARIABLES=(
        ["GTM_ID"]=${GTM_ID}
        ["NEXT_PUBLIC_SENTRY_DSN"]=${SENTRY_DSN}
        ["NEXT_PUBLIC_SENTRY_ENVIRONMENT"]=${CI_ENVIRONMENT_SLUG}
        ["REDIS_PREFIX"]=${PROJECT_NAME}
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
    )

    source "${DEPLOY_TARGET_PATH}/functions.sh"
    source "${DEPLOY_TARGET_PATH}/parts/parameters.sh"
    source "${DEPLOY_TARGET_PATH}/parts/domains.sh"
    source "${BASE_PATH}/deploy/parts/whitelist-ip.sh"
    source "${DEPLOY_TARGET_PATH}/parts/environment-variables.sh"
    source "${DEPLOY_TARGET_PATH}/parts/kubernetes-variables.sh"
    source "${DEPLOY_TARGET_PATH}/parts/cron.sh"
    source "${DEPLOY_TARGET_PATH}/parts/autoscaling.sh"
    source "${DEPLOY_TARGET_PATH}/parts/deploy.sh"
}

function merge() {
    source "${BASE_PATH}/vendor/devops/kubernetes-deployment/deploy/functions.sh"
    merge_configuration
    source "${BASE_PATH}/vendor/shopsys/cdn/deploymentPatch/cdnPatch.sh"
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

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
    ["parameters.mailer_host"]=${SMTP_HOST}
    ["parameters.elasticsearch_host"]=${ELASTICSEARCH_IP_ADDRESS_HOST}:${ELASTICSEARCH_HOST_PORT}
    ["parameters.trusted_proxies[+]"]=10.0.0.0/8
    ["parameters.sconto_bridge_config.enabled"]=${SCONTO_BRIDGE_DEV_ENABLED}
    ["parameters.sconto_bridge_config.base_uri"]=${SCONTO_BRIDGE_DEV_BASE_URI}
    ["parameters.sconto_bridge_config.user"]=${SCONTO_BRIDGE_DEV_USER}
    ["parameters.sconto_bridge_config.password"]=${SCONTO_BRIDGE_DEV_PASSWORD}
    ["parameters.akeneo_config.enabled"]=${AKENEO_ENABLED}
    ["parameters.akeneo_config.base_uri"]=${AKENEO_BASE_URI}
    ["parameters.akeneo_config.client_id"]=${AKENEO_CLIENT_ID}
    ["parameters.akeneo_config.secret"]=${AKENEO_SECRET}
    ["parameters.akeneo_config.user"]=${AKENEO_USER}
    ["parameters.akeneo_config.password"]=${AKENEO_PASSWORD}
    ["parameters.disable_form_fields_from_transfer"]=${DISABLE_FROM_FIELDS_FROM_TRANSFER}
    ["parameters.kraken_config.enabled"]=${KRAKEN_DEV_ENABLED}
    ["parameters.kraken_config.lossy"]=${KRAKEN_DEV_LOSSY}
    ["parameters.kraken_config.api_key"]=${KRAKEN_DEV_API_KEY}
    ["parameters.kraken_config.api_secret"]=${KRAKEN_DEV_API_SECRET}
    ["parameters.targito_config.enabled"]=${TARGITO_DEV_ENABLED}
    ["parameters.targito_config.eshop_to_targito_account_id"]=${TARGITO_ESHOP_TO_TARGITO_ACCOUNT_ID}
    ["parameters.targito_config.eshop_to_targito_password"]=${TARGITO_ESHOP_TO_TARGITO_PASSWORD}
    ["parameters.gtm_config.cs.enabled"]=${GTM_DEV_ENABLED}
    ["parameters.gtm_config.cs.container_id"]=${GTM_CZ_ID}
    ["parameters.gtm_config.sk.enabled"]=${GTM_DEV_ENABLED}
    ["parameters.gtm_config.sk.container_id"]=${GTM_SK_ID}
    ["parameters.env(CDN_DOMAIN)"]=${CDN_DOMAIN}
    ["parameters.promo_code_manage_page_config.user"]=${PROMO_CODE_MANAGE_PAGE_USER_LOGIN}
    ["parameters.promo_code_manage_page_config.pass"]=${PROMO_CODE_MANAGE_PAGE_USER_PASS}
    ["parameters.promo_code_manage_page_config.allowed_ips[+]"]=${PROMO_CODE_MANAGE_PAGE_ALLOWED_IPS}
    ["parameters.show_mail_restriction_info_bar"]=${SHOW_MAIL_RESTRICTION_INFO_BAR}
    ["parameters.admin_url"]=${ADMIN_URL}
    ["parameters.gopay_config.cs.clientSecret"]=${GOPAY_CS_CLIENTSECRET}
    ["parameters.gopay_config.sk.clientSecret"]=${GOPAY_SK_CLIENTSECRET}
    ["parameters.gopay_config.isProductionMode"]=${GOPAY_PRODUCTION_MODE}
)

# declare parameters, that types' need to be forced
declare -A CAST_PARAMETERS=(
    ["parameters.gopay_config.cs.goid"]=${GOPAY_CS_GOID}
    ["parameters.gopay_config.cs.clientId"]=${GOPAY_CS_CLIENTID}
    ["parameters.gopay_config.sk.goid"]=${GOPAY_SK_GOID}
    ["parameters.gopay_config.sk.clientId"]=${GOPAY_SK_CLIENTID}
)

declare -A CRON_INSTANCES=(
    ["cron-service"]='*/5 * * * *'
    ["cron-export"]='*/5 * * * *'
#    ["cron-import-akeneo-product"]='*/5 * * * *'
#    ["cron-import-im"]='*/5 * * * *'
#    ["cron-import-akeneo-other"]='*/5 * * * *'
#    ["cron-gopay"]='*/5 * * * *'
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

    K8S_HPA_REPLICAS_MIN
    K8S_HPA_REPLICAS_MAX
    K8S_DEPLOY_PHPFPM_CPU_LIMIT
    K8S_DEPLOY_PHPFPM_CPU_REQUEST
    K8S_DEPLOY_WEBSERVER_CPU_LIMIT
    K8S_DEPLOY_WEBSERVER_CPU_REQUEST
)

source "${__DIR__}/functions.sh"
source "${__DIR__}/parts/parameters.sh"
source "${__DIR__}/parts/domains.sh"
source "${__DIR__}/parts/kubernetes-variables.sh"
source "${__DIR__}/parts/cron.sh"
source "${__DIR__}/parts/deploy.sh"

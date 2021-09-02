#!/bin/bash -e

echo -n "Prepare Domains "

assertVariable "BASE_PATH"
assertVariable "CONFIGURATION_TARGET_PATH"
assertVariable "DOMAINS"
assertVariable "RUNNING_PRODUCTION"

DOMAIN_ITERATOR=0

for DOMAIN in ${DOMAINS[@]}; do
    BASENAME=${!DOMAIN}

    if [[ "${BASENAME}" == *"/"* ]]; then
        BASENAME=${BASENAME%%\/*} # Remove path from Domain if exists
    fi

    yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/storefront.yaml" "spec.template.spec.containers[0].env[${DOMAIN_ITERATOR}].name" "\"${DOMAIN}\""
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/storefront.yaml" "spec.template.spec.containers[0].env[${DOMAIN_ITERATOR}].value" "\"https://${BASENAME}/\""

    DOMAIN_ITERATOR=$(expr $DOMAIN_ITERATOR + 1)
done

yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/storefront.yaml" "spec.template.spec.containers[0].env[${DOMAIN_ITERATOR}].name" "\"INTERNAL_HOST\""
yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/storefront.yaml" "spec.template.spec.containers[0].env[${DOMAIN_ITERATOR}].value" "\"http://webserver-php-fpm:8080/graphql/\""


echo -e "[${GREEN}OK${NO_COLOR}]"

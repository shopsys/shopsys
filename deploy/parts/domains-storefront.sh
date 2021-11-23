#!/bin/bash -e

echo -n "Prepare Domains "

assertVariable "BASE_PATH"
assertVariable "CONFIGURATION_TARGET_PATH"
assertVariable "DOMAINS"
assertVariable "RUNNING_PRODUCTION"

ENV_VARIABLE_ITERATOR=0

for DOMAIN in ${DOMAINS[@]}; do
    BASENAME=${!DOMAIN}

    if [[ "${BASENAME}" == *"/"* ]]; then
        BASENAME=${BASENAME%%\/*} # Remove path from Domain if exists
    fi

    yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/storefront.yaml" "spec.template.spec.containers[0].env[${ENV_VARIABLE_ITERATOR}].name" "\"${DOMAIN}\""
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/storefront.yaml" "spec.template.spec.containers[0].env[${ENV_VARIABLE_ITERATOR}].value" "\"https://${BASENAME}/\""

    ENV_VARIABLE_ITERATOR=$(expr $ENV_VARIABLE_ITERATOR + 1)

    yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/storefront.yaml" "spec.template.spec.containers[0].env[${ENV_VARIABLE_ITERATOR}].name" "\"${DOMAIN/DOMAIN_HOSTNAME/PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME}\""
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/storefront.yaml" "spec.template.spec.containers[0].env[${ENV_VARIABLE_ITERATOR}].value" "\"https://${BASENAME}/graphql/\""

    ENV_VARIABLE_ITERATOR=$(expr $ENV_VARIABLE_ITERATOR + 1)
done

yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/storefront.yaml" "spec.template.spec.containers[0].env[${ENV_VARIABLE_ITERATOR}].name" "\"INTERNAL_GRAPHQL_ENDPOINT\""
yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/storefront.yaml" "spec.template.spec.containers[0].env[${ENV_VARIABLE_ITERATOR}].value" "\"http://webserver-php-fpm:8080/graphql/\""


echo -e "[${GREEN}OK${NO_COLOR}]"

#!/bin/bash -e

echo -n "Prepare Domains for storefront"

assertVariable "BASE_PATH"
assertVariable "CONFIGURATION_TARGET_PATH"
assertVariable "STOREFRONT_DOMAINS"
assertVariable "RUNNING_PRODUCTION"

if [ -z ${FORCE_HTTP_AUTH_IN_PRODUCTION} ]; then
  FORCE_HTTP_AUTH_IN_PRODUCTION=()
fi

# Configure domains, part domains.sh must be run before
DOMAIN_ITERATOR=0

for DOMAIN in ${STOREFRONT_DOMAINS[@]}; do
    INGRESS_FILENAME="ingress-${DOMAIN_ITERATOR}.yaml"

    BASENAME=${!DOMAIN}

    if [[ "${BASENAME}" == *"/"* ]]; then
        BASENAME=${BASENAME%%\/*} # Remove path from Domain if exists
    fi

    if [[ ${BASENAME} == "www."* ]]; then
        BASE_DOMAIN=${BASENAME}
        REDIRECT_DOMAIN=${BASENAME#"www."}
    else
        BASE_DOMAIN=${BASENAME}
        REDIRECT_DOMAIN="www.${BASENAME}"
    fi

    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress/${INGRESS_FILENAME}" spec.rules[1].host ${BASE_DOMAIN}
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress/${INGRESS_FILENAME}" spec.tls[0].hosts[+] ${BASE_DOMAIN}

    if [ ${RUNNING_PRODUCTION} -eq "1" ] && ! containsElement ${DOMAIN} ${FORCE_HTTP_AUTH_IN_PRODUCTION[@]}; then
        yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress/${INGRESS_FILENAME}" spec.tls[0].hosts[+] ${REDIRECT_DOMAIN}
    fi

    DOMAIN_ITERATOR=$(expr $DOMAIN_ITERATOR + 1)
done

echo -e "[${GREEN}OK${NO_COLOR}]"

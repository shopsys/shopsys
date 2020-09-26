#!/bin/bash -e

echo "Start of domains.sh"

assertVariable "BASE_PATH"
assertVariable "CONFIGURATION_TARGET_PATH"
assertVariable "DOMAINS"
assertVariable "RUNNING_PRODUCTION"

domains_urls_filepath="${BASE_PATH}/config/domains_urls.yaml"

cp "${BASE_PATH}/config/domains_urls.yaml.dist" $domains_urls_filepath

# Configure domains
domain_iterator=0

for DOMAIN in ${DOMAINS[@]}; do
    BASE_DOMAIN=${!DOMAIN}

    if [[ ${!DOMAIN} == "www."* ]]; then
      REDIRECT_DOMAIN=${!DOMAIN#"www."}
    else
      REDIRECT_DOMAIN="www.${!DOMAIN}"
    fi

    echo "Www-redirect from ${REDIRECT_DOMAIN} to ${BASE_DOMAIN}"

    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress_domain_${domain_iterator}.yaml" spec.rules[0].host ${BASE_DOMAIN}
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress_domain_${domain_iterator}.yaml" spec.tls[0].hosts[+] ${BASE_DOMAIN}
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/webserver-php-fpm.yaml" spec.template.spec.hostAliases[0].hostnames[+] ${BASE_DOMAIN}
    yq write --inplace $domains_urls_filepath domains_urls[${domain_iterator}].url https://${BASE_DOMAIN}

    if [ ${RUNNING_PRODUCTION} -eq "1" ]; then
        yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress_domain_${domain_iterator}.yaml" spec.tls[0].hosts[+] ${REDIRECT_DOMAIN}
    fi

    domain_iterator=$(expr $domain_iterator + 1)
done

echo "End of domains.sh"

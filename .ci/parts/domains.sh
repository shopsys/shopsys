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
smtp_other_hostnames=""

for DOMAIN in ${DOMAINS[@]}; do
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yaml" spec.rules[${domain_iterator}].host ${!DOMAIN}
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yaml" spec.tls[0].hosts[+] ${!DOMAIN}
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/webserver-php-fpm.yaml" spec.template.spec.hostAliases[0].hostnames[+] ${!DOMAIN}
    yq write --inplace $domains_urls_filepath domains_urls[${domain_iterator}].url https://${!DOMAIN}

    if [ ${RUNNING_PRODUCTION} -eq "1" ]; then
        yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yaml" spec.tls[0].hosts[+] www.${!DOMAIN}
    fi

    if [ ${domain_iterator} -gt "0" ]; then
        smtp_other_hostnames+="${!DOMAIN};"
    fi

    domain_iterator=$(expr $domain_iterator + 1)
done

echo "End of domains.sh"

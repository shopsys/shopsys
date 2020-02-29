#!/bin/bash -e

echo "Start of domains.sh"

assertVariable "BASE_PATH"
assertVariable "CONFIGURATION_TARGET_PATH"
assertVariable "DOMAINS"
assertVariable "RUNNING_PRODUCTION"

domains_urls_filepath="${BASE_PATH}/config/domains_urls.yml"

cp "${BASE_PATH}/config/domains_urls.yml.dist" $domains_urls_filepath

# Configure domains
domain_iterator=0
smtp_other_hostnames=""

for DOMAIN in ${DOMAINS[@]}; do
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yml" spec.rules[${domain_iterator}].host ${!DOMAIN}
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yml" spec.tls[0].hosts[+] ${!DOMAIN}
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/webserver-php-fpm.yml" spec.template.spec.hostAliases[0].hostnames[+] ${!DOMAIN}
    yq write --inplace $domains_urls_filepath domains_urls[${domain_iterator}].url https://${!DOMAIN}

    if [ ${RUNNING_PRODUCTION} -eq "1" ]; then
        yq write --inplace "${CONFIGURATION_TARGET_PATH}/ingress.yml" spec.tls[0].hosts[+] www.${!DOMAIN}
    fi

    if [ ${domain_iterator} -gt "0" ]; then
        smtp_other_hostnames+="${!DOMAIN};"
    fi

    domain_iterator=$(expr $domain_iterator + 1)
done

yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/smtp-server.yml" spec.template.spec.containers[0].env[1].value ${DOMAINS[0]}

if [ ${domain_iterator} -gt "1" ]; then
    yq write --inplace "${CONFIGURATION_TARGET_PATH}/deployments/smtp-server.yml" spec.template.spec.containers[0].env[2].value ${smtp_other_hostnames}
fi

echo "End of domains.sh"

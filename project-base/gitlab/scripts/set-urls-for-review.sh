#!/bin/bash -e

setUrlsToDomainsUrls () {
    DOMAINS=${HOSTS}
    ITERATOR=1

    for DOMAIN in ${DOMAINS//,/ } ; do
        docker compose exec -T php-fpm sed -i "s/http:\/\/127.0.0.${ITERATOR}:8000/https:\/\/${DOMAIN//\//\\/}/g" config/domains_urls.yaml
        docker compose exec -T php-consumer sed -i "s/http:\/\/127.0.0.${ITERATOR}:8000/https:\/\/${DOMAIN//\//\\/}/g" config/domains_urls.yaml
        ITERATOR=$(expr $ITERATOR + 1)
    done
}

convertToTraefikHosts() {
    local hosts_string=$1
    local filtered_hosts=$(echo "$hosts_string" | sed -e 's|/.*||' | tr ',' '\n' | grep -v '^$' | sort -u | tr '\n' ',')
    export TRAEFIK_HOSTS=$(echo "$filtered_hosts" | sed -e 's/ *, */,/g' -e 's/\([^,]*\)/Host(`\1`)/g' -e 's/,/ || /g' | sed -e 's/ || $//' | sed -e 's/ || Host(``)//g')
}

setDomainsToDockerCompose() {
    DOMAINS=${HOSTS}
    ITERATOR=1

    for DOMAIN in ${DOMAINS//,/ } ; do
        yq -i ".services.storefront.environment.DOMAIN_HOSTNAME_${ITERATOR} = \"https://${DOMAIN}/\"" ./docker-compose.yml
        yq -i ".services.storefront.environment.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_${ITERATOR} = \"https://${DOMAIN}/graphql/\"" ./docker-compose.yml

        ITERATOR=$(expr $ITERATOR + 1)
    done
}

printDomains() {
    DOMAINS=${HOSTS}
    echo "Available hosts"

    for DOMAIN in ${DOMAINS//,/ } ; do
        echo "    - https://${DOMAIN}"
    done
}

case $1 in
    setUrlsToDomainsUrls) "$@"; exit;;
    printDomains) "$@"; exit;;
    setDomainsToDockerCompose) "$@"; exit;;
esac

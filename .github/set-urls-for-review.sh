#!/bin/bash -e

setUrlsToDomainsUrls () {
    DOMAINS=${HOSTS}
    ITERATOR=1

    for DOMAIN in ${DOMAINS//,/ } ; do
        docker compose exec -T php-fpm sed -i "s/http:\/\/127.0.0.${ITERATOR}:8000/https:\/\/${DOMAIN}/g" project-base/app/config/domains_urls.yaml
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
esac

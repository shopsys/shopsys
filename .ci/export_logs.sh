#!/bin/sh -ex

# used to copy logs of codeception which are not streamed
WEBSERVER_PHP_FPM_CONTAINER_NAME="webserver-php-fpm"

for POD_NAME in $(kubectl get pods -n ${JOB_NAME} | grep -v ^NAME | cut -f 1 -d ' '); do
    # check if pod name is php-fpm pod because of codeception logs
    if [[ ${POD_NAME} == *${WEBSERVER_PHP_FPM_CONTAINER_NAME}* ]]; then
        # copy codeception logs from php-fpm pod to local
        # we do not need to specify the php-fpm container because it is picked by default
        kubectl cp ${JOB_NAME}/${POD_NAME}:/var/www/html/project-base/var/logs/codeception/ ${WORKSPACE}/logs/codeception
    fi
done

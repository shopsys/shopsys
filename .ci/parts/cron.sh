#!/bin/bash -e

echo "Prepare Cron"

assertVariable "BASE_PATH"
assertVariable "CONFIGURATION_TARGET_PATH"

for key in ${!CRON_INSTANCES[@]}; do

    CRONTAB_LINE="        ${CRON_INSTANCES[${key}]} root . /root/project_env.sh; cd /var/www/html && ./phing ${key} > /dev/null 2>&1"
    echo "${CRONTAB_LINE}" >> "${CONFIGURATION_TARGET_PATH}/configmap/cron-list.yaml"

done

echo "        " >> "${CONFIGURATION_TARGET_PATH}/configmap/cron-list.yaml"
unset CRON_INSTANCES

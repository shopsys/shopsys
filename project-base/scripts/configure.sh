#!/bin/bash
numberRegex="^[0-9]+([.][0-9]+)?$"
operatingSystem=""
allowedValues=(1 2)
projectPathPrefix=""
echo This is installation script that will install demo Shopsys Framework application on docker with all required containers and with demo database created.

docker ps -q &> /dev/null
if [[ "$?" != 0 ]]; then
    1>&2 echo -e "\e[31mERROR:\e[0m Unable to connect to docker. Either the docker service is not running, or the current user is not allowed to run docker."
    exit 1
fi

set -e

echo "Start with specifying your operating system: \

    1) Linux or Windows with WSL 2
    2) Mac
    "

while [[ 1 -eq 1 ]]
do
    read -p "Enter OS number: " operatingSystem
    if [[ ${operatingSystem} =~ $numberRegex ]] ; then
        if [[ " ${allowedValues[@]} " =~ " ${operatingSystem} " ]]; then
            break;
        fi
        echo "Not existing value, please enter one of existing values"
    else
        echo "Please enter a number"
    fi
done

if [[ -d "project-base" ]]; then
    projectPathPrefix="project-base/"
    echo "You are in monorepo, prefixing paths app paths with ${projectPathPrefix}"
fi

echo "Creating domains_urls.yaml file.."
cp -f "${projectPathPrefix}config/domains_urls.yaml.dist" "${projectPathPrefix}config/domains_urls.yaml"

echo "Creating docker configuration.."
case "$operatingSystem" in
    "1")
        cp -f docker/conf/docker-compose.yml.dist docker-compose.yml

        sed -i -r "s#www_data_uid: [0-9]+#www_data_uid: $(id -u)#" ./docker-compose.yml
        sed -i -r "s#www_data_gid: [0-9]+#www_data_gid: $(id -g)#" ./docker-compose.yml
        ;;
    "2")
        cp -f docker/conf/docker-compose-mac.yml.dist docker-compose.yml
        cp -f docker/conf/docker-sync.yml.dist docker-sync.yml

        sed -i '' -E "s#www_data_uid: [0-9]+#www_data_uid: $(id -u)#" ./docker-compose.yml
        sed -i '' -E "s#www_data_gid: [0-9]+#www_data_gid: $(id -g)#" ./docker-compose.yml

        if [[ $1 != --skip-aliasing ]]; then
            echo "You will be asked to enter sudo password in case to allow second domain alias in your system config.."
            sudo ifconfig lo0 alias 127.0.0.2 up
        fi

        mkdir -p ${projectPathPrefix}var/postgres-data ${projectPathPrefix}var/elasticsearch-data vendor
        docker-sync start
        ;;
esac

echo "Starting docker-compose.."

docker-compose up -d --build

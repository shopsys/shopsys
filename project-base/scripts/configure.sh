#!/bin/bash
numberRegex="^[0-9]+([.][0-9]+)?$"
operatingSystem=""
allowedValues=(1 2)
projectPathPrefix="app/"
scriptsPathPrefix=""
skipAliasing=false

echo This is installation script that will install demo Shopsys Platform application on docker with all required containers and with demo database created.

docker ps -q &> /dev/null
if [[ "$?" != 0 ]]; then
    1>&2 printf "\e[31mERROR:\e[0m Unable to connect to docker. Either the docker service is not running, or the current user is not allowed to run docker.\n"
    exit 1
fi

set -e

operatingSystemsList="
    1) Linux or Windows with WSL 2
    2) macOS with Mutagen
"

for argument in "$@"; do
    case "${argument}" in
        --skip-aliasing)
            skipAliasing=true
            ;;
        --os=linux|--os=wsl)
            operatingSystem=1
            ;;
        --os=mac|--os=macos)
            operatingSystem=2
            ;;
        *)
            1>&2 printf "\e[31mERROR:\e[0m Unknown option \"%s\", supported options are --os=linux, --os=mac, --os=wsl, and --skip-aliasing.\n" "${argument}"
            exit 1
            ;;
    esac
done

if [[ -z "${operatingSystem}" ]]; then
    kernelName="$(uname -s)"

    case "${kernelName}" in
        Darwin)
            detectedOperatingSystem=2
            detectedOperatingSystemName="macOS with Mutagen"
            ;;
        Linux)
            detectedOperatingSystem=1
            kernelRelease="$(cat /proc/sys/kernel/osrelease 2>/dev/null || true)"

            if [[ -n "${WSL_DISTRO_NAME}" ]] || echo "${kernelRelease}" | grep -qiE "microsoft|wsl"; then
                detectedOperatingSystemName="Windows with WSL"

                if ! echo "${kernelRelease}" | grep -qi "wsl2"; then
                    1>&2 printf "\e[33mWARNING:\e[0m WSL detected, but the kernel \"%s\" does not look like WSL 2.\n" "${kernelRelease}"
                    1>&2 echo "         Shopsys Platform requires WSL 2, run \"wsl.exe -l -v\" in Windows to check the version of your distribution."
                fi

                if [[ "$(pwd)" == /mnt/* ]]; then
                    1>&2 printf "\e[33mWARNING:\e[0m The project is placed in \"%s\", which is a Windows drive mounted into WSL.\n" "$(pwd)"
                    1>&2 echo "         The application would be extremely slow, move the project into the filesystem of your WSL distribution."
                fi
            else
                detectedOperatingSystemName="Linux"
            fi
            ;;
        *)
            detectedOperatingSystem=""
            1>&2 printf "\e[33mWARNING:\e[0m Unable to detect the operating system from kernel name \"%s\".\n" "${kernelName}"
            1>&2 echo "         Git Bash, MSYS and Cygwin are not supported, the installation should run inside a WSL 2 distribution."
            ;;
    esac

    if [[ -n "${detectedOperatingSystem}" ]]; then
        echo "Detected operating system: ${detectedOperatingSystemName} (${detectedOperatingSystem})"
        prompt="Press Enter to confirm the detected operating system, or enter OS number: "
    else
        prompt="Enter OS number: "
    fi
    echo "${operatingSystemsList}"

    if [[ -t 0 ]]; then
        while [[ 1 -eq 1 ]]
        do
            read -p "${prompt}" enteredOperatingSystem

            if [[ -z "${enteredOperatingSystem}" && -n "${detectedOperatingSystem}" ]]; then
                operatingSystem=${detectedOperatingSystem}
                break
            fi

            if [[ ${enteredOperatingSystem} =~ $numberRegex ]] ; then
                if [[ " ${allowedValues[@]} " =~ " ${enteredOperatingSystem} " ]]; then
                    operatingSystem=${enteredOperatingSystem}
                    break
                fi
                echo "Not existing value, please enter one of existing values"
            else
                echo "Please enter a number"
            fi
        done
    elif [[ -n "${detectedOperatingSystem}" ]]; then
        operatingSystem=${detectedOperatingSystem}
        echo "There is no interactive terminal available, continuing with the detected operating system."
    else
        1>&2 printf "\e[31mERROR:\e[0m Unable to detect the operating system and there is no interactive terminal to ask.\n"
        1>&2 echo "       Run the script again with --os=linux, --os=wsl or --os=mac."
        exit 1
    fi
fi

if [[ -d "project-base" ]]; then
    projectPathPrefix="project-base/app/"
    scriptsPathPrefix="project-base/"
    echo "You are in monorepo, prefixing paths app paths with ${projectPathPrefix}"
fi

echo "Creating domains_urls.yaml file"
cp -f "${projectPathPrefix}config/domains_urls.yaml.dist" "${projectPathPrefix}config/domains_urls.yaml"

echo "Creating docker configuration"
case "$operatingSystem" in
    "1")
        cp -f docker/conf/docker-compose.yml.dist docker-compose.yml

        sed -i -r "s#www_data_uid: [0-9]+#www_data_uid: $(id -u)#" ./docker-compose.yml
        sed -i -r "s#www_data_gid: [0-9]+#www_data_gid: $(id -g)#" ./docker-compose.yml
        sed -i -r "s#node_uid: [0-9]+#node_uid: $(id -u)#" ./docker-compose.yml
        sed -i -r "s#LOCAL_PATH_TO_PROJECT_ROOT: .*#LOCAL_PATH_TO_PROJECT_ROOT: $(pwd)#" ./docker-compose.yml

        echo "Starting docker compose"
        docker compose up -d --build --force-recreate
        ;;
    "2")
        if ! command -v mutagen &> /dev/null; then
            1>&2 printf "\e[31mERROR:\e[0m Mutagen is not installed. Please install it first by running:\n"
            1>&2 printf "    brew install mutagen-io/mutagen/mutagen\n"
            exit 1
        fi

        cp -f docker/conf/docker-compose-mac.yml.dist docker-compose.yml
        cp -f docker/conf/mutagen.yml.dist mutagen.yml

        sed -i '' -E "s#www_data_uid: [0-9]+#www_data_uid: $(id -u)#" ./docker-compose.yml
        sed -i '' -E "s#www_data_gid: [0-9]+#www_data_gid: $(id -g)#" ./docker-compose.yml
        sed -i '' -E "s#LOCAL_PATH_TO_PROJECT_ROOT: .*#LOCAL_PATH_TO_PROJECT_ROOT: $(pwd)#" ./docker-compose.yml
        sed -i '' -E "s#defaultOwner: \"id:501\"#defaultOwner: \"id:$(id -u)\"#g" ./mutagen.yml
        sed -i '' -E "s#defaultGroup: \"id:20\"#defaultGroup: \"id:$(id -g)\"#g" ./mutagen.yml
        sed -i '' -E "s#mutagenio/sidecar:[0-9.]+#mutagenio/sidecar:$(mutagen version)#g" ./docker-compose.yml

        if [[ "${skipAliasing}" != true ]]; then
            echo "You will be asked to enter sudo password in case to allow second domain alias in your system config"
            sudo ifconfig lo0 alias 127.0.0.2 up
        fi

        echo "Starting docker compose with Mutagen"
        ./${scriptsPathPrefix}scripts/mutagen-up.sh --build
        ;;
esac

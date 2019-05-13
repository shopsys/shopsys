projectPathPrefix=''
dockerComposeOS=''
allowedValues=(1 2 3)

mutagen daemon start
mutagen terminate -a

if [[ -d "project-base/" ]]; then
    projectPathPrefix="project-base/"
    echo "You are in monorepo, prefixing paths app paths with ${projectPathPrefix}"
fi

set -e

echo "Start with specifying your operating system: \

    1) Linux
    2) Mac
    3) Windows
    "

while [[ 1 -eq 1 ]]
do
    read -p "Enter OS number: " operatingSystem
    if [[ ${operatingSystem} =~ $numberRegex ]] ; then
        if [[ " ${allowedValues[@]} " =~ " ${operatingSystem} " ]]; then
            break;
        fi
        echo "Not existing value, please enter one of existing values"ke
    else
        echo "Please enter a number"
    fi
done

case "$operatingSystem" in
    "1")
        cp -f docker/conf/mutagen/docker-compose.yml.dist docker-compose.yml
        ;;
    "2")
        cp -f docker/conf/mutagen/docker-compose-mac.yml.dist docker-compose.yml
        ;;
    "3")
        cp -f docker/conf/mutagen/docker-compose-win.yml.dist docker-compose.yml
        ;;
esac

docker-compose down -v
docker-compose up -d
docker-compose exec webserver mkdir -p /var/www/html/${projectPathPrefix}web
docker-compose exec php-fpm mkdir -p /var/www/html/{vendor,${projectPathPrefix}node_modules}

mutagen create --ignore-vcs -i ./vendor \
    -i ./${projectPathPrefix}web \
    -i ./${projectPathPrefix}node_modules \
    ./ docker://www-data@shopsys-framework-php-fpm/var/www/html
mutagen create --ignore-vcs \
    -i ./${projectPathPrefix}node_modules \
    ./${projectPathPrefix}web docker://www-data@shopsys-framework-php-fpm/var/www/html/${projectPathPrefix}web
mutagen create ./vendor docker://www-data@shopsys-framework-php-fpm/var/www/html/vendor

# wait till all sync sessions are filled with files
mutagen flush -a

# move web content from php-fpm into webserver container
# no need to wait
mutagen create --sync-mode one-way-replica --ignore-vcs docker://www-data@shopsys-framework-php-fpm/var/www/html/${projectPathPrefix}web docker://root@shopsys-framework-webserver/var/www/html/${projectPathPrefix}web

### build process
# docker-compose exec php-fpm composer install -n
# docker-compose exec php-fpm php phing db-create test-db-create
# docker-compose exec php-fpm php phing build-demo-dev
### build process

#!/bin/bash -xe

## Configure docker-compose.yml

# Copy docker-compose.yml.dist into root of project
# Adds mounting of composer cache into php-fpm container using sed with regular expression
# Set UID and GID of jenkins user into docker-compose to assure correct rights for Jenkins
# Sed does not handle \n well, so we change format of new lines using tr and change it back at the end
cat $WORKSPACE/docker/conf/docker-compose.yml.dist |
	tr '\n' '\r' |
    sed -r 's#(php-fpm:(\r[^\r]+)+volumes:)(\s+- )#\1\3~/.composer:/home/www-data/.composer\3#' |
    sed "s#www_data_uid: 1000#www_data_uid: $(id -u)#" |
    sed "s#www_data_gid: 1000#www_data_gid: $(id -g)#" |
    tr '\r' '\n' > $WORKSPACE/docker-compose.yml

# Uniquely rename all containers based on $JOB_NAME
# e.g. 'container_name: shopsys-framework-webserver' => 'container_name: job-name-shopsys-framework-webserver'
sed -i "s/container_name:\s*\b/container_name: $JOB_NAME-/" $WORKSPACE/docker-compose.yml

# Use ports 80xx for "webserver", 44xx for "selenium-server" and 11xx for "adminer"
PORT_BASE_WEB=8000;
PORT_BASE_SELENIUM=4400;
PORT_BASE_ADMINER=1100;
PORT_BASE_REDIS_ADMIN=1600;
PORT_BASE_ELASTICSEARCH=9200;
PORT_BASE_LIVERELOAD_JAVASCRIPT=35729;

# Allow docker to choose available public port on hostmachine for each container
sed -i "s/\- \"$PORT_BASE_WEB\:*/\- \"/" $WORKSPACE/docker-compose.yml
sed -i "s/\- \"$PORT_BASE_SELENIUM\:*/\- \"/" $WORKSPACE/docker-compose.yml
sed -i "s/\- \"$PORT_BASE_ADMINER\:*/\- \"/" $WORKSPACE/docker-compose.yml
sed -i "s/\- \"$PORT_BASE_ELASTICSEARCH\:*/\- \"/" $WORKSPACE/docker-compose.yml
sed -i "s/\- \"$PORT_BASE_REDIS_ADMIN\:*/\- \"/" $WORKSPACE/docker-compose.yml

# remove LIVERELOAD exposed ports from php-fpm service since these are not needed for CI
sed -ni '$!N;/$PORT_BASE_LIVERELOAD_JAVASCRIPT:/!P;D' $WORKSPACE/docker-compose.yml

sed -i "s/\.\/project-base\/var\/postgres-data*/\/var\/postgres-data\/$JOB_NAME/" $WORKSPACE/docker-compose.yml

# Pull new images for microservices, if they were changed
/usr/local/bin/docker-compose pull $(/usr/local/bin/docker-compose config --services | grep microservice)

# Build containers and allocate ports on hostmachine
# ORPHAN is container with unlisted service in docker-compose file
/usr/local/bin/docker-compose up --build --force-recreate --remove-orphans -d

# store allocated port of webserver container in form of 0.0.0.0:0000
PORT_WEB=$(/usr/local/bin/docker-compose port webserver 8080)

# Configure nginx to redirect web access to the container

# Copy nginx configuration from a jenkins-wide template
# it contains definition of a proxy that redirects given URLs to the "webserver" container
# Note: The template is used by all jobs - changes in the template should be backward-compatible, or you can create a new template (effectively versioning it)
cp -f $WORKSPACE/project-base/.ci/docker/templates/nginx.conf /etc/nginx/conf.d/$JOB_NAME.conf

# Replace $DEVELOPMENT_SERVER_DOMAIN and $JOB_NAME and $PORT_WEB in the nginx configuration
sed -i "s/{{DEVELOPMENT_SERVER_DOMAIN}}/$DEVELOPMENT_SERVER_DOMAIN/" /etc/nginx/conf.d/$JOB_NAME.conf
sed -i "s/{{JOB_NAME}}*/$JOB_NAME/" /etc/nginx/conf.d/$JOB_NAME.conf
sed -i "s/{{PORT_WEB}}*/$PORT_WEB/" /etc/nginx/conf.d/$JOB_NAME.conf

# Reload nginx to apply the new configuration
# "jenkins" user has been allowed to run "nginx" command as super-user without password prompt via /etc/sudoers configuration
# see https://www.digitalocean.com/community/tutorials/how-to-edit-the-sudoers-file-on-ubuntu-and-centos#how-to-modify-the-sudoers-file
sudo nginx -s reload

## Parameters setup

# Note: We manually copy parameters.yml.dist to parameters.yml here
# because when "composer install" does it, it generates Yaml file
# that is not easily parsable by "sed".
cp $WORKSPACE/project-base/app/config/parameters.yml.dist $WORKSPACE/project-base/app/config/parameters.yml
cp $WORKSPACE/project-base/app/config/parameters_test.yml.dist $WORKSPACE/project-base/app/config/parameters_test.yml

## Domain URLs setup

# Copy domains_urls.yml from the template
cp $WORKSPACE/project-base/app/config/domains_urls.yml.dist $WORKSPACE/project-base/app/config/domains_urls.yml

# Fetch all domain IDs
DOMAIN_IDS=$(cat $WORKSPACE/project-base/app/config/domains_urls.yml|grep -Po 'id: ([0-9]+)$'|sed 's/id: \([0-9]\+\)/\1/')

# Modify public URLs to $DOMAIN_ID.$JOB_NAME.$DEVELOPMENT_SERVER_DOMAIN ($DOMAIN_ID is ommited for first domain)
for DOMAIN_ID in $DOMAIN_IDS; do
    if [ "$DOMAIN_ID" == "1" ]; then
        # 1st domain has URL without number prefix
        sed -i "/id: 1/,/url:/{s/url:.*/url: http:\/\/$JOB_NAME.$DEVELOPMENT_SERVER_DOMAIN/}" $WORKSPACE/project-base/app/config/domains_urls.yml
    else
        # 2nd and subsequent domains have URLs with DOMAIN_ID prefix
        sed -i "/id: $DOMAIN_ID/,/url:/{s/url:.*/url: http:\/\/$DOMAIN_ID.$JOB_NAME.$DEVELOPMENT_SERVER_DOMAIN/}" $WORKSPACE/project-base/app/config/domains_urls.yml
    fi
done

# launch staging build
/usr/bin/docker exec $JOB_NAME-shopsys-framework-php-fpm ./phing clean composer-dev npm timezones-check dirs-create assets db-migrations create-domains-data generate-friendly-urls replace-domains-urls grunt error-pages-generate tests-acceptance-build checks-ci

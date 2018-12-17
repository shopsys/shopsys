#!/bin/sh

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

# remove exposed port for webserver and use any available one
PORT_BASE_WEB=8000;

# Allow docker to choose available public port on host machine for each container
# think about to remove ports except webserver service
sed -i "s/\- \"$PORT_BASE_WEB\:*/\- \"/" $WORKSPACE/docker-compose.yml

# persist postgres data for future use by mounting them outside the running container
sed -i "s/\.\/project-base\/var\/postgres-data*/\/var\/postgres-data\/$JOB_NAME/" $WORKSPACE/docker-compose.yml

# Build containers and allocate ports on hostmachine
# ORPHAN is container with unlisted service in docker-compose file
/usr/local/bin/docker-compose up --build --force-recreate --remove-orphans -d

# store allocated port of webserver container into $PORT_WEB variable
PORT_WEB=$(/usr/local/bin/docker-compose port webserver 8080)

# Configure nginx to redirect web access to the container
# Copy nginx configuration from a jenkins-wide template
# it contains definition of a proxy that redirects given URLs to the "webserver" container
# Note: The template is used by all jobs - changes in the template should be backward-compatible, or you can create a new template (effectively versioning it)
cp -f $WORKSPACE/.ci/docker/templates/nginx.conf /etc/nginx/conf.d/$JOB_NAME.conf

# Replace $SERVER_VIRTUAL_HOST and $JOB_NAME and $PORT_WEB in the nginx configuration
sed -i "s/{{SERVER_VIRTUAL_HOST}}/$SERVER_VIRTUAL_HOST/" /etc/nginx/conf.d/$JOB_NAME.conf
sed -i "s/{{JOB_NAME}}/$JOB_NAME/" /etc/nginx/conf.d/$JOB_NAME.conf
sed -i "s/{{PORT_WEB}}/$PORT_WEB/" /etc/nginx/conf.d/$JOB_NAME.conf

# Reload nginx to apply the new configuration
# "jenkins" user has been allowed to run "nginx" command as super-user without password prompt via /etc/sudoers configuration
# see https://www.digitalocean.com/community/tutorials/how-to-edit-the-sudoers-file-on-ubuntu-and-centos#how-to-modify-the-sudoers-file
sudo nginx -s reload

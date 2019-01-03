#!/bin/sh

# destroy containers and allocated ports on hostmachine
# ORPHAN is container with unlisted service in docker-compose file
/usr/local/bin/docker-compose down --volumes --remove-orphans

# Remove virtual host configuration for current job
rm -rf /etc/nginx/conf.d/$JOB_NAME.conf

# Reload nginx to disable virtual host for current job
# "jenkins" user has been allowed to run "nginx" command as super-user without password prompt via /etc/sudoers configuration
# see https://www.digitalocean.com/community/tutorials/how-to-edit-the-sudoers-file-on-ubuntu-and-centos#how-to-modify-the-sudoers-file
sudo nginx -s reload

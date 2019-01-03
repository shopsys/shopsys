#!/bin/sh -xe

# Make SSFW single domain

# remove all domain URLs except URLs for domain with ID more than 1 in app/config/domains.yml
sed '/id: 2/{:a;Q}' $WORKSPACE/project-base/app/config/domains_urls.yml.dist > $WORKSPACE/project-base/app/config/domains_urls.yml
sed -i '/id: 2/{:a;Q}' $WORKSPACE/project-base/app/config/domains.yml

# set "is-multidomain" property to "false" in build.xml
sed -i 's/<property name="is-multidomain".*/<property name="is-multidomain" value="false" \/>/' $WORKSPACE/project-base/build.xml

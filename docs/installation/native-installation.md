# Native Installation

This document will provide you with the general information that is needed for running Shopsys Framework on different operation systems like (Windows, Mac, Linux, ... ), however it is not a step-by-step guide, since it would be very difficult to maintain all operation systems and their versions.

First it is truly essential to read and understand the articles about requirements and configurations for Shopsys Framework application.

1. [Application Requirements](application-requirements.md)
1. [Application Configuration](application-configuration.md)
1. [Native Installation Troubleshooting](native-installation-troubleshooting.md)

After you read the articles you are ready to start with the creating and building the Shopsys Framework project.

## Create new project from Shopsys Framework sources

```sh
php -d memory_limit=-1 <PATH TO COMPOSER or COMPOSER.PHAR> create-project shopsys/project-base --keep-vcs
```

!!! note "Notes"
    - The `--keep-vcs` option initializes GIT repository in your project folder that is needed for diff commands of the application build and keeps the GIT history of `shopsys/project-base`
    - We have set memory limit to `-1` for composer because of the increased memory consumption during the dependencies calculation

!!! note
    During the execution of `composer create-project`, there will be installed 3-rd party software as dependencies of Shopsys Framework by [composer](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies) with licenses that are described in document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/9.0/open-source-license-acknowledgements-and-third-party-copyrights.md)

## Create databases

```sh
php phing db-create
php phing test-db-create
```

!!! hint
    In this step you were using multiple Phing targets.  
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md)

## Build application

```sh
php phing build-demo-dev
```

!!! note
    During the execution of `build-demo-dev phing target`, there will be installed 3-rd party software as dependencies of Shopsys Framework by [composer](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies) and [npm](https://docs.npmjs.com/about-the-public-npm-registry) with licenses that are described in document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/9.0/open-source-license-acknowledgements-and-third-party-copyrights.md)

## Run integrated HTTP server

```sh
php bin/console server:run
```

!!! note
    you will be prompted for starting one of the 2 localised domains

!!! tip
    you can use Nginx service instead of integrated server, in that case let be inspired by [nginx configuration](https://github.com/shopsys/shopsys/blob/master/project-base/docker/nginx/nginx.conf)

## See it in your browser!

Open [http://127.0.0.1:8000/](http://127.0.0.1:8000/) to see running application.

You can also login into the administration section on [http://127.0.0.1:8000/admin/](http://127.0.0.1:8000/admin/) with default credentials:

* Username: `admin` or `superadmin` (the latter has access to advanced options)
* Password: `admin123`

*And now you can [start building your application](../introduction/start-building-your-application.md).*

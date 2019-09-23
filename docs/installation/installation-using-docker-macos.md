# Installation Using Docker for MacOS

**This guide is for the version which is not released yet. See the [version for `v8.0.0`](https://github.com/shopsys/shopsys/blob/v8.0.0/docs/installation/installation-using-docker-macos.md).**

This guide covers building new projects based on Shopsys Framework.
If you want to contribute to the framework itself,
you need to install the whole [shopsys/shopsys](https://github.com/shopsys/shopsys) monorepo.
Take a look at the article about [Monorepo](../introduction/monorepo.md) for more information.

This solution uses [*docker-sync*](http://docker-sync.io/) (for relatively fast two-way synchronization of the application files between the host machine and Docker volume).

!!! warning
    Docker-sync might be a burden for intensive project development, especially when there is a huge amount of files in shared volumes of virtualized docker and when switching between branches or even between projects often. In such a case, you should consider using [native installation](./native-installation.md).

## Requirements
* [GIT](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
* [PHP](http://php.net/manual/en/install.macosx.php)
    * At least version **7.2 or higher**
* [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
* [Docker for Mac](https://docs.docker.com/engine/installation/)
    * Docker-sync suggests ([in known issue](https://github.com/EugenMayer/docker-sync/issues/517)) to use Docker for Mac in version 17.09.1-ce-mac42 (21090)
    * Docker for Mac requires at least 4 GB of memory, otherwise, `composer install` can result in `Killed` status (we recommend to set 2 GB RAM, 1 CPU and 2 GB Swap in `Docker -> Preferences… -> Advanced`)
    * Version of Docker Engine should be at least **17.05 or higher** so it supports [multi-stage builds](https://docs.docker.com/develop/develop-images/multistage-build/).
    * Version of Docker Compose should be at least **1.17.0 or higher** because we use compose file version `3.4`
* [Docker-sync](http://docker-sync.io/) (install via `sudo gem install docker-sync`)

## Steps
### 1. Create new project from Shopsys Framework sources
```sh
composer create-project shopsys/project-base --no-install --keep-vcs
cd project-base
```

!!! note "Notes"
    - The `--no-install` option disables installation of the vendors - this will be done later in the Docker container
    - The `--keep-vcs` option initializes GIT repository in your project folder that is needed for diff commands of the application build and keeps the GIT history of `shopsys/project-base`

### 2. Installation
Now, you have two options:

#### Option 1
In the case you want to start demo of the application as fast as possible, you can simply execute the installation script and that is all:
```
./scripts/install.sh
```
!!! note
    `--skip-aliasing` may be used in case you have already enabled second domain or you do not want to enable it for some reason. When using this option you will not be asked for sudo password.

After the script is finished with installing the application, you can skip all the other steps and see [the last chapter of Application Setup Guide](./installation-using-docker-application-setup.md#2-see-it-in-your-browser) to get all the important information you might need right after the installation.

#### Option 2
If you want to know more about what is happening during installation, continue with the steps [#2.1 - #2.5](#21-enable-second-domain-optional).

#### 2.1 Enable second domain (optional)
There are two domains each for different language in default installation. First one is available via IP adress `127.0.O.1` and second one via `127.0.0.2`.
`127.0.0.2` is not alias of `127.0.0.1` on Mac by default. To create this alias in network interface run:
```sh
sudo ifconfig lo0 alias 127.0.0.2 up
```

#### 2.2. Create docker-compose.yml and docker-sync.yml
Create `docker-compose.yml` from template [`docker-compose-mac.yml.dist`](https://github.com/shopsys/shopsys/blob/master/project-base/docker/conf/docker-compose-mac.yml.dist).
```sh
cp docker/conf/docker-compose-mac.yml.dist docker-compose.yml
```

Create `docker-sync.yml` from template [`docker-sync.yml.dist`](https://github.com/shopsys/shopsys/blob/master/project-base/docker/conf/docker-sync.yml.dist).

```sh
cp docker/conf/docker-sync.yml.dist docker-sync.yml
```

#### 2.3 Set the UID and GID to allow file access in mounted volumes
Because we want both the user in host machine (you) and the user running php-fpm in the container to access shared files, we need to make sure that they both have the same UID and GID.
This can be achieved by build arguments `www_data_uid` and `www_data_gid` that should be set to the same UID and GID as your own user in your `docker-compose.yml`.
Also, you need to change `sync_userid` in `docker-sync.yml` file.

You can find out your UID by running `id -u` and your GID by running `id -g`.

Once you get these values, set these values into your `docker-compose.yml` into `php-fpm` container definition by replacing values in `args` section.

Also you need to insert your UID into `docker-sync.yml` into value `sync_userid`.

#### 2.4 Compose Docker container
On MacOS you need to synchronize folders using docker-sync.
Before starting synchronization you need to create a directory for persisting Postgres and Elasticsearch data so you won't lose it when the container is shut down.

```sh
mkdir -p var/postgres-data var/elasticsearch-data vendor
docker-sync start
```

Then rebuild and start containers

```sh
docker-compose up -d --build
```

!!! note
    During the build of the docker containers there will be installed 3-rd party software as dependencies of Shopsys Framework by [Dockerfile](https://docs.docker.com/engine/reference/builder/) with licenses that are described in document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/master/open-source-license-acknowledgements-and-third-party-copyrights.md)

#### 2.5 Setup the application
[Application setup guide](installation-using-docker-application-setup.md)

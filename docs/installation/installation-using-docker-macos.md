# Installation Using Docker Desktop on macOS

This guide covers building new projects based on Shopsys Platform.
If you want to contribute to the Shopsys Platform itself,
you need to install the whole [shopsys/shopsys](https://github.com/shopsys/shopsys) monorepo.
Take a look at the article about [Monorepo](../introduction/monorepo.md) for more information.

This solution uses [*Mutagen*](https://mutagen.io) (for relatively fast two-way synchronization of the application files between the host machine and Docker volume).

## Requirements
* [GIT](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
* [PHP](http://php.net/manual/en/install.macosx.php)
    * At least version **8.1 or higher**
* [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
* [Docker Desktop](https://docs.docker.com/engine/install/)
     * Enable Docker Compose V2 in General settings 
     * We recommend to set at least 8 GB RAM, 4 CPUs and 512 MB Swap in `Docker -> Preferences… -> Resources -> ADVANCED`
* [Mutagen](https://mutagen.io/) (install using [Mutagen installation guide](https://mutagen.io/documentation/introduction/installation))
* [Mutagen Compose](https://mutagen.io/documentation/orchestration/compose/) (install using [Mutagen Compose installation guide](https://github.com/mutagen-io/mutagen-compose#installation))

## Steps
### 1. Create new project from Shopsys Platform sources
```sh
composer create-project shopsys/project-base --no-install --keep-vcs --ignore-platform-reqs
cd project-base
```

!!! note "Notes"
    - The `--no-install` option disables installation of the vendors - this will be done later in the Docker container
    - The `--keep-vcs` option initializes GIT repository in your project folder that is needed for diff commands of the application build and keeps the GIT history of `shopsys/project-base`
    - The `--ignore-platform-reqs` option ensures your local PHP setup is not verified (it is not needed, everything is installed in Docker later)

### 2. Installation
Now, you have two options:

#### Option 1
In the case you want to start demo of the application as fast as possible, you can simply execute the installation script and that is all:
```
./scripts/install.sh
```
!!! note
    `--skip-aliasing` may be used in case you have already enabled second domain, or you do not want to enable it for some reason. When using this option you will not be asked for sudo password.

After the script is finished with installing the application, you can skip all the other steps and see [the last chapter of Application Setup Guide](./installation-using-docker-application-setup.md#2-see-it-in-your-browser) to get all the important information you might need right after the installation.

#### Option 2
If you want to know more about what is happening during installation, continue with the steps [#2.1 - #2.5](#21-enable-second-domain-optional).

#### 2.1 Enable second domain (optional)
There are two domains each for different language in default installation. First one is available via IP adress `127.0.O.1` and second one via `127.0.0.2`.
`127.0.0.2` is not alias of `127.0.0.1` on Mac by default. To create this alias in network interface run:
```sh
sudo ifconfig lo0 alias 127.0.0.2 up
```

#### 2.2. Create docker-compose.yml
Create `docker-compose.yml` from template [`docker-compose-mac.yml.dist`](https://github.com/shopsys/shopsys/blob/master/project-base/docker/conf/docker-compose-mac.yml.dist).
```sh
cp docker/conf/docker-compose-mac.yml.dist docker-compose.yml
```

#### 2.3 Set the UID and GID to allow file access in mounted volumes
Because we want both the user in host machine (you) and the user running php-fpm in the container to access shared files, we need to make sure that they both have the same UID and GID.
This can be achieved by build arguments `www_data_uid` and `www_data_gid` that should be set to the same UID and GID as your own user in your `docker-compose.yml`.
You can find out your UID by running `id -u` and your GID by running `id -g`.
Once you get these values, set these values into your `docker-compose.yml` into `php-fpm` container definition by replacing values in `args` section.
Update also `defaultOwner` to your UID in `x-mutagen` section in `docker-compose.yml`.

#### 2.4 Build and start containers using Mutagen
On macOS, you want to synchronize folders using Mutagen as it enables faster performance then current implementation in Docker Desktop.

```sh
mutagen-compose up -d --build
```

!!! note
    With Mutagen Compose you will use `mutagen-compose` instead of `docker-compose` for all your Docker Compose commands.
    `mutagen-compose` is a wrapper around `docker-compose` that adds Mutagen synchronization to the `docker-compose up` command.

!!! note
    During the build of the Docker containers there will be installed 3-rd party software as dependencies of Shopsys Platform by [Dockerfile](https://docs.docker.com/engine/reference/builder/) with licenses that are described in document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/master/open-source-license-acknowledgements-and-third-party-copyrights.md)

#### 2.5 Set up the application
[Application setup guide](installation-using-docker-application-setup.md)

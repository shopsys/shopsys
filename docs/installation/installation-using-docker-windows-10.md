# Installation Using Docker on Windows 10
**Expected installation time:** less than 1 hour.

This guide covers building new projects based on Shopsys Platform.
In case you want to contribute to the Shopsys Platform itself, you need to install the whole [shopsys/shopsys](https://github.com/shopsys/shopsys) monorepo.
Take a look at the article about [Monorepo](../introduction/monorepo.md) for more information.

## Supported systems
- Windows 10 version 2004 and higher

## System requirements
- enabled Virtualization in BIOS

## Installation of application requirement

### Setup WSL 2 and download Debian
!!! note
    This guide covers installation using `Debian` distribution. Rest of guide might not be suitable for you, if you decide to select different distribution.

In [Windows Subsystem for Linux Installation Guide for Windows 10](https://docs.microsoft.com/en-us/windows/wsl/install-win10#manual-installation-steps) follow steps 1 to 5.
Select `Debian GNU/Linux` distribution in step 6.

After installation open `Debian` application.
You will be prompted to create new user.

### Install Docker Desktop
* Download [Docker Desktop](https://docs.docker.com/docker-for-windows/install/) and install it.

### Enable WSL 2 and Debian integration in Docker Desktop
* Open settings of Docker Desktop.
* Go to `General` section.
* Check `Expose daemon on tcp://localhost:2375 without TLS` option.
* Check `Use the WSL 2 based engine` option.
* Click `Apply & Restart` button.
* Go to `Resources` section and then to `WSL INTEGRATION` subsection.
* Check `Enable integration with my default WSL distro` option.
* Also turn on `Debian` under `Enable integration with additional distros`.
* Click `Apply & Restart` button.

### Install dependencies, Docker and docker-compose in Debian
We have prepared installation script for Debian dependencies in order to speed up the installation process.
In `Debian` application run these commands:
* `sudo apt update`
* `sudo apt install -y --no-install-recommends wget`
* `wget --no-check-certificate https://raw.githubusercontent.com/shopsys/shopsys/master/project-base/scripts/install-docker-wsl-debian.sh`
* `sudo bash install-docker-wsl-debian.sh`
* You can delete the downloaded file at the end of installation using `rm install-docker-wsl-debian.sh` command.
* To ensure everything is working alright, restart `Docker Desktop` and `Debian` applications.

!!! note
    If you prefer to know more about what is happening during installation, open [install-docker-wsl-debian.sh](https://github.com/shopsys/shopsys/master/project-base/scripts/install-docker-wsl-debian.sh) script and run all commands manually.

## Installation of Shopsys Platform

### 1. Create new project from Shopsys Platform sources
Open `Debian` application.

Change your current directory to your home directory (`/home/<your-linux-user-name>/`)

```sh
cd ~/
```

Install project with composer

```sh
composer create-project shopsys/project-base --no-install --keep-vcs --ignore-platform-reqs
cd project-base
```

!!! note
    Now you can access your project files directly from Windows Explorer under this address `\\wsl$\Debian\home\<your-linux-user-name>\project-base\`

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

Select `Linux or Windows with WSL 2` from list of operating systems as you are installing it in `Debian`.

After the script is finished with installing the application, you can skip all the other steps and see [the last chapter of Application Setup Guide](./installation-using-docker-application-setup.md#2-see-it-in-your-browser) to get all the important information you might need right after the installation.

#### Option 2
If you prefer to know more about what is happening during installation, continue with the steps [#2.1 - #2.3](#21-create-docker-composeyml-and-docker-syncyml-file).

#### 2.1 Create docker-compose.yml and docker-sync.yml file
Create `docker-compose.yml` from template [`docker-compose.yml.dist`](https://github.com/shopsys/shopsys/blob/master/project-base/docker/conf/docker-compose.yml.dist).

```sh
cp docker/conf/docker-compose.yml.dist docker-compose.yml
```

#### 2.2. Compose Docker container
You need to create a directory for persisting vendor, PostgreSQL and Elasticsearch data so you won't lose them when the container is shut down.

```sh
mkdir -p vendor var/postgres-data var/elasticsearch-data
```

Then rebuild and start containers
```sh
docker-compose up -d
```

!!! note
    During installation there will be installed 3-rd party software as dependencies of Shopsys Platform by [Dockerfile](https://docs.docker.com/engine/reference/builder/) with licenses that are described in document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/master/open-source-license-acknowledgements-and-third-party-copyrights.md)

#### 2.3 Setup the application
[Application setup guide](installation-using-docker-application-setup.md)

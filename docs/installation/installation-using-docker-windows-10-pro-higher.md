# Installation Using Docker for Windows 10 Pro and higher

**Expected installation time:** 3 hours.

This guide covers building new projects based on Shopsys Framework.
If you want to contribute to the framework itself,
you need to install the whole [shopsys/shopsys](https://github.com/shopsys/shopsys) monorepo.
Take a look at the article about [Monorepo](../introduction/monorepo.md) for more information.

This solution uses [*docker-sync*](http://docker-sync.io/) (for relatively fast two-way synchronization of the application files between the host machine and Docker volume).

!!! warning
    Docker-sync might be a burden for intensive project development, especially when there is a huge amount of files in shared volumes of virtualized docker and when switching between branches or even between projects often. In such a case, you should consider using [native installation](./native-installation.md).

## Supported systems
- Windows 10 Pro
- Windows 10 Enterprise
- Windows 10 Education

## Requirements
* [GIT](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
* [PHP](http://php.net/manual/en/install.windows.php)
    * At least version **7.2 or higher**
* [Docker for Windows](https://docs.docker.com/docker-for-windows/install/)
    * Docker for Windows requires at least 4 GB of memory, otherwise, `composer install` can result in `Killed` status (we recommend to set 2 GB RAM, 1 CPU and 2 GB Swap in `Docker -> Preferences… -> Advanced`)
    * Version of Docker Engine should be at least **17.05 or higher** so it supports [multi-stage builds](https://docs.docker.com/develop/develop-images/multistage-build/).
    * Version of Docker Compose should be at least **1.17.0 or higher** because we use compose file version `3.4`
* [Docker-sync](http://docker-sync.io/) (installation guide [see below](./installation-using-docker-windows-10-pro-higher.md/#installation-of-docker-sync-for-windows))

### Installation of Docker-sync for Windows

!!! warning
    Be aware of using custom firewalls or protection tools other than default `Windows Defender`, we experienced that some of them make synchronization malfunctioning because of blocking synchronization ports.
    To speed up the synchronization and developing faster, you can exclude folder from indexing and search path of `Windows Defender`.

#### Prerequisites
In settings of Windows docker check `Expose daemon on localhost:2375` in `General` tab and check drive option in `Shared Drives` tab, where the project will be installed, you will be prompted for your Windows credentials.

#### Enable WSL
Open the `Windows Control Panel`, `Programs and Features`, click on the left on `Turn Windows features on or off` and check `Windows Subsystem for Linux` near the bottom, restart of Windows is required.

#### Install Debian app
Install `Debian` app form `Microsoft Store` and launch it, so console window is opened.

#### Execute following commands in console window.

Update linux packages so system will be up to date to install packages needed for running docker-sync.
```sh
sudo apt update
```

Now install the tools needed for adding package repositories from which the system will be able to download docker, docker-sync and unison synchronization strategy driver.
```sh
sudo apt install -y --no-install-recommends apt-transport-https ca-certificates curl gnupg2 software-properties-common
```

Add repository for docker, then install it and configure environment variable for connecting to Windows docker.
```sh
curl -fsSL https://download.docker.com/linux/debian/gpg | sudo apt-key add -
sudo apt-key fingerprint 0EBFCD88
sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable"
sudo apt update
sudo apt install -y --no-install-recommends docker-ce
echo "export DOCKER_HOST=tcp://127.0.0.1:2375" >> ~/.bashrc && source ~/.bashrc
```

Install docker-compose tool that will help us to launch containers via `docker-compose.yml` configuration file.
```sh
sudo curl -L "https://github.com/docker/compose/releases/download/1.22.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

Install ruby as installation tool for docker-sync in specific version for working unison synchronization strategy driver.
```sh
sudo apt install -y --no-install-recommends ruby ruby-dev
sudo gem install docker-sync
```

Download, compile and install unison driver.
```sh
sudo apt install -y --no-install-recommends build-essential make
wget -qO- http://caml.inria.fr/pub/distrib/ocaml-4.06/ocaml-4.06.0.tar.gz | tar xvz
cd ocaml-4.06.0
./configure
make world opt
umask 022
sudo make install clean
wget -qO- https://github.com/bcpierce00/unison/archive/v2.51.2.tar.gz | tar xvz
cd unison-2.51.2
make UISTYLE=text
sudo cp src/unison /usr/local/bin/unison
sudo cp src/unison-fsmonitor /usr/local/bin/unison-fsmonitor

# remove sources of sync tools
cd ../..
rm -rf ocaml-4.06.0 *.tar.gz
```

Set timezone of the system as docker-sync requirement.
```sh
sudo dpkg-reconfigure tzdata
```

Set WSL init script for mounting of computer drives from root path `/` instead of `/mnt` path.
```sh
echo -e [automount]\\nenabled = true\\nroot = /\\noptions = \"metadata,umask=22,fmask=11\" | sudo dd of=/etc/wsl.conf
```

Add valid Debian repository for php version that is used by composer and install composer.
```sh
sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list

sudo apt update
sudo apt install -y --no-install-recommends composer git
```

Close console window and open it again so the new configuration is loaded.

## Shopsys Framework Installation
### 1. Create new project from Shopsys Framework sources
After WSL installation use linux console for each command.  
Pick path of some directory in Windows filesystem and move into it, for example documents of windows user `myuser` so path will be like this.
```sh
cd /c/Users/myuser/Documents/
```

Install project with composer.
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
After the script is finished with installing the application, you can skip all the other steps and see [the last chapter of Application Setup Guide](./installation-using-docker-application-setup.md#2-see-it-in-your-browser) to get all the important information you might need right after the installation.

#### Option 2
If you want to know more about what is happening during installation, continue with the steps [#2.1 - #2.3](#21-create-docker-composeyml-and-docker-syncyml-file).

#### 2.1 Create docker-compose.yml and docker-sync.yml file
Create `docker-compose.yml` from template [`docker-compose-win.yml.dist`](https://github.com/shopsys/shopsys/blob/master/project-base/docker/conf/docker-compose-win.yml.dist).
```sh
cp docker/conf/docker-compose-win.yml.dist docker-compose.yml
```

Create `docker-sync.yml` from template [`docker-sync-win.yml.dist`](https://github.com/shopsys/shopsys/blob/master/project-base/docker/conf/docker-sync-win.yml.dist).
```sh
cp docker/conf/docker-sync-win.yml.dist docker-sync.yml
```

#### 2.2. Compose Docker container
On Windows you need to synchronize folders using docker-sync.
Before starting synchronization you need to create a directory for persisting Vendor data so you won't lose it when the container is shut down.
```sh
mkdir -p vendor
docker-sync start
```

Then rebuild and start containers
```sh
docker-compose up -d
```

!!! note
    During installation there will be installed 3-rd party software as dependencies of Shopsys Framework by [Dockerfile](https://docs.docker.com/engine/reference/builder/) with licenses that are described in document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/master/open-source-license-acknowledgements-and-third-party-copyrights.md)

#### 2.3 Setup the application
[Application setup guide](installation-using-docker-application-setup.md)

# Docker Troubleshooting

1. [How to Run Multiple Projects by Docker](#how-to-run-multiple-projects-by-docker)
    1. [Multiple Projects - Quick Solution - Only One Project Running at the Time](#multiple-projects---quick-solution---only-one-project-running-at-the-time)
    1. [Multiple Projects - Long Term Solution](#multiple-projects---long-term-solution)
1. [Update of Dockerfile is not Reflected](#update-of-dockerfile-is-not-reflected)
1. [Update of Docker-compose is not Reflected](#update-of-docker-compose-is-not-reflected)
1. [Docker-sync stopped to sync files](#docker-sync-stopped-to-sync-files)
1. [Application is slow on Mac](#application-is-slow-on-mac)
1. [A Docker container is not running](#a-docker-container-is-not-running)
1. [Composer dependencies installation fails on memory limit](#composer-dependencies-installation-fails-on-memory-limit)
1. [Starting up the Docker containers fails due to invalid reference format](#starting-up-the-docker-containers-fails-due-to-an-invalid-reference-format)

If you are developing on Shopsys Platform using Docker, you might run into some problems during the process.

Most of the time, you might think that a problem is in Docker, but the truth is that you are probably using it wrong. This document
provides advice to help you develop Shopsys Platform on Docker without problems.

## How to Run Multiple Projects by Docker

If you use Docker for more than one Shopsys Platform project, you might run into a problem with container names and their ports.
Docker requires a unique container name and port for each container, and since our `docker compose` is not dynamically initialized,
it contains hard-coded container names and ports, and that makes running more projects in Docker on the same machine impossible without
modifying your configuration.

With that being said, we have two options to solve this problem.

### Multiple Projects - Quick Solution - Only One Project Running at the Time

This solution is simpler and is used if we only need one project running at the time.

All we really need to do is to properly turn off `docker compose`.

Usually, everyone shuts off their `docker compose` by running `docker compose stop`, which is not the correct way.

This command is used to stop containers, not to delete them. That means that if you now try to start Docker compose
in another project, it will output an error that there already are containers with those names.
That is true because these stopped containers are still registered in memory.

To properly delete your workspace containers, run:

```sh
docker compose down
```

This will not only stop the containers but also delete them. This means that containers and all their data in volumes will be deleted.
Now, you can use the same configuration in other projects, and it will work.

### Multiple Projects - Long-Term Solution

This solution is more viable for someone who really needs to have projects ready to run in a few seconds and often ends up having
two or more projects running at the same time. So what if we don't always want to reinstall whole containers, and we want our data to persist in volumes?

Earlier, we said that Docker needs to have unique container names and ports.

So, how about changing their name?
We recommend replacing `shopsys-framework` with your project name. For instance, php-fpm container that is defaultly named as
`shopsys-framework-php-fpm` would now be named `my-project-name-php-fpm`.

This would actually work, only if you always downed `docker compose` before switching between projects.
Because it would try to locate our localhost ports to the same values, which would fail.

So we need to change the ports of the containers. Containers have their ports defined in this format.

```
8000:8000
```

The first defines the port exposed on our local computer, and the second is for the Docker network. Since with every start of
Docker compose, Docker creates the new network, and that isolates each project from each other, we do not need to care about the second port.
We actually just need to allocate the first port to a free port on our local system.

Since we are trying to change ports on your local machine, there is a chance that you will pick a port that is already allocated for something else running on your computer.
You can check all of your taken ports using `netstat` (for MacOs `lsof`).

```sh
netstat -ltn
```

This will output all listening TCP ports in numeric format. Now we can just pick one that isn't in this list and set it to our container.

!!! warning

    Try not to use ports between 1000-1100. These are ports that root usually uses for its processes.

So, we have configured our `docker-compose` files in a way they do not have any conflicts among them.
That way, we can have as many projects running at the same time as many ports there are in our local network.

Remember that after changing these, you need to do a few things differently.

-   You changed the `port` of the webserver container, which affects the domain URL, so you need to change ports in `domains_urls.yaml`.
-   You changed the `container_name` of php-fpm, which means that in order to get inside the php-fpm container, you must now use this name.
    for instance, if your new container name is `my-new-project-name-php-fpm`, you need to execute

```sh
docker exec -it my-new-project-name-php-fpm bash
```

## Update of Dockerfile is not Reflected

Sometimes, there is a need to change the dockerfile for one of our images.
If we already had a project running once in Docker, there is probably a cached image for the container.

That means that Docker does not really check if there is a change in the dockerfile,
it will always build a container by cached image. We need to rebuild our containers.
First, we need to stop our containers in `docker compose` because we cannot update containers that are already in use:

```sh
docker compose stop
```

Then we need to force Docker to rebuild our containers:

```sh
docker compose build
```

Docker has now updated our containers and we can continue as usual with:

```sh
docker compose up -d
```

## Update of Docker-compose is not Reflected

Docker compose is much easier to change than images. If we change anything in `docker compose`, we just need to recreate `docker compose`.
That is done by executing:

```sh
docker compose up -d --force-recreate
```

## Docker-sync stopped to sync files

Docker-sync suggests ([in known issue](https://github.com/EugenMayer/docker-sync/issues/517)) to use Docker for Mac in version 17.09.1-ce-mac42 (21090).
This version helped most people to solve their issues with syncing.

You may sometimes encounter a sync problem even with the suggested version of Docker. In those cases, you need to recreate docker-sync containers. Here are two easy steps you have to follow:

Delete your docker-sync containers and volumes (data on your host will not be removed):

```sh
docker-sync clean
```

Start docker-sync so your docker-sync containers and volumes will be recreated:

```sh
docker-sync start
```

## Application is slow on Mac

We focus on enhancing the performance of the application on all platforms.
There are known some performance issues with Docker for Mac and Docker for Windows because all project files need to be synchronized from the host computer to the application running in a virtual machine.
On Mac, we partially solved this by implementing docker-sync.
Docker-sync has some limits, and that is the reason why we use Docker native volumes for syncing PostgreSQL and Elasticsearch data to ensure the data persistence.
In some cases, performance can be more important than the persistence of the data.
In this case, you can increase the performance by deleting these volumes in your `docker-compose.yml` file, but that will result in loss of persistence, which means that the data will be lost after the removal of the container, e.g., during `docker compose down`.

## A Docker container is not running

You can inspect what is wrong by using `docker logs <container-name>` command.

## Composer dependencies installation fails on memory limit

When `composer install` or `composer update` fails on an error with exceeding the allowed memory size, you can increase the memory limit by setting `COMPOSER_MEMORY_LIMIT` environment variable in your `docker/php-fpm/Dockerfile` or `docker-compose.yml`.

!!! note

    Since `v7.0.0-beta4`, we have set the Composer memory limit to `-1` (which means unlimited) in the php-fpm's `Dockerfile`.<br>
    If you still encounter memory issues while using Docker for Windows (or Mac), try increasing the limits in `Docker -> Preferences… -> Advanced`.

!!! note

    Composer dependencies contain 3-rd party software with licenses that are described in the document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/master/open-source-license-acknowledgements-and-third-party-copyrights.md)

## Starting up the Docker containers fails due to an invalid reference format

Docker images may fail to build during `docker compose up -d` due to invalid reference format, e.g.:

```no-highlight
Building php-fpm
Step 1/41 : FROM php:8.3-fpm-bullseye as base
ERROR: Service 'php-fpm' failed to build: Error parsing reference: "php:8.3-fpm-bullseye as base" is not a valid repository/tag: invalid reference format
```

This is because you have a version of Docker that does not support [multi-stage builds](https://docs.docker.com/develop/develop-images/multistage-build/).

Upgrade your Docker to version **17.05 or higher** and try running the command again.

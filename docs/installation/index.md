# Installation

This document will provide you with information about two ways of developing and running Shopsys Platform project and the services that it depends on.  
The first option [using docker](#installation-using-docker) is **highly recommended** since it is the easiest and fastest way to start Shopsys Platform.

In the case the operating system does not support docker, or you are not able to use Docker (e.g., due to the performance problems with Docker-sync),
we prepared also second section with document about project [installation without docker](#installation-without-docker),
however this way is slower and harder to configure and maintain because of different operating systems and their versions.

## Installation using Docker

These guides will show you how to use prepared Docker Compose configuration to simplify the installation process.

Docker contains complete development environment necessary for running your application, so you do not need to install and configure the whole server stack (Nginx, PostgreSQL, etc.) natively to run and develop Shopsys Platform on your machine.

All the services needed by the Shopsys Platform like Nginx or PostgreSQL run in Docker, and your source code is automatically synchronized between your local machine and Docker container in both ways.  
That means that you can normally use your IDE to edit the code while it is running inside a Docker container.

-   [Linux](./installation-using-docker-linux.md)
-   [macOS](./installation-using-docker-macos.md)
-   [Windows 10](./installation-using-docker-windows-10.md)

### Application setup

After a successful installation, you may be interested in the [Application setup guide](./installation-using-docker-application-setup.md).

## Installation without Docker

If your system is not listed above, or you do not want to use Docker containers, you can still install it natively.
To develop and run Shopsys Platform natively, you can read the [native installation](native-installation/native-installation.md) document.
This document is not a step-by-step guide as support for all operating systems and their versions is very hard to maintain.

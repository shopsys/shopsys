# Installation Guide

This document will provide you with information about ways of developing and running Shopsys Platform project and the services that it depends on.

## Quick Start with Shopsys CLI

For new projects, we recommend using **Shopsys CLI** - a command-line tool that automates project initialization and configuration:

> **Windows Users:** Run these commands inside WSL2 (Windows Subsystem for Linux).

```bash
# Download Shopsys CLI
curl -L https://github.com/shopsys/cli/releases/latest/download/shopsys.phar -o shopsys
chmod +x shopsys

# Initialize a new project
./shopsys init my-project
```

The CLI will guide you through domain configuration, locale settings, and more.
See [Project Initialization with Shopsys CLI](./project-initialization-with-shopsys-cli.md) for detailed documentation.

## Installation using Docker

These guides will show you how to use prepared Docker Compose configuration to simplify the installation process.
Docker contains complete development environment necessary for running your application so you do not need to install and configure the whole server stack (Nginx, PostgreSQL, etc.) natively in order to run and develop Shopsys Platform on your machine.  
All the services needed by Shopsys Platform like Nginx or PostgreSQL run in Docker and your source code is automatically synchronized between your local machine and Docker container in both ways.  
That means that you can normally use your IDE to edit the code while it is running inside a Docker container.

- [Linux](installation-using-docker-linux.md)
- [macOS](installation-using-docker-macos.md)
- [Windows 10](installation-using-docker-windows-10.md)

## Installation without Docker

If your system is not listed above, or you do not want to use Docker containers, you can still install it natively.
To develop and run Shopsys Platform natively you can read the [native installation](native-installation.md) document.
This document is not step-by-step guide since support for all operating systems and their versions is very hard to maintain.

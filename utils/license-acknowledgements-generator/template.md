# Open Source License Acknowledgements and Third-Party Copyrights

Shopsys Platform is licensed under the terms of the [Shopsys Platform Development License](./LICENSE).

Shopsys Platform utilizes third party software from various sources. Portions of this software are copyrighted by their respective owners as indicated in the copyright notices below.

The following acknowledgements pertain to this software license.

## Main components used by Shopsys Platform
These components are installed via `composer` or via `npm`.
You can check all the dependencies using the instructions from the section Libraries dynamically referenced via Composer and Libraries dynamically referenced via npm.

## Backend PHP packages

{{ placeholder.backendComposer }}

## Backend NPM packages

{{ placeholder.backendNpm }}

## Storefront NPM packages

{{ placeholder.storefrontNpm }}

## Images and libraries installed for a full run of Shopsys Platform on Docker
These images and packages are configured in `docker-compose.yml` and in `Dockerfile`. We do not redistribute these packages, we are only referencing them to download, user agrees to download these images by pulling and building images done by `docker compose up` or `docker build`.

### Postgres
Image: `postgres:12.1-alpine`  
License: PostgreSQL License  
https://www.postgresql.org/about/licence/

### Nginx
Image: `nginx:1.27-alpine`  
License: BSD-2-Clause  
http://nginx.org/LICENSE

### Redis
Image: `redis:7.4-alpine`  
License: BSD-3-Clause  
https://redis.io/topics/license

### Redis Commander
Image: `ghcr.io/joeferner/redis-commander:latest`  
License: MIT  
https://github.com/joeferner/redis-commander/blob/master/LICENSE

### Selenium Docker
Image: `seleniarm/standalone-firefox:4.9.0`  
License: Apache License 2.0  
https://github.com/seleniumhq-community/docker-seleniarm/blob/trunk/LICENSE.md

### Adminer
Image: `adminer:4.7`  
License: Apache License 2.0 or GPL 2  
https://github.com/vrana/adminer/blob/master/readme.txt

### Elasticsearch
Image: `docker.elastic.co/elasticsearch/elasticsearch-oss:7.10.2`  
License: Apache License 2.0  
https://github.com/elastic/elasticsearch/blob/v7.10.2/LICENSE.txt

### Elasticsearch ICU Analysis plugin
License: Apache License 2.0  
https://github.com/elastic/elasticsearch/blob/master/LICENSE.txt

### Kibana  
Image: `docker.elastic.co/kibana/kibana-oss:7.6.0`  
License: Apache License 2.0  
https://github.com/elastic/kibana/blob/v7.6.0/LICENSE.txt

### Node
Image: `node:20-alpine3.17`  
License: MIT  
https://github.com/nodejs/docker-node/blob/main/LICENSE

### Php
Image: `php:8.3-fpm-bullseye`  
License: The PHP License  
http://php.net/license/

### Python
Image: `python:3.7.4-slim-buster`  
License: Python Software Foundation License  
https://docs.python.org/3/license.html

### Composer - Dependency Management for PHP
License: MIT  
https://github.com/composer/composer/blob/master/LICENSE

### wget
License: GPL-3  
https://metadata.ftp-master.debian.org/changelogs/main/w/wget/stable_copyright

### gnupg
License: GPL-3+  
https://metadata.ftp-master.debian.org/changelogs/main/g/gnupg2/stable_copyright

### g++
License: GPL-2  
https://metadata.ftp-master.debian.org/changelogs/main/g/gcc-defaults/stable_copyright

### locales
License: LGPL-2.1  
https://metadata.ftp-master.debian.org/changelogs//main/g/glibc/stable_copyright

### unzip
License: Info-ZIP  
https://metadata.ftp-master.debian.org/changelogs/main/u/unzip/stable_copyright

### dialog
License: LGPL-2.1  
https://metadata.ftp-master.debian.org/changelogs/main/d/dialog/stable_copyright

### apt-utils
Licens: GPL-2+  
https://metadata.ftp-master.debian.org/changelogs/main/a/apt/stable_copyright

### nodejs-npm
License: Artistic License 2.0  
https://docs.npmjs.com/policies/npm-license

### bash-completion
License: GPL-2+  
https://metadata.ftp-master.debian.org/changelogs/main/b/bash-completion/stable_copyright

### libicu-dev
License: ICU License  
https://metadata.ftp-master.debian.org/changelogs/main/i/icu/stable_copyright

### libpng-dev
License: libpng  
https://metadata.ftp-master.debian.org/changelogs/main/libp/libpng1.6/stable_copyright

### libpq-dev
License: PostgreSQL  
https://metadata.ftp-master.debian.org/changelogs/main/p/postgresql-11/stable_copyright

### libzip-dev
License: GPL  
https://metadata.ftp-master.debian.org/changelogs/main/libz/libzip/stable_copyright

### autoconf
License: GPL-3+  
https://metadata.ftp-master.debian.org/changelogs/main/a/autoconf/stable_copyright

### libjpeg-dev
License: GPL-3  
https://metadata.ftp-master.debian.org/changelogs/main/libj/libjpeg/stable_copyright

### libfreetype6-dev
License: FTL  
https://metadata.ftp-master.debian.org/changelogs/main/f/freetype/stable_copyright

### vim
License: Vim  
https://metadata.ftp-master.debian.org/changelogs/main/v/vim/stable_copyright

### nano
License: GPL-3+  
https://metadata.ftp-master.debian.org/changelogs/main/n/nano/stable_copyright

### mc
License: GPL-3+  
https://metadata.ftp-master.debian.org/changelogs/main/m/mc/stable_copyright

### htop
License: GPL-2+  
https://metadata.ftp-master.debian.org/changelogs/main/h/htop/stable_copyright

### pecl
License: The PHP License  
https://pecl.php.net/copyright.php

### postgresql-12 and postgresql-client-12
License: PostgreSQL  
https://www.postgresql.org/about/licence/

### mkdocs
License: BSD-2-Clause  
https://github.com/mkdocs/mkdocs/blob/master/LICENSE

### jinja2
License: BSD-3-Clause  
https://github.com/pallets/jinja/blob/main/LICENSE.rst

### mkdocs-awesome-pages-plugin
License: MIT  
https://github.com/lukasgeiter/mkdocs-awesome-pages-plugin/blob/master/LICENSE.md

### markdown
License: BSD License  
https://github.com/Python-Markdown/markdown/blob/master/LICENSE.md

### readthedocs-version-warning-mkdocs-plugin
License: MIT  
https://github.com/shopsys/readthedocs-version-warning-mkdocs-plugin/blob/master/LICENSE


## Libraries dynamically referenced via Composer
Run `composer license` in your `shopsys-framework-php-fpm` container of your project to get the latest licensing info about all dependencies.

## Libraries dynamically referenced via npm
Run these commands in your `shopsys-framework-php-fpm` container of your project to get the latest licensing info about all packages.

```
cd project-base/app || true

npm install --no-save license-checker

./node_modules/.bin/license-checker
```
## Sources of information about licenses
For the packages installed through the composer, the composer.lock file is the source of the information about licenses. In some cases also the package license information directly in the GitHub repository of the given package is used.

For the packages installed through the npm, the GitHub repositories of these packages are used as the source of the information about licenses.

As a source of information about licenses of images and libraries downloaded and installed through Dockerfile and docker-compose.yml, there are used the GitHub repositories of these images and packages. Licenses of some libraries are mentioned also in a description of used Linux distribution https://www.debian.org/distrib/packages

Sources of information about licenses of libraries and components that are not downloaded and installed dynamically are the source files of libraries itself or the GitHub repositories of these libraries.

The transitive dependencies of the dependencies and images of 3rd parties are not included.

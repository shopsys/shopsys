# Open Source License Acknowledgements and Third-Party Copyrights

Shopsys Framework is licensed under the terms of the [Shopsys Community License](./LICENSE).

Shopsys Framework utilizes third party software from various sources. Portions of this software are copyrighted by their respective owners as indicated in the copyright notices below.

The following acknowledgements pertain to this software license.

## Main components used by Shopsys Framework
These components are installed via `composer` or via `npm`.
You can check all the dependencies using the instructions from the section Libraries dynamically referenced via Composer and Libraries dynamically referenced via npm.

## Backend PHP packages

{{ placeholder.backendComposer }}

## Backend NPM packages

{{ placeholder.backendNpm }}

## Storefront NPM packages

{{ placeholder.storefrontNpm }}

## Images and libraries installed for a full run of Shopsys Framework on Docker
These images and packages are configured in `docker-compose.yml` and in `Dockerfile`. We do not redistribute these packages, we are only referencing them to download, user agrees to download these images by pulling and building images done by `docker-compose up` or `docker build`.

### Postgres
Image: `Postgres:12.1-alpine`  
License: PostgreSQL License  
https://www.postgresql.org/about/licence/

### Nginx
Image: `Nginx:1.13-alpine`  
License: BSD-2-Clause  
http://nginx.org/LICENSE

### Redis
Image: `Redis:7.0-alpine`  
License: BSD-3-Clause  
https://redis.io/topics/license

### phpRedisAdmin
Image: `Erikdubbelboer/phpredisadmin:v1.10.2`  
License: Creative Commons Attribution 3.0 BY  
https://github.com/erikdubbelboer/phpRedisAdmin/blob/master/README.markdown

### Selenium Docker
Image: `seleniarm/standalone-firefox:4.9.0`  
License: Apache License 2.0  
https://github.com/seleniumhq-community/docker-seleniarm/blob/trunk/LICENSE.md  
Copyright 2018 Software Freedom Conservancy (SFC)

### Adminer
Image: `Adminer:4.7`  
License: Apache License 2.0 or GPL 2  
https://github.com/vrana/adminer/blob/master/readme.txt

### Elasticsearch
Image: `Docker.elastic.co/elasticsearch/elasticsearch-oss`  
License: Apache License 2.0  
https://github.com/elastic/elasticsearch/blob/v6.3.2/LICENSE.txt  
Copyright 2009-2018 Elasticsearch

### Php
Image: `php:8.1-fpm-bullseye`  
License: The PHP License  
http://php.net/license/  
Copyright (c) 1999 - 2018 The PHP Group. All rights reserved.

### Composer - Dependency Management for PHP
License: MIT  
https://github.com/composer/composer/blob/master/LICENSE  
Copyright (c) Nils Adermann, Jordi Boggiano

### wget
License: GPL  
https://metadata.ftp-master.debian.org/changelogs/main/w/wget/wget_1.18-5+deb9u2_copyright  
Copyright: (C) 2007 Free Software Foundation, Inc.

### gnupg
License: GPL-3+  
https://metadata.ftp-master.debian.org/changelogs/main/g/gnupg2/gnupg2_2.1.18-8~deb9u3_copyright  
Copyright: 1992, 1995-2016, Free Software Foundation, Inc

### g++
License: GPL  
https://metadata.ftp-master.debian.org/changelogs/main/g/gcc-defaults/gcc-defaults_1.168_copyright  
Copyright (c) 1999 The NetBSD Foundation, Inc.

### locales
License: LGPL-2.1  
https://metadata.ftp-master.debian.org/changelogs//main/g/glibc/glibc_2.24-11+deb9u3_copyright  
Copyright (C) 1991-2015 Free Software Foundation, Inc.

### unzip
License: Info-ZIP  
https://metadata.ftp-master.debian.org/changelogs/main/u/unzip/unzip_6.0-21_copyright  
Copyright (c) 1990-2009 Info-ZIP.  All rights reserved.

### dialog
License: LGPL-2.1  
https://metadata.ftp-master.debian.org/changelogs/main/d/dialog/dialog_1.3-20160828-2_copyright  
Copyright 1999-2016 Thomas E. Dickey  

### apt-utils
Licens: GPLv2+  
https://metadata.ftp-master.debian.org/changelogs/main/a/apt/apt_1.4.8_copyright  
Apt is copyright 1997, 1998, 1999 Jason Gunthorpe and others.

### nodejs-npm
License: Artistic License 2.0  
https://www.npmjs.com/policies/npm-license  
Copyright (c) 2000-2006, The Perl Foundation

### libicu-dev
License: ICU License  
https://metadata.ftp-master.debian.org/changelogs/main/i/icu/icu_57.1-6+deb9u2_copyright  
Copyright (c) 1995-2013 International Business Machines Corporation and others

### libpng-dev
License: libpng  
https://metadata.ftp-master.debian.org/changelogs/main/libp/libpng1.6/libpng1.6_1.6.28-1_copyright  
Copyright: Copyright (c) 1998-2016 Glenn Randers-Pehrson

### libpq-dev
Licens: PostgreSQL  
https://metadata.ftp-master.debian.org/changelogs/main/p/postgresql-9.6/postgresql-9.6_9.6.10-0+deb9u1_copyright  
Copyright:
Portions Copyright (c) 1996-2016, PostgreSQL Global Development Group,
Portions Copyright (c) 1994, The Regents of the University of California

### libzip-dev
License: GPL  
https://metadata.ftp-master.debian.org/changelogs/main/libz/libzip/libzip_1.1.2-1.1_copyright  
Copyright (C) 2007-2010 Fathi Boudra <fabo@debian.org>

### autoconf
License: GPL-3+  
https://metadata.ftp-master.debian.org/changelogs/main/a/autoconf/autoconf_2.69-10_copyright  
Copyright: 1992-1996, 1999-2001, 2003, 2005-2012 Free Software Foundation, Inc.

### pecl
License: The PHP License  
https://pecl.php.net/copyright.php  
Copyright © 2001-2018 The PHP Group. All rights reserved.

### postgresql-10 and postgresql-client-10
License: PostgreSQL  
https://www.postgresql.org/about/licence/  
Portions Copyright © 1996-2019, The PostgreSQL Global Development Group

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

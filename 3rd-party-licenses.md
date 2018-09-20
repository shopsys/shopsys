# License overview of included 3rd party libraries

Shopsys Framework is licensed under the terms of the [Shopsys Community License](./LICENSE).

Shopsys Framework also uses some third-party components and images
which are licensed under their own respective licenses.

## Components used by Shopsys Framework
These components are installed via `composer` or via `npm`.

### Symfony Framework and Symfony Components
License: MIT  
https://symfony.com/doc/3.4/contributing/code/license.html

### Elasticsearch
License: Apache License 2.0  
https://github.com/elastic/elasticsearch-php/blob/master/LICENSE

### Grunt: The JavaScript Task Runner
License: MIT  
https://github.com/gruntjs/grunt/blob/master/README.md

### Phing
License: LGPL-3.0-only  
https://github.com/phingofficial/phing/blob/master/LICENSE

### Nette Foundation tools 
License: BSD-2-Clause or GPL-2.0 or GPL-3.0  
https://nette.org/en/license

### PHP_CodeSniffer
License: BSD-3-Clause  
https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt

### PHP Parallel Lint
License: BSD-2-Clause  
https://github.com/JakubOnderka/PHP-Parallel-Lint/blob/master/LICENSE

### ESLint
License: MIT  
https://github.com/eslint/eslint/blob/master/LICENSE

### jQuery
License: MIT or GPL Version 2  
http://jquery.org/license

### selectize.js
License: Apache License 2.0  
https://github.com/selectize/selectize.js/blob/master/LICENSE

### slick.js
License: MIT  
https://github.com/kenwheeler/slick/blob/master/LICENSE

## Images and libraries installed for a full run of Shopsys Framework on Docker
These images and packages are configured in `docker-compose.yml` and in `Dockerfile`.

### Postgres
Image: Postgres:10.5-alpine  
License: PostgreSQL License  
https://www.postgresql.org/about/licence/

### Nginx
Image: Nginx:1.13-alpine  
License: BSD-2-Clause  
http://nginx.org/LICENSE

### Redis
Image: Redis:4.0-alpine  
License: BSD-3-Clause  
https://redis.io/topics/license

### phpRedisAdmin
Image: Erikdubbelboer/phpredisadmin:v1.10.2  
License: Creative Commons Attribution 3.0 BY
https://github.com/erikdubbelboer/phpRedisAdmin/blob/master/README.markdown

### Selenium Docker
Image: Selenium/standalone-chrome:3.11  
License: Apache License 2.0  
https://github.com/SeleniumHQ/docker-selenium/blob/master/LICENSE.md

### Adminer
Image: Adminer:4.6  
License: Apache License 2.0 or GPL 2  
https://github.com/vrana/adminer/blob/master/readme.txt

### Elasticsearch
Image: Docker.elastic.co/elasticsearch/elasticsearch-oss  
License: Apache License 2.0  
https://github.com/elastic/elasticsearch/blob/66b5ed86f7adede8102cd4d979b9f4924e5bd837/LICENSE.txt

### Php
Image: php:7.2-fpm-alpine  
License: The PHP License  
http://php.net/license/

### GNU libiconv
Package: gnu-libiconv  
License: LGPL  
https://pkgs.alpinelinux.org/package/edge/testing/x86/gnu-libiconv

### Composer - Dependency Management for PHP
License: MIT
https://github.com/composer/composer/blob/master/LICENSE

### grunt-cli
License: MIT  
https://github.com/gruntjs/grunt-cli/blob/master/LICENSE-MIT

### nodejs-npm
License: Artistic License 2.0  
https://www.npmjs.com/policies/npm-license

### prestissimo (composer plugin)
License: MIT  
https://github.com/hirak/prestissimo/blob/master/LICENSE

### libpng-dev
License: GPL
https://pkgs.alpinelinux.org/package/v3.3/main/x86/libpng-dev

### icu-dev
License: MIT ICU Unicode-TOU  
https://pkgs.alpinelinux.org/package/edge/main/x86/icu-dev

### postgresql-dev
License: PostgreSQL  
https://pkgs.alpinelinux.org/package/edge/main/x86/postgresql-dev

### libzip-dev
License: BSD-3-clause  
https://pkgs.alpinelinux.org/package/edge/community/x86/libzip-dev

### autoconf
License: GPL2+  
https://pkgs.alpinelinux.org/package/v3.3/main/x86/autoconf

### freetype-dev
License: FTL GPL2+  
https://pkgs.alpinelinux.org/package/edge/main/x86/freetype-dev

### libjpeg-turbo-dev
License: GPL  
https://pkgs.alpinelinux.org/package/edge/main/x86/libjpeg-turbo-dev

### pecl
License: The PHP License  
https://pecl.php.net/copyright.php

### postgresql
License: PostgreSQL  
https://pkgs.alpinelinux.org/package/edge/main/x86/postgresql-dev

## Others css and js libraries
Other components, mostly css and js libraries, that are not dynamically installed.
They can be found primarily in the `ShopBundle/Resources/` and `FrameworkBundle/Resources/` directories.

### Magnific Popup Repository
License: MIT  
https://github.com/dimsemenov/Magnific-Popup/blob/master/LICENSE

### Bootstrap - front-end framework
License: MIT  
https://github.com/twbs/bootstrap/blob/master/LICENSE

### Chart.js
License: MIT  
https://github.com/chartjs/Chart.js/blob/master/LICENSE.md

### BazingaJsTranslationBundle
License: MIT  
https://github.com/willdurand/BazingaJsTranslationBundle/blob/master/LICENSE

### jQuery Ajax File Uploader Widget
License: MIT  
https://github.com/danielm/uploader/blob/master/LICENSE.txt

### jquery.fix.clone
License: MIT  
https://github.com/spencertipping/jquery.fix.clone/blob/master/README

### jQuery MiniColors: A tiny color picker built on jQuery
License: MIT  
https://github.com/claviska/jquery-minicolors/blob/master/LICENSE.md

### FastClick
License: MIT  
https://github.com/ftlabs/fastclick/blob/master/LICENSE

### hoverIntent jQuery Plugin
License: MIT  
https://github.com/briancherne/jquery-hoverIntent/blob/master/jquery.hoverIntent.js

### nestedSortable jQuery Plugin
License: MIT  
https://github.com/ilikenwf/nestedSortable/blob/master/README.md

### normalize.css
License: MIT  
https://github.com/necolas/normalize.css/blob/master/LICENSE.md

### jQuery UI Touch Punch 0.2.3
License: MIT or GPL Version 2  
https://github.com/furf/jquery-ui-touch-punch/blob/master/jquery.ui.touch-punch.js

### Modernizr
License: MIT  
https://github.com/Modernizr/Modernizr/blob/master/LICENSE

### jquery.cookie
License: MIT  
https://github.com/carhartl/jquery-cookie/blob/master/MIT-LICENSE.txt

## Libraries dynamically referenced via Composer
Run `composer license` in your `shopsys-framework-php-fpm` container of your project to get the latest licensing info about all dependencies. 

## Libraries dynamically referenced via npm
Run these commands in your `shopsys-framework-php-fpm` container of your project to get the latest licensing info about all packages.

```
npm install --no-save license-checker

./node_modules/.bin/license-checker
``` 

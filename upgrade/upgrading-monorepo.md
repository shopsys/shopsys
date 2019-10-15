# Upgrading monorepo

Typical upgrade sequence should be:
* run `docker-compose down` to turn off your containers
* *(MacOS, Windows only)* run `docker-sync clean` so your volumes will be stopped and removed
* update your `docker-compose.yml` by `docker-compose` file specific for your operating system from `docker/conf` folder
* *(MacOS, Windows only)* update your `docker-sync.yml` by `docker-sync` file specific for your operating system from `docker/conf` folder
* *(MacOS, Windows only)* run `docker-sync start` to create volumes
* run `docker-compose build --no-cache --pull` to build your images without cache and with latest version
* run `docker-compose up -d --force-recreate --remove-orphans` to start the application again
* run `php phing composer-dev clean db-migrations` in `php-fpm` container
* if you're experiencing some errors, you can always rebuild application and load demo data with `php phing build-demo-dev`

***Note:** During the execution of `build-demo-dev phing target`, there will be installed 3-rd party software as dependencies of Shopsys Framework by [composer](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies) and [npm](https://docs.npmjs.com/about-the-public-npm-registry) with licenses that are described in document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/7.3/open-source-license-acknowledgements-and-third-party-copyrights.md)*

## [From v8.0.0 to v8.0.1-dev]

## [From v7.3.1 to v8.0.0]

## [From v7.3.2 to v7.3.3-dev]
- add mkdocs container to your `docker-compose.yml` to be able to see rendered documentation ([#1432](https://github.com/shopsys/shopsys/pull/1432))
    - copy proper definition from dist file for your platform from `docker/conf` directory

## [From v7.3.1 to v7.3.2]

## [From v7.3.0 to v7.3.1]

## [From v7.2.1 to v7.3.0]
- update definition of Elasticsearch service in your `docker-compose.yml` file to use Dockerfile that installs ICU analysis plugin ([#1069](https://github.com/shopsys/shopsys/pull/1069))
    ```diff
    elasticsearch:
    -   image: docker.elastic.co/elasticsearch/elasticsearch-oss:6.3.2
    +   build:
    +       context: .
    +       dockerfile: project-base/docker/elasticsearch/Dockerfile
        container_name: shopsys-framework-elasticsearch
        ulimits:
            nofile:
                soft: 65536
                hard: 65536
        ports:
            - "9200:9200"
        volumes:
            - elasticsearch-data:/usr/share/elasticsearch/data
        environment:
            - discovery.type=single-node
    ```
- use the restructured phing targets ([#1068](https://github.com/shopsys/shopsys/pull/1068))
    - don't use targets with suffix `-packages` or `-utils`, the targets without the suffixes now work in the whole monorepo
        - eg. you can use `tests-unit` to run unit tests in the whole monorepo instead of running `tests-unit`, `tests-packages` and `tests-utils`
        - you can even use coding standards subtargets in the whole monorepo, such as `ecs`, `eslint-fix`, etc.
    - read [the new guidelines for phing targets](https://docs.shopsys.com/en/latest/contributing/guidelines-for-phing-targets/) before suggesting changes via pull requests
- run `db-create` and `test-db-create` phing targets to install extension for UUID ([#1055](https://github.com/shopsys/shopsys/pull/1055))
- remove `'project-base/docs',` line from your `docker-sync.yml` ([#1172](https://github.com/shopsys/shopsys/pull/1172))

## [From v7.2.0 to v7.2.1]

## [From v7.1.0 to v7.2.0]
- update definition of postgres service in your `docker-compose.yml` file to use customized configuration ([#946](https://github.com/shopsys/shopsys/pull/946))
    ```diff
    postgres:
        image: postgres:10.5-alpine
        container_name: shopsys-framework-postgres
        volumes:
            - ./docker/postgres/postgres.conf:/var/lib/postgresql/data/postgresql.conf:delegated
            - ./var/postgres-data:/var/lib/postgresql/data:cached
        environment:
            - PGDATA=/var/lib/postgresql/data/pgdata
            - POSTGRES_USER=root
            - POSTGRES_PASSWORD=root
            - POSTGRES_DB=shopsys
    +   command:
    +       - postgres
    +       - -c
    +       - config_file=/var/lib/postgresql/data/postgresql.conf
    ```
- remove `database_server_version` parameter from `parameters.yml` ([#1001](https://github.com/shopsys/shopsys/pull/1001))

## [From v7.0.0 to v7.1.0]

## [From v7.0.0-beta5 to v7.0.0-beta6]
- [#694 PHP 7.3 support](https://github.com/shopsys/shopsys/pull/694)
    - rebuild your Docker images with `docker-compose up -d --build`
- *(low priority)* [#755 update npm packages to latest version](https://github.com/shopsys/shopsys/pull/755)
    - remove all npm packages by removing folder `project-base/node_modules` and `project-base/package-lock.json`
    - run command `php phing npm`
- [#783 microservices has been removed and their funcionality has been moved to framework](https://github.com/shopsys/shopsys/pull/793)
    - remove microservice services and volumes from `docker-compose.yml`, remove volumes of microservices and remove exclude on microservice directory in `docker-sync.yml`
    - run `docker-compose down --remove-orphans`
    - run `docker-sync clean` if you are using MacOS or Windows installation using Docker
    - run `docker-sync start` if you are using MacOS or Windows installation using Docker
    - run `docker-compose up -d --build`

## [From v7.0.0-beta4 to v7.0.0-beta5]
- [#651 It's possible to add index prefix to elastic search](https://github.com/shopsys/shopsys/pull/651)
    - either rebuild your Docker images with `docker-compose up -d --build` or add `ELASTIC_SEARCH_INDEX_PREFIX=''` to your `.env` files in the microservice root directories, otherwise all requests to the microservices will throw `EnvNotFoundException`
- [#679 webserver container starts after php-fpm is started](https://github.com/shopsys/shopsys/pull/679)
    - add `depends_on: [php-fpm]` into `webserver` service of your `docker-compose.yml` file so webserver will not fail on error `host not found in upstream php-fpm:9000`

## [From v7.0.0-beta2 to v7.0.0-beta3]
- *(MacOS only)* [#503 updated docker-sync configuration](https://github.com/shopsys/shopsys/pull/503/)
    - run `docker-compose down` to turn off your containers
    - run `docker-sync clean` so your volumes will be removed
    - remove these lines from `docker-compose.yml`
        ```yaml
        shopsys-framework-postgres-data-sync:
            external: true
        shopsys-framework-elasticsearch-data-sync:
            external: true
        ```
    - remove these lines from `docker-sync.yml`
        ```yaml
        shopsys-framework-postgres-data-sync:
            src: './project-base/var/postgres-data/'
            host_disk_mount_mode: 'cached'
         shopsys-framework-elasticsearch-data-sync:
            src: './project-base/var/elasticsearch-data/'
            host_disk_mount_mode: 'cached'
        ```
    - add `shopsys-framework-microservice-product-search-sync` and `shopsys-framework-microservice-product-search-export-sync` volumes to `docker-compose.yml` for `php-fpm` service
        ```yaml
        services:
            # ...
            php-fpm:
                # ...
                volumes:
                    # ...
                    - shopsys-framework-microservice-product-search-sync:/var/www/html/microservices/product-search
                    - shopsys-framework-microservice-product-search-export-sync:/var/www/html/microservices/product-search-export
        ```
    - run `docker-sync start` to create volumes
    - run `docker-compose up -d --force-recreate` to start application again
- [#533 main php-fpm container now uses multi-stage build feature](https://github.com/shopsys/shopsys/pull/533)
    - update the build config in `docker-compose.yml` ([changes in version and build config can be seen in the PR](https://github.com/shopsys/shopsys/pull/533/files#diff-1aa104f9fc120d0743883a5ba02bfe21))
    - rebuild images by running `docker-compose up -d --build`
- [#530 - Update of installation for production via docker](https://github.com/shopsys/shopsys/pull/530)
    - update `docker-compose.yml` on production server with the new configuration from updated [`docker-compose.prod.yml`](/project-base/docker/conf/docker-compose.prod.yml.dist) file
- [#545 - Part of the application build is now contained in the build of the image](https://github.com/shopsys/shopsys/pull/545)
    - rebuild image by running `docker-compose up -d --build`
- [#547 - content-test directory is used instead of content during the tests](https://github.com/shopsys/shopsys/pull/547)
    - modify your `parameters_test.yml` according to this pull request so there will be used different directory for feeds, images, etc., during the tests
- [#580 Removed trailing whitespaces from markdown files ](https://github.com/shopsys/shopsys/pull/580)
    - run `docker-compose down` to turn off your containers
    - *(MacOS, Windows only)*
        - run `docker-sync clean` so your volumes will be removed
        - remove excluding of `docs` folder from `docker-sync.yml`
        - run `docker-sync start` to create volumes
    - run `docker-compose up -d --build --force-recreate` to start application  
- *(low priority)* [#551 - github token erase](https://github.com/shopsys/shopsys/pull/551)
    - you can stop providing the `github_oauth_token` in your `docker-compose.yml`

## [From v7.0.0-alpha5 to v7.0.0-alpha6]
- when upgrading your installed [monorepo](https://docs.shopsys.com/en/8.0/introduction/monorepo/), you'll have to change the build context for the images of the microservices in `docker-compose.yml`
    - `build.context` should be the root of the microservice (eg. `microservices/product-search-export`)
    - `build.dockerfile` should be `docker/Dockerfile`
    - execute `docker-compose up -d --build`, microservices should be up and running

[From v8.0.0 to v8.0.1-dev]: https://github.com/shopsys/shopsys/compare/v8.0.0...8.0
[From v7.3.1 to v8.0.0]: https://github.com/shopsys/shopsys/compare/v7.3.1...v8.0.0
[From v7.3.2 to v7.3.3-dev]: https://github.com/shopsys/shopsys/compare/v7.3.2...7.3
[From v7.3.1 to v7.3.2]: https://github.com/shopsys/shopsys/compare/v7.3.1...v7.3.2
[From v7.3.0 to v7.3.1]: https://github.com/shopsys/shopsys/compare/v7.3.0...v7.3.1
[From v7.2.1 to v7.3.0]: https://github.com/shopsys/shopsys/compare/v7.2.1...v7.3.0
[From v7.2.0 to v7.2.1]: https://github.com/shopsys/shopsys/compare/v7.2.0...v7.2.1
[From v7.1.0 to v7.2.0]: https://github.com/shopsys/shopsys/compare/v7.1.0...v7.2.0
[From v7.0.0 to v7.1.0]: https://github.com/shopsys/shopsys/compare/v7.0.0...v7.1.0
[From v7.0.0-beta5 to v7.0.0-beta6]: https://github.com/shopsys/shopsys/compare/v7.0.0-beta5...v7.0.0-beta6
[From v7.0.0-beta4 to v7.0.0-beta5]: https://github.com/shopsys/shopsys/compare/v7.0.0-beta4...v7.0.0-beta5
[From v7.0.0-beta2 to v7.0.0-beta3]: https://github.com/shopsys/shopsys/compare/v7.0.0-beta2...v7.0.0-beta3
[From v7.0.0-alpha5 to v7.0.0-alpha6]: https://github.com/shopsys/shopsys/compare/v7.0.0-alpha5...v7.0.0-alpha6

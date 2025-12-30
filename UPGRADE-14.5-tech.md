# UPGRADING FROM 14.0.1 to 14.5.0 (Technology Update Release)

The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading

-   upgrade only your composer dependencies and follow the instructions in the guide below
-   upgrade locally first - after you fix all issues caused by the upgrade, commit your changes, test your application, and then continue with a deployment onto your server
-   upgrade one version at a time:
    -   start with a working application
    -   upgrade to the next version
    -   fix all the issues you encounter
    -   repeat
-   check the instructions in all sections; any of them could be relevant to you
-   the typical upgrade sequence should be:
    -   run `docker-compose down --volumes` to turn off your containers
    -   _(macOS only)_ run `mutagen-compose down --volumes` instead
    -   follow upgrade notes in the _Infrastructure_ section (related to `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    -   _(MacOS, Windows only)_ run `docker-sync start` to create volumes
    -   run `docker-compose build --no-cache --pull` to build your images without cache and with the latest version
    -   run `docker-compose up -d --force-recreate --remove-orphans` to start the application again
    -   update the `shopsys/*` dependencies in `composer.json` to the version you are upgrading to
        -   e.g., `"shopsys/framework": "14.5.0"`
    -   follow upgrade notes in the _Composer dependencies_ section (related with `composer.json`)
    -   run `composer update shopsys/* --with-dependencies`
    -   update the `@shopsys/framework` package in your `package.json` (in "dependencies" section) to the version you are upgrading to
        -   eg. `"@shopsys/framework": "9.0.4",`
    -   run `npm install` to update the NPM dependencies
    -   follow all upgrade notes you have not done yet
    -   run `php phing clean`
    -   run `php phing db-migrations` to run the database migrations
    -   test your app locally
    -   commit your changes
    -   run `composer update` to update the rest of your dependencies, test the app again, and commit `composer.lock`
-   if any of the database migrations do not suit you, there is an option to skip it; see [our Database Migrations docs](https://docs.shopsys.com/en/latest/introduction/database-migrations/#reordering-and-skipping-migrations)
-   we may miss something even if we care a lot about these instructions. In case something doesn't work after the upgrade, you'll find more information in the [CHANGELOG](CHANGELOG.md)

## [Upgrade from v14.0.1 to v14.5.0](https://github.com/shopsys/shopsys/compare/v14.0.1...v14.5.0)

#### upgrade the necessary libraries to fix builds ([#4275](https://github.com/shopsys/shopsys/pull/4275))

-   update Twig to the latest version to prevent security issues
-   upgrade doctrine/persistence to ^3.3.3
-   see the following diffs to update your project:
    -   [project-base diff](https://www.github.com/shopsys/project-base/commit/03fd2b379cc580dc7728d2386718dbce2d790682)
    -   [project-base diff](https://www.github.com/shopsys/project-base/commit/a8d6b4a8dd789989356fb95e78db40fde400242e)

#### upgrade PostgreSQL version to 17.4 ([#4278](https://github.com/shopsys/shopsys/pull/4278))

-   CAUTION: upgrade and deploy the application BEFORE upgrading the database to PostgreSQL 17.4
    -   otherwise the application will not work properly
-   rename reserved database function `normalize` to non-reserved name `normalized`
    -   create migration to change `normalize()` function to `normalized()` if you had used it in some indexes, functions, or somewhere else
    -   don't forget to rename this function in SQLs in repositories, commands, or somewhere else where is used
-   codeception acceptance tests now use pg_restore to dump DB from an SQL file
    -   `Shopsys\FrameworkBundle\Component\Doctrine\DatabaseConnectionCredentialsProvider::getConnectionDsn()` method was removed without replacement
        -   if needed, compose the DSN manually using the provided credentials
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8ee9cee1e12c6d261debd541bd615b13f20de785) to update your project

#### upgrade Nginx to the new version ([#4276](https://github.com/shopsys/shopsys/pull/4276))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/c206ce74e123fc77b8fb6cc08fd3e7276e0d2836) to update your project
-   remember to update your local docker-compose.yml file and rebuild the containers
-   if necessary, update the CI configuration with the new version of the nginx image

#### remove warnings from docker ([#4277](https://github.com/shopsys/shopsys/pull/4277))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/05374b00eab69127f6fefe72bc60aa3ef1198167) to update your project

#### upgrade Redis to the newest version ([#4280](https://github.com/shopsys/shopsys/pull/4280))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/5894a434807bccf36fc32109e96e753e2976822c) to update your project
-   upgrade `redis` package on the storefront and check your custom code for compatibility
-   if you have installed Review server, then don't forget to update the `redis` service in `docker-compose.yml` to the `7.4-alpine`.

#### upgrade RabbitMQ version ([#4279](https://github.com/shopsys/shopsys/pull/4279))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/1d21da1a97c334a5afe3de6ecc29d9d19b9f26d7) to update your project

#### upgrade Elastic and Kibana to version 7.17.2 ([#4283](https://github.com/shopsys/shopsys/pull/4283))

-   update Elasticsearch and Kibana to version 7.17.2 on all your environments
    -   you can use docker images `docker.elastic.co/elasticsearch/elasticsearch:7.17.2` and `docker.elastic.co/kibana/kibana:7.17.2`
-   make the same changes in your uncommitted `docker-compose.yml` file and recreate the `elasticsearch` and `kibana` containers
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/7b13c05b4d508c7724d04f16b706865c43439832) to update your project

#### upgrade shopsys/deployment package ([#4285](https://github.com/shopsys/shopsys/pull/4285))

-   before upgrading the deployment package, look closely at the changes in the deployment package: https://github.com/shopsys/deployment/compare/v2.1.0...v4.2.0
-   some manifests were updated and require a newer version of Kubernetes
-   upgrade the deployment package to version 4.2.0:
    -   in your `app/composer.json` upgrade version of `shopsys/deployment` package:
    -   run `composer update shopsys/deployment`
    -   look at the changes in the deployment package and apply them to your project: https://github.com/shopsys/deployment/blob/main/UPGRADE.md
-   use `WHITELIST_IPS` variable to define whitelist IPs for ingress. See: https://github.com/shopsys/deployment?tab=readme-ov-file#whitelist-ip-addresses
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/192a3205ee26f54c5b62aa6703c7543509f2183d) to update your project

#### Replace `mutagen-compose` with plain `mutagen` because `mutagen-compose` is no longer compatible with the latest Docker API ([#4343](https://github.com/shopsys/shopsys/pull/4343))

##### If you were using mutagen-compose, follow these steps to migrate:

1. Stop and clean up your current environment:

    ```bash
    mutagen-compose down
    docker system prune -a
    ```

2. Ensure you are running the latest version of Mutagen

    ```bash
    brew upgrade mutagen
    ```

3. Run the installation script to set up the new environment:
    ```bash
    ./project-base/scripts/install.sh
    ```

##### New Make targets for macOS with Mutagen:

| Target                           | Description                                                            |
| -------------------------------- | ---------------------------------------------------------------------- |
| `make mutagen-up`                | Starts Docker environment with Mutagen sync                            |
| `make mutagen-up-build`          | Starts environment and rebuilds images                                 |
| `make mutagen-up-build-no-cache` | Starts environment and rebuilds images without cache                   |
| `make mutagen-stop`              | Stops containers while preserving them (useful for switching projects) |
| `make mutagen-down`              | Removes containers and stops Mutagen sync                              |

##### Using Docker Compose directly:

For commands not covered by Make targets (e.g., `exec`, `logs`, `restart`), use plain `docker compose` like `docker compose exec php-fpm bash`

-   see #project-base-diff to update your project

#### update coding standards for YAML files ([#4361](https://github.com/shopsys/shopsys/pull/4361))

-   see #project-base-diff to update your project

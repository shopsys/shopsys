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
    -   run `docker compose down --volumes` to turn off your containers
    -   _(macOS only)_ run `mutagen project terminate` first, then `docker compose down --volumes`
    -   follow upgrade notes in the _Infrastructure_ section (related to `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    -   _(MacOS, Windows only)_ run `docker-sync start` to create volumes
    -   run `docker compose build --no-cache --pull` to build your images without cache and with the latest version
    -   run `docker compose up -d --force-recreate --remove-orphans` to start the application again
    -   update the `shopsys/*` dependencies in `composer.json` to the version you are upgrading to
        -   e.g., `"shopsys/framework": "v7.0.0"`
    -   follow upgrade notes in the _Composer dependencies_ section (related with `composer.json`)
    -   run `composer update shopsys/* --with-dependencies`
    -   run `npm install` to update the NPM dependencies
    -   follow all upgrade notes you have not done yet
    -   run `php phing clean`
    -   run `php phing db-migrations` to run the database migrations
    -   test your app locally
    -   commit your changes
    -   run `composer update` to update the rest of your dependencies, test the app again, and commit `composer.lock`
-   if any of the database migrations do not suit you, there is an option to skip it; see [our Database Migrations docs](https://docs.shopsys.com/en/latest/introduction/database-migrations/#reordering-and-skipping-migrations)
-   we may miss something even if we care a lot about these instructions. In case something doesn't work after the upgrade, you'll find more information in the [CHANGELOG](CHANGELOG.md)

#### Movement of features from project-base to packages

-   in this version, there are quite a lot of features that have been moved from `project-base` to the packages, mostly to the `framework` and the `frontend-api` package
-   each section in the upgrade guide contains a link to the `project-base` diff and besides the particular upgrade instructions, there is also a list of the moved features you should be aware of (if there are any)
-   if your project was originally not developed from the Commerce Cloud version, or it was developed on a version lower than `v13.0.0`, these feature movements should not affect you during the upgrade
-   otherwise, you might need to adjust your project to the changes:
-   if you had no custom changes in the moved features, you should be fine, you can safely remove the features from your project and use the ones from the packages (project-base diff in each section will help you with that)
-   if you had custom changes in the moved features, you will need to adjust your project to the changes
-   you should remove everything that was not modified in your project and keep just the custom changes using the recommended ways of the [framework extensibility](https://docs.shopsys.com/en/latest/extensibility/)
-   one way or another, you should pay a special attention to the database migrations that were added with the feature movement. If they suit your needs, you should keep them and remove the original migrations from your project, otherwise, you should skip the newly added migrations.

#### Introduction of strict types

-   with each change, we are updating most classes that have been altered by that change to use strict types
-   this means that you will need to update your project to use strict types as well
-   we do not see writing upgrade notes for such changes as beneficial, as it would mean for you to check every single change manually even if only a few occurrences would apply to your project
-   we are currently not aware of easy way to automate this process, so you will need to do it manually
-   probably the easiest way is to run `composer install`, `php phing standards-fix` and `php phing phpstan` commands, which will fail on errors caused by incompatibility strict types and fix those errors manually

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

#### stop processing images by PHP to avoid decreasing quality ([#4360](https://github.com/shopsys/shopsys/pull/4360))

-   `Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor` class was changed:
    -   `createInterventionImage()` method was removed
    -   `resize()` method was removed
-   `Shopsys\FrameworkBundle\Component\Image\Processing\ImageThumbnailFactory` class was removed
-   `Shopsys\FrameworkBundle\Component\Domain\DomainIconResizer` class was removed, use `Shopsys\FrameworkBundle\Component\Domain\DomainIconProcessor` instead
-   `Shopsys\FrameworkBundle\Component\Domain\DomainIconProcessor` class was changed:
    -   `convertToDomainIconFormatAndSave()` method was renamed to `saveIcon()`
-   `Shopsys\FrameworkBundle\Component\Image\Processing\Exception\OriginalSizeImageCannotBeGeneratedException` class was removed
-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    -   `imageuploadFields.html.twig` Twig template extension
    -   `Advert/listGrid.html.twig` Twig template extension
-   see #project-base-diff to update your project

#### Upgrade two-factor packages to be compatible with Symfony 6 ([#4357](https://github.com/shopsys/shopsys/pull/4357))

-   see #project-base-diff to update your project

#### replace shopsys/ordered-form package with becklyn/ordered-form-bundle ([#4357](https://github.com/shopsys/shopsys/pull/4357))

-   calling `setPosition()` directly on existing form fields is not supported anymore, otherwise, ordering form fields works the same way
-   see [becklyn/ordered-form-bundle](https://github.com/Becklyn/OrderedFormBundle) for the documentation
-   see #project-base-diff to update your project

#### Upgrade Sentry package ([#4357](https://github.com/shopsys/shopsys/pull/4357))

-   see #project-base-diff to update your project

#### update composer dependencies to newer versions ([#4357](https://github.com/shopsys/shopsys/pull/4357))

-   PHPUnit has been updated to version 11 with many other dependencies
    -   many changes have been introduced since previously used version 9 e.g. configuration options, deprecated or removed methods, deprecated doc-blocks, etc.
    -   see https://github.com/sebastianbergmann/phpunit/blob/11.1/DEPRECATIONS.md and https://github.com/sebastianbergmann/phpunit/blob/10.5/DEPRECATIONS.md for deprecations in PHPUnit that you need to solve in your tests
    -   see #project-base diff to see changes you might need to apply in your tests
-   `commerceguys/intl` has been updated to the latest version
    -   `IntlCurrencyRepository` and `NumberFormatterExtension` class methods have updated their interfaces to include strict types, you will need to update your usages of such methods in your project
-   see #project-base-diff to update your project

#### update easy-coding-standard to version 12.2 ([#4367](https://github.com/shopsys/shopsys/pull/4367))

-   update configuration file to new version
-   skip rules are now defined in the separate `ecs-skip-rule.php` file
-   paths to check are now defined directly in the `ecs.php` file
-   fixer `RedundantMarkDownTrailingSpacesFixer` was removed as markdown files are formatted by prettier
-   see #project-base-diff to update your project

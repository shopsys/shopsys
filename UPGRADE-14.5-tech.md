# UPGRADING FROM 14.0.1 to 14.5.0-tech.x

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
        -   e.g., `"shopsys/framework": "14.5.0-tech.1"`
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

#### Movement of features from project-base to packages

-   in this version, there are quite a lot of features that have been moved from `project-base` to the packages, mostly to the `framework` and the `frontend-api` package
-   each section in the upgrade guide contains a link to the `project-base` diff and besides the particular upgrade instructions, there is also a list of the moved features you should be aware of (if there are any)
-   if your project was originally not developed from the Commerce Cloud version, or it was developed on a version lower than `v13.0.0`, these feature movements should not affect you during the upgrade
-   otherwise, you might need to adjust your project to the changes:
    -   if you had no custom changes in the moved features, you should be fine, you can safely remove the features from your project and use the ones from the packages (project-base diff in each section will help you with that)
    -   if you had custom changes in the moved features, you will need to adjust your project to the changes
        -   you should remove everything that was not modified in your project and keep just the custom changes using the recommended ways of the [framework extensibility](https://docs.shopsys.com/en/latest/extensibility/)
    -   one way or another, you should pay a special attention to the database migrations that were added with the feature movement. If they suit your needs, you should keep them and remove the original migrations from your project, otherwise, you should skip the newly added migrations.

<!-- Insert upgrade instructions in the following format:
- general instruction ([#<PR number>](https://github.com/shopsys/shopsys/pull/<PR number>))
    - additional instructions
    - see #project-base-diff to update your project
-->

## [Upgrade from v14.0.1 to v14.5.0-tech.1](https://github.com/shopsys/shopsys/compare/v14.0.1...14.5.0-tech.1)

#### upgrade the necessary libraries to fix builds ([#4275](https://github.com/shopsys/shopsys/pull/4275))

-   update Twig to the latest version to prevent security issues
-   upgrade doctrine/persistence to ^3.3.3
-   see #project-base-diff to update your project

#### upgrade PostgreSQL version to 17.4 ([#4278](https://github.com/shopsys/shopsys/pull/4278))

-   CAUTION: upgrade and deploy the application BEFORE upgrading the database to PostgreSQL 17.4
    -   otherwise the application will not work properly
-   rename reserved database function `normalize` to non-reserved name `normalized`
    -   create migration to change `normalize()` function to `normalized()` if you had used it in some indexes, functions, or somewhere else
    -   don't forget to rename this function in SQLs in repositories, commands, or somewhere else where is used
-   codeception acceptance tests now use pg_restore to dump DB from an SQL file
    -   `Shopsys\FrameworkBundle\Component\Doctrine\DatabaseConnectionCredentialsProvider::getConnectionDsn()` method was removed without replacement
        -   if needed, compose the DSN manually using the provided credentials
-   see #project-base-diff to update your project

#### upgrade nginx to the new version ([#4276](https://github.com/shopsys/shopsys/pull/4276))

-   see #project-base-diff to update your project
-   remember to update your local docker-compose.yml file and rebuild the containers
-   if necessary, update the CI configuration with the new version of the nginx image

#### remove warnings from docker ([#4277](https://github.com/shopsys/shopsys/pull/4277))

-   see #project-base-diff to update your project

#### upgrade Redis to the newest version ([#4280](https://github.com/shopsys/shopsys/pull/4280))

-   see #project-base-diff to update your project
-   upgrade `redis` package on the storefront and check your custom code for compatibility
-   if you have installed Review server, then don't forget to update the `redis` service in `docker-compose.yml` to the `7.4-alpine`.

#### upgrade rabbitMQ version ([#4279](https://github.com/shopsys/shopsys/pull/4279))

-   see #project-base-diff to update your project

#### upgrade Elastic and Kibana to version 7.17.2 ([#4283](https://github.com/shopsys/shopsys/pull/4283))

-   update Elasticsearch and Kibana to version 7.17.2 on all your environments
    -   you can use docker images `docker.elastic.co/elasticsearch/elasticsearch:7.17.2` and `docker.elastic.co/kibana/kibana:7.17.2`
-   make the same changes in your uncommitted `docker-compose.yml` file and recreate the `elasticsearch` and `kibana` containers
-   see #project-base-diff to update your project

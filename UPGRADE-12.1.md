# UPGRADING FROM 12.0 to 12.1
The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading
Since there are two possible scenarios how you can use the Shopsys Platform, instructions are divided into these scenarios.

### You use our packages only
Follow the instructions in relevant sections, e.g. `shopsys/coding-standards` or `shopsys/http-smoke-testing`.

### You are developing a project based on the project-base repository
* upgrade only your composer dependencies and follow the instructions in the guide below
* upgrade locally first. After you fix all issues caused by the upgrade, commit your changes, test your application and then continue with a deployment onto your server
* upgrade one version at a time:
    * start with a working application
    * upgrade to the next version
    * fix all the issues you encounter
    * repeat
* check the instructions in all sections, any of them could be relevant for you
* the typical upgrade sequence should be:
    * run `docker-compose down --volumes` to turn off your containers
    * *(macOS only)* run `mutagen-compose down --volumes` instead
    * follow upgrade notes in the *Infrastructure* section (related with `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    * run `docker-compose build --no-cache --pull` to build your images without a cache and with the latest version
    * run `docker-compose up -d --force-recreate --remove-orphans` to start the application again
    * update the `shopsys/*` dependencies in `composer.json` to version you are upgrading to
        * eg. `"shopsys/framework": "v7.0.0"`
    * follow upgrade notes in the *Composer dependencies* section (related with `composer.json`)
    * run `composer update shopsys/* --with-dependencies`
    * update the `@shopsys/framework` package in your `package.json` (in "dependencies" section) to the version you are upgrading to
        * eg. `"@shopsys/framework": "9.0.4",`
    * run `npm install` to update the NPM dependencies
    * follow all upgrade notes you have not done yet
    * run `php phing clean`
    * run `php phing db-migrations` to run the database migrations
    * test your app locally
    * commit your changes
    * run `composer update` to update the rest of your dependencies, test the app again and commit `composer.lock`
* if any of the database migrations does not suit you, there is an option to skip it, see [our Database Migrations docs](https://docs.shopsys.com/en/latest/introduction/database-migrations/#reordering-and-skipping-migrations)
* even if we care a lot about these instructions, it is possible we miss something. In case something doesn't work after the upgrade, you'll find more information in the [CHANGELOG](CHANGELOG-12.1.md)

<!-- Insert upgrade instructions in the following format:
- general instruction ([#<PR number>](https://github.com/shopsys/shopsys/pull/<PR number>))
    - additional instructions
    - see #project-base-diff to update your project
-->

## [Upgrade from v12.0.0 to v12.1.0-dev](https://github.com/shopsys/shopsys/compare/v12.0.0...12.1)

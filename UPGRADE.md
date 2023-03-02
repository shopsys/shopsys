# UPGRADING
The releases of Shopsys Framework adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading
Since there are 3 possible scenarios how you can use the Shopsys Framework, instructions are divided into these scenarios.

### You use our packages only
Follow the instructions in relevant sections, eg. `shopsys/coding-standards` or `shopsys/http-smoke-testing`.

### You are using the monorepo
Follow the instructions in the [monorepo upgrade guide](upgrade/upgrading-monorepo.md).

### You are developing a project based on the project-base repository
* upgrade only your composer dependencies and follow the instructions in a guide below
* upgrade locally first - after you fix all issues caused by the upgrade, commit your changes, test your application and then continue with a deployment onto your server
* upgrade one version at a time:
    * start with a working application
    * upgrade to the next version
    * fix all the issues you encounter
    * repeat
* check the instructions in all sections, any of them could be relevant for you
* typical upgrade sequence should be:
    * run `docker-compose down` to turn off your containers
    * *(MacOS, Windows only)* run `docker-sync clean` so your volumes will be stopped and removed
    * follow upgrade notes in the *Infrastructure* section (related with `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    * *(MacOS, Windows only)* run `docker-sync start` to create volumes  
    * run `docker-compose build --no-cache --pull` to build your images without cache and with latest version
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
* even we care a lot about these instructions, it is possible we miss something. In case something doesn't work after the upgrade, you'll find more information in the [CHANGELOG](CHANGELOG.md)

## Upgrade guides
* ### [From v11.0.0 to v11.0.1-dev](/upgrade/UPGRADE-v11.0.1-dev.md)
* ### [From v10.0.5 to v11.0.0](/upgrade/UPGRADE-v11.0.0.md)
* ### [From v10.0.4 to v10.0.5](/upgrade/UPGRADE-v10.0.5.md)
* ### [From v10.0.2 to v10.0.3](/upgrade/UPGRADE-v10.0.3.md)
* ### [From v10.0.1 to v10.0.2](/upgrade/UPGRADE-v10.0.2.md)
* ### [From v9.1.2 to v10.0.0](/upgrade/UPGRADE-v10.0.0.md)
* ### [From v9.1.1 to v9.1.2](/upgrade/UPGRADE-v9.1.2.md)
* ### [From v9.1.0 to v9.1.1](/upgrade/UPGRADE-v9.1.1.md)
* ### [From v9.0.4 to v9.1.0](/upgrade/UPGRADE-v9.1.0.md)
* ### [From v9.0.3 to v9.0.4](/upgrade/UPGRADE-v9.0.4.md)
* ### [From v9.0.2 to v9.0.3](/upgrade/UPGRADE-v9.0.3.md)
* ### [From v9.0.1 to v9.0.2](/upgrade/UPGRADE-v9.0.2.md)
* ### [From v9.0.0 to v9.0.1](/upgrade/UPGRADE-v9.0.1.md)
* ### [From v8.1.1 to v9.0.0](/upgrade/UPGRADE-v9.0.0.md)
* ### [From v8.1.0 to v8.1.1](/upgrade/UPGRADE-v8.1.1.md)
* ### [From v8.0.0 to v8.1.0](/upgrade/UPGRADE-v8.1.0.md)
* ### [From v7.3.3 to v8.0.0](upgrade/UPGRADE-v8.0.0.md)
* ### [From v7.3.3 to v7.3.4-dev](upgrade/UPGRADE-v7.3.4-dev.md)
* ### [From v7.3.2 to v7.3.3](upgrade/UPGRADE-v7.3.3.md)
* ### [From v7.3.1 to v7.3.2](upgrade/UPGRADE-v7.3.2.md)
* ### [From v7.3.0 to v7.3.1](upgrade/UPGRADE-v7.3.1.md)
* ### [From v7.2.2 to v7.3.0](upgrade/UPGRADE-v7.3.0.md)
* ### [From v7.2.1 to v7.2.2](upgrade/UPGRADE-v7.2.2.md)
* ### [From v7.2.0 to v7.2.1](upgrade/UPGRADE-v7.2.1.md)
* ### [From v7.1.0 to v7.2.0](upgrade/UPGRADE-v7.2.0.md)
* ### [From v7.1.0 to v7.1.1](upgrade/UPGRADE-v7.1.1.md)
* ### [From v7.0.0 to v7.1.0](upgrade/UPGRADE-v7.1.0.md)
* ### [From v7.0.0 to v7.0.1](upgrade/UPGRADE-v7.0.1.md)
* ### [From v7.0.0-beta6 to v7.0.0](upgrade/UPGRADE-v7.0.0.md)
* ### [From v7.0.0-beta5 to v7.0.0-beta6](upgrade/UPGRADE-v7.0.0-beta6.md)
* ### [From v7.0.0-beta4 to v7.0.0-beta5](upgrade/UPGRADE-v7.0.0-beta5.md)
* ### [From v7.0.0-beta3 to v7.0.0-beta4](upgrade/UPGRADE-v7.0.0-beta4.md)
* ### [From v7.0.0-beta2 to v7.0.0-beta3](upgrade/UPGRADE-v7.0.0-beta3.md)
* ### [From v7.0.0-beta1 to v7.0.0-beta2](upgrade/UPGRADE-v7.0.0-beta2.md)
* ### [From v7.0.0-alpha6 to v7.0.0-beta1](upgrade/UPGRADE-v7.0.0-beta1.md)
* ### [From v7.0.0-alpha5 to v7.0.0-alpha6](upgrade/UPGRADE-v7.0.0-alpha6.md)
* ### [From v7.0.0-alpha4 to v7.0.0-alpha5](upgrade/UPGRADE-v7.0.0-alpha5.md)
* ### [From v7.0.0-alpha3 to v7.0.0-alpha4](upgrade/UPGRADE-v7.0.0-alpha4.md)
* ### [From v7.0.0-alpha2 to v7.0.0-alpha3](upgrade/UPGRADE-v7.0.0-alpha3.md)
* ### [From v7.0.0-alpha1 to v7.0.0-alpha2](upgrade/UPGRADE-v7.0.0-alpha2.md)
* ### [Before monorepo](upgrade/before-monorepo.md)

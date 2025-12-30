# UPGRADING FROM 18.x to 19.0

The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading

- upgrade only your composer dependencies and follow the instructions in the guide below
- upgrade locally first - after you fix all issues caused by the upgrade, commit your changes, test your application, and then continue with a deployment onto your server
- upgrade one version at a time:
    - start with a working application
    - upgrade to the next version
    - fix all the issues you encounter
    - repeat
- check the instructions in all sections; any of them could be relevant to you
- the typical upgrade sequence should be:
    - run `docker compose down --volumes` to turn off your containers
    - _(macOS only)_ run `mutagen project terminate` first, then `docker compose down --volumes`
    - follow upgrade notes in the _Infrastructure_ section (related to `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    - _(MacOS, Windows only)_ run `docker-sync start` to create volumes
    - run `docker compose build --no-cache --pull` to build your images without cache and with the latest version
    - run `docker compose up -d --force-recreate --remove-orphans` to start the application again
    - update the `shopsys/*` dependencies in `composer.json` to the version you are upgrading to
        - e.g., `"shopsys/framework": "v7.0.0"`
    - follow upgrade notes in the _Composer dependencies_ section (related with `composer.json`)
    - run `composer update shopsys/* --with-dependencies`
    - run `npm install` to update the NPM dependencies
    - follow all upgrade notes you have not done yet
    - run `php phing clean`
    - run `php phing db-migrations` to run the database migrations
    - test your app locally
    - commit your changes
    - run `composer update` to update the rest of your dependencies, test the app again, and commit `composer.lock`
- if any of the database migrations do not suit you, there is an option to skip it; see [our Database Migrations docs](https://docs.shopsys.com/en/latest/introduction/database-migrations/#reordering-and-skipping-migrations)
- we may miss something even if we care a lot about these instructions. In case something doesn't work after the upgrade, you'll find more information in the [CHANGELOG](CHANGELOG.md)

#### Movement of features from project-base to packages

- in this version, there are quite a lot of features that have been moved from `project-base` to the packages, mostly to the `framework` and the `frontend-api` package
- each section in the upgrade guide contains a link to the `project-base` diff and besides the particular upgrade instructions, there is also a list of the moved features you should be aware of (if there are any)
- if your project was originally not developed from the Commerce Cloud version, or it was developed on a version lower than `v13.0.0`, these feature movements should not affect you during the upgrade
- otherwise, you might need to adjust your project to the changes:
- if you had no custom changes in the moved features, you should be fine, you can safely remove the features from your project and use the ones from the packages (project-base diff in each section will help you with that)
- if you had custom changes in the moved features, you will need to adjust your project to the changes
- you should remove everything that was not modified in your project and keep just the custom changes using the recommended ways of the [framework extensibility](https://docs.shopsys.com/en/latest/extensibility/)
- one way or another, you should pay a special attention to the database migrations that were added with the feature movement. If they suit your needs, you should keep them and remove the original migrations from your project, otherwise, you should skip the newly added migrations.

#### Introduction of strict types

- with each change, we are updating most classes that have been altered by that change to use strict types
- this means that you will need to update your project to use strict types as well
- we do not see writing upgrade notes for such changes as beneficial, as it would mean for you to check every single change manually even if only a few occurrences would apply to your project
- we are currently not aware of easy way to automate this process, so you will need to do it manually
- probably the easiest way is to run `composer install`, `php phing standards-fix` and `php phing phpstan` commands, which will fail on errors caused by incompatibility strict types and fix those errors manually

<!-- backendNotes -->

### Storefront

<!-- storefrontNotes -->

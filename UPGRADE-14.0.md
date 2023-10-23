# UPGRADING FROM 13.x to 14.0

The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading

Since there are 3 possible scenarios for using Shopsys Platform, instructions are divided into these scenarios.

### You use our packages only

Follow the instructions in relevant sections, e.g. `shopsys/coding-standards` or `shopsys/http-smoke-testing`.

### You are developing a project based on the project-base repository

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
        -   e.g., `"shopsys/framework": "v7.0.0"`
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

<!-- Insert upgrade instructions in the following format:
- general instruction ([#<PR number>](https://github.com/shopsys/shopsys/pull/<PR number>))
    - additional instructions
    - see #project-base-diff to update your project
-->

## [Upgrade from v13.0.0 to v14.0.0-dev](https://github.com/shopsys/shopsys/compare/v13.0.0...14.0)

-   add rounded price value to order process ([#2835](https://github.com/shopsys/shopsys/pull/2835))
    -   see #project-base-diff to update your project
-   remove link from administrator and customers breadcrumb ([#2881](https://github.com/shopsys/shopsys/pull/2881))
    -   see #project-base-diff to update your project
-   add test to keep Elasticsearch converter and mapping in sync ([#2880](https://github.com/shopsys/shopsys/pull/2880))
    -   see #project-base-diff to update your project
-   set products for export to elastic after changing quantity after completing, editing, or deleting order ([#2587](https://github.com/shopsys/shopsys/pull/2587))
    -   method `Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade::__construct()` changed its interface:
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
                protected readonly ProductHiddenRecalculator $productHiddenRecalculator,
                protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
                protected readonly ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
                protected readonly ProductVisibilityFacade $productVisibilityFacade,
                protected readonly ModuleFacade $moduleFacade,
        +       protected readonly ProductRepository $productRepository,
            )
        ```
-   update your project to fix problems with single domain ([#2875](https://github.com/shopsys/shopsys/pull/2875))
    -   see #project-base-diff to update your project
-   improve product lists in GrapesJS ([#2879](https://github.com/shopsys/shopsys/pull/2879))
    -   see #project-base-diff to update your project
-   add Symfony Messenger along with RabbitMQ ([#2898](https://github.com/shopsys/shopsys/pull/2898))
    -   install application to create new necessary containers (run again `./scripts/install.sh`) – this will overwrite your local `docker-compose.yml` file
    -   see #project-base-diff to update your project
-   set the custom logger to the Frontend API ([#2882](https://github.com/shopsys/shopsys/pull/2882))
    -   you can set `shopsys.frontend_api.validation_logged_as_error` parameter to `true` to log validation errors with log level ERROR instead of INFO
    -   see #project-base-diff to update your project

### Storefront

-   add rounded price value to order process ([#2835](https://github.com/shopsys/shopsys/pull/2835))
    -   see #project-base-diff to update your project
-   remove unnecessary default value for domain config in zustand ([#2888](https://github.com/shopsys/shopsys/pull/2888))
    -   you probably do not need the default value as well, as we set it right at the beginning of page load
    -   see #project-base-diff to update your project
-   fixed undefined window error ([#2882](https://github.com/shopsys/shopsys/pull/2882))
    -   in one of the previous PR's, the `canUseDom` function was removed, but it caused the application to fail in some cases
    -   because of that, the function was brought back as a constant (as `isClient`) and some checks were reinstantiated
    -   in your code, you should pay attention to where you are using the window object and make sure it is available (either by checking the logic or by explicitly wrapping it in a check)
    -   also keep in mind that `canUseDom` is now `isClient` and `isServer` is now used as `!isClient`
    -   for not-discussed changes, see #project-base-diff to update your project
-   add Prettier plugin and ESlint plugins and rules ([#2874](https://github.com/shopsys/shopsys/pull/2874))
    -   see #project-base-diff to update your project
    -   to check if your repo follows the new rules run `pnpm run check` or `pnpm run check--fix` to autofix in Storefront folder or in Storefront Docker container
    -   **Prettier**
        -   added `@trivago/prettier-plugin-sort-imports` plugin
    -   **ESlint**
        -   added `eslint-plugin-no-relative-import-paths` plugin and rules enforcing rules for imports
        -   new rules
            -   no-helpers-are-exported-from-component-file
            -   react/jsx-no-useless-fragment
            -   react/self-closing-comp
            -   react/jsx-sort-props
            -   no-relative-import-paths/no-relative-import-paths
-   add Related Products tab on product detail page ([#2885](https://github.com/shopsys/shopsys/pull/2885))
    -   see #project-base-diff to update your project
-   improve product lists in GrapesJS ([#2879](https://github.com/shopsys/shopsys/pull/2879))
    -   see #project-base-diff to update your project
-   add instant page skeleton after link click ([#2863](https://github.com/shopsys/shopsys/pull/2863))
    -   before page must first load and then skeleton was shown, now we pass page type to `ExtendedLink` component which allow us to display immediately after user click on the link proper skeleton for the required page
    -   some reorganization and renaming was done to Skeletons, we basically have only two types of skeletons, for pages and modules, since it is sometimes difficult to recognise which one is which, we have added Page word, but this was not perfect in folder organization, that's why it's been added word Module as well, to organize skeletons better way
    -   added missing skeletons for Homepage and Stores
    -   adjustments to current skeletons to match the design of a page better
    -   see #project-base-diff to update your project
-   refactoring of various error-related matters on SF ([#2871](https://github.com/shopsys/shopsys/pull/2871))
    -   the goal was to shine light on some of the not-well-understood places in regard of error handling on SF
    -   for you to get the most out of this PR, you should check `error-handling.md` in SF docs, which is a direct result of this PR
    -   it contains explanations and tips on how to improve error handling in your SF code as well
    -   for not-discussed changes, see #project-base-diff to update your project


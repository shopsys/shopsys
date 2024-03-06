# UPGRADING FROM 14.x to 15.0

The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading

Since there are two possible scenarios for using Shopsys Platform, instructions are divided into these scenarios.

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

#### Movement of features from project-base to packages

-   in this version, there are quite a lot of features that have been moved from `project-base` to the packages, mostly to the `framework` and the `frontend-api` package
-   each section in the upgrade guide contains a link to the `project-base` diff and besides the particular upgrade instructions, there is also a list of the moved features you should be aware of (if there are any)
-   if your project was originally not developed from the Commerce Cloud version, or it was developed on a version lower than `v13.0.0`, these feature movements should not affect you during the upgrade
-   otherwise, you might need to adjust your project to the changes:
    -   if you had no custom changes in the moved features, you should be fine, you can safely remove the features from your project and use the ones from the packages (project-base diff in each section will help you with that)
    -   if you had custom changes in the moved features, you will need to adjust your project to the changes
        -   you should remove everything that was not modified in your project and keep just the custom changes using the recommended ways of the [framework extensibility](https://docs.shopsys.com/en/latest/extensibility/)

<!-- Insert upgrade instructions in the following format:
- general instruction ([#<PR number>](https://github.com/shopsys/shopsys/pull/<PR number>))
    - additional instructions
    - see #project-base-diff to update your project
-->

## [Upgrade from v14.0.0 to v15.0.0-dev](https://github.com/shopsys/shopsys/compare/v14.0.0...15.0)

-   fix promo code mass generation ([#3039](https://github.com/shopsys/shopsys/pull/3039))
    -   see #project-base-diff to update your project
-   fix display advert in categories ([#3040](https://github.com/shopsys/shopsys/pull/3040))
    -   see #project-base-diff to update your project
-   remove unused order flow ([#3046](https://github.com/shopsys/shopsys/pull/3046))
    -   class `Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade` was removed
    -   class `Shopsys\FrameworkBundle\Model\Order\OrderFlowFactoryInterface` was removed
    -   constructor `Shopsys\FrameworkBundle\Model\Security\FrontLogoutHandler` changed its interface:
        ```diff
            public function __construct(
                protected readonly RouterInterface $router,
        -       protected readonly OrderFlowFacade $orderFlowFacade,
        ```
    -   constructor `Shopsys\FrameworkBundle\Model\Security\LoginListener` changed its interface:
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
        -       protected readonly OrderFlowFacade $orderFlowFacade,
                protected readonly AdministratorActivityFacade $administratorActivityFacade,
        ```
    -   see #project-base-diff to update your project
-   Added check vulnerabilities for javascripts in storefront ([#2993](https://github.com/shopsys/shopsys/pull/2993))
    -   see #project-base-diff to update your project
-   remove deprecated properties from product entity ([#3027](https://github.com/shopsys/shopsys/pull/3027))
    -   the following features were removed altogether in favor of `ProductAvailabilityFacade` functionality
        -   `Product::$outOfStockAction`
        -   `Product::$outOfStockAvailability`
        -   `Product::$stockQuantity`
        -   `Product::$usingStock`
        -   `Product::$availability`
        -   `Availability` entity with all the related logic (see `Shopsys\FrameworkBundle\Model\Product\Availability` namespace)
    -   `Shopsys\FrameworkBundle\Controller\Admin\DefaultController::__construct` interface has changed:
        ```diff
            public function __construct(
                // ...
        -       protected readonly AvailabilityFacade $availabilityFacade,
        ```
    -   `Shopsys\FrameworkBundle\Controller\Admin\ProductController::__construct` interface has changed:
        ```diff
            public function __construct(
                // ...
        -       protected readonly AvailabilityFacade $availabilityFacade,
        ```
    -   `assets/js/admin/validation/form/validationProduct.js` was removed
    -   `Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface::OPERATOR_IS_USED` and `OPERATOR_IS_NOT_USED` constants were removed
    -   `Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductStockFilter` class was removed
    -   `Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig::__construct` interface has changed:
        ```diff
            public function __construct(
                // ...
        -       ProductStockFilter $productStockFilter,
                // ..
        ```
    -   `Shopsys\FrameworkBundle\Model\Module\ModuleList::PRODUCT_STOCK_CALCULATIONS` constant was removed
    -   `Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade` class was removed
    -   `Shopsys\FrameworkBundle\Model\Order\OrderFacade::__construct` interface has changed:
        ```diff
            public function __construct(
                // ...
        -       protected readonly OrderProductFacade $orderProductFacade,
        ```
    -   `Shopsys\FrameworkBundle\Model\Product\Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY`, `OUT_OF_STOCK_ACTION_EXCLUDE_FROM_SALE`, and `OUT_OF_STOCK_ACTION_HIDE` constants were removed
    -   `Shopsys\FrameworkBundle\Model\ProductProductDataFactory::__construct` interface has changed:
        ```diff
            public function __construct(
                // ...
        -       protected readonly AvailabilityFacade $availabilityFacade,
        ```
    -   `Shopsys\FrameworkBundle\Model\Product\ProductFactory::__construct` interface has changed:
        ```diff
            public function __construct(
                // ...
        -       protected readonly ProductAvailabilityCalculation $productAvailabilityCalculation,
        ```
    -   `Shopsys\FrameworkBundle\Model\Product\ProductRepository::getAllSellableUsingStockInStockQueryBuilder()` method was removed
    -   `Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade::__construct` interface has changed:
        ```diff
            public function __construct(
                // ...
        -       protected readonly OrderProductFacade $orderProductFacade,
        ```
    -   `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper::isUsingStock()` method was removed
    -   FE API: `isUsingStock` field was removed from `ProductDecorator.types`
    -   double-check the `Shopsys\FrameworkBundle\Migrations\Version20240206145944` migration to ensure it does not break your application
    -   see #project-base-diff to update your project
-   set version of `friendsofphp/php-cs-fixer` >= `3.50` as conflicting to resolve problems in tests ([#3042](https://github.com/shopsys/shopsys/pull/3042))
    -   see #project-base-diff to update your project
-   fix removing promo code from cart ([#3043](https://github.com/shopsys/shopsys/pull/3043))
    -   see #project-base-diff to update your project

### Storefront

-   added query/mutation name to URL and headers ([#3041](https://github.com/shopsys/shopsys/pull/3041))

    -   queries and mutation names are now part of the request URL, which means query is not made to `/graphql/` but `/graphql/<QueryName>/`
    -   if you do not want this, you can skip the changes (ensure there is no `operationNameExchange` used in your URQL client)
    -   if you apply this change, it should be easier for you to debug requests in tools like Kibana and also see operation names in browser network tab

-   fix display advert in categories ([#3040](https://github.com/shopsys/shopsys/pull/3040))

-   refactored different levels of error debugging on SF ([#3033](https://github.com/shopsys/shopsys/pull/3033))

    -   we now have three levels (`console`, `toast-and-console`, `no-debug`) based on which verbose error messages are shown to developers
    -   in your projects, you should use constants `isWithConsoleErrorDebugging`, `isWithToastAndConsoleErrorDebugging`,`isWithErrorDebugging` to control different debugging in all places where you handle errors or exceptions
    -   docs were rewritten to match this new approach, so you can read them to get a better idea
    -   verbose logging was also added for mutations, so if you need to handle this differently, check `errorExchange.ts`
    -   added .env.development for SF, so you should put all your env variables for development there, if you need to handle them in a specific way, differently from the app being in production mode

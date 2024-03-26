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
#### general instruction ([#<PR number>](https://github.com/shopsys/shopsys/pull/<PR number>))

- additional instructions
- see #project-base-diff to update your project
-->

## [Upgrade from v14.0.0 to v15.0.0-dev](https://github.com/shopsys/shopsys/compare/v14.0.0...15.0)

#### fix promo code mass generation ([#3039](https://github.com/shopsys/shopsys/pull/3039))

-   see #project-base-diff to update your project

#### fix display advert in categories ([#3040](https://github.com/shopsys/shopsys/pull/3040))

-   see #project-base-diff to update your project

#### remove unused order flow ([#3046](https://github.com/shopsys/shopsys/pull/3046))

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

#### Added check vulnerabilities for javascripts in storefront ([#2993](https://github.com/shopsys/shopsys/pull/2993))

-   see #project-base-diff to update your project

#### remove deprecated properties from product entity ([#3027](https://github.com/shopsys/shopsys/pull/3027))

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

#### set version of `friendsofphp/php-cs-fixer` >= `3.50` as conflicting to resolve problems in tests ([#3042](https://github.com/shopsys/shopsys/pull/3042))

-   see #project-base-diff to update your project

#### fix removing promo code from cart ([#3043](https://github.com/shopsys/shopsys/pull/3043))

-   see #project-base-diff to update your project

#### add doctrine backtrace collecting ([#3055](https://github.com/shopsys/shopsys/pull/3055))

-   see #project-base-diff to update your project

#### remove unused front order classes ([#3054](https://github.com/shopsys/shopsys/pull/3054))

-   class `Shopsys\FrameworkBundle\Model\Order\FrontOrderData` was removed
-   class `Shopsys\FrameworkBundle\Model\Order\FrontOrderDataMapper` was removed
-   class `Shopsys\FrameworkBundle\Model\Order\OrderDataMapper` was removed
-   `Shopsys\FrameworkBundle\Model\Order\OrderFacade`:
    -   constructor `__construct()` changed its interface:
        ```diff
            public function __construct(
                // ...
        -       protected readonly FrontOrderDataMapper $frontOrderDataMapper,
                // ...
        ```
    -   method `createOrderFromFront()` was removed
    -   method `prefillFrontOrderData()` was removed
-   see #project-base-diff to update your project
-   fix a problem with deployment for the same commit ([#3061](https://github.com/shopsys/shopsys/pull/3061))
    -   see #project-base-diff to update your project

#### add rounding and discount as a separate order item types ([#3056](https://github.com/shopsys/shopsys/pull/3056))

-   order items now have proper types for rounding and discount, see `OrderItem::TYPE_*` constants
-   interface `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactoryInterface` was removed, use `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory`, or your custom implementation instead
-   interface `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactoryInterface` was removed, use `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory`, or your custom implementation instead
-   interface `Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface` was removed, use `Shopsys\FrameworkBundle\Model\Order\OrderDataFactory`, or your custom implementation instead
-   the following classes are now strictly typed:
    -   `Shopsys\FrameworkBundle\Controller\Admin\OrderController`
    -   `Shopsys\FrameworkBundle\Model\Order\Item\OrderItem`
    -   `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade`
    -   `Shopsys\FrameworkBundle\Model\Order\Item\Order`
    -   `Shopsys\FrameworkBundle\Model\Order\Item\OrderDataFactory`
    -   `Shopsys\FrameworkBundle\Model\Order\Item\OrderFacade`
-   constructor `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade::__construct()` was changed:
    ```diff
        public function __construct(
            // ...
    +       protected readonly OrderItemDataFactory $orderItemDataFactory,
    ```
-   `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory`:
    -   method `createProduct()` changed its interface:
        ```diff
            public function createProduct(
        +       OrderItemData $orderItemData,
                Order $order,
        -       string $name,
        -       Price $price,
        -       string $vatPercent,
        -       int $quantity,
        -       ?string $unitName,
        -       ?string $catnum,
        -       ?Product $product = null,
        +       ?Product $product,
            ): OrderItem {
        ```
    -   method `createPayment()` changed its interface:
        ```diff
            public function createPayment(
        +       OrderItemData $orderItemData,
                Order $order,
        -       string $name,
        -       Price $price,
        -       string $vatPercent,
        -       int $quantity,
                Payment $payment,
            ): OrderItem {
        ```
    -   method `createTransport()` changed its interface:
        ```diff
            public function createTransport(
        +       OrderItemData $orderItemData,
                Order $order,
        -       string $name,
        -       Price $price,
        -       string $vatPercent,
        -       int $quantity,
                Transport $transport,
            ): OrderItem {
        ```
-   `Shopsys\FrameworkBundle\Model\Order\Order`:
    -   method `getPaymentName()` was removed, use `getPaymentItem()->getName()` instead
    -   method `getOrderPayment()` was removed, use `getPaymentItem()` instead
    -   method `getTransportName()` was removed, use `getTransportItem()->getName()` instead
    -   method `getOrderTransport()` was removed, use `getTransportItem()` instead
    -   method `getTransportAndPaymentItems()` was removed, use `getTransportItem()` and `getPaymentItem()` instead
    -   method `getTransportAndPaymentPrice()` was removed
    -   method `getItemById()` was removed
    -   method `getProductItemsCount()` was removed
-   see #project-base-diff to update your project

#### changed link URL for Catalog navigation element ([#3057](https://github.com/shopsys/shopsys/pull/3057))

-   see #project-base-diff to update your project

#### add security headers for more safety in local development ([#3050](https://github.com/shopsys/shopsys/pull/3050))

-   see #project-base-diff to update your project

### added indexes for columns which were used for order by and where in entites TransferIssue and CronModuleRun ([#3048](https://github.com/shopsys/shopsys/pull/3048))

-   see #project-base-diff to update your project

#### fix persist on null object in create delivery address ([#2350](https://github.com/shopsys/shopsys/pull/2350))

-   `Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade` class has changed:
    -   `create` method was renamed to `createIfAddressFilled` and return type was changed to `?DeliveryAddress`
-   see #project-base-diff to update your project

### Storefront

#### added query/mutation name to URL and headers ([#3041](https://github.com/shopsys/shopsys/pull/3041))

-   queries and mutation names are now part of the request URL, which means query is not made to `/graphql/` but `/graphql/<QueryName>/`
-   if you do not want this, you can skip the changes (ensure there is no `operationNameExchange` used in your URQL client)
-   if you apply this change, it should be easier for you to debug requests in tools like Kibana and also see operation names in browser network tab

#### fix display advert in categories ([#3040](https://github.com/shopsys/shopsys/pull/3040))

#### refactored different levels of error debugging on SF ([#3033](https://github.com/shopsys/shopsys/pull/3033))

-   we now have three levels (`console`, `toast-and-console`, `no-debug`) based on which verbose error messages are shown to developers
-   in your projects, you should use constants `isWithConsoleErrorDebugging`, `isWithToastAndConsoleErrorDebugging`,`isWithErrorDebugging` to control different debugging in all places where you handle errors or exceptions
-   docs were rewritten to match this new approach, so you can read them to get a better idea
-   verbose logging was also added for mutations, so if you need to handle this differently, check `errorExchange.ts`
-   added .env.development for SF, so you should put all your env variables for development there, if you need to handle them in a specific way, differently from the app being in production mode

#### refactor mobile menu ([#3035](https://github.com/shopsys/shopsys/pull/3035))

-   now the whole component is refactored and is included with new design

#### added visitAndWaitForStableDOM for visiting pages in cypress ([#3071](https://github.com/shopsys/shopsys/pull/3071))

-   change all `cy.visit` to `cy.visitAndWaitForStableDOM`, to make sure that cypress waits for the DOM to be stable before interacting

#### cypress with GUI ([#3069](https://github.com/shopsys/shopsys/pull/3069))

-   cypress tests can now be run using cypress interactive GUI
-   read the docs (`cypress.md`) for detailed info

#### SF bundle size reduction changes ([#3077](https://github.com/shopsys/shopsys/pull/3077))

-   we removed `react-dom/server` from the client bundle by removing it from `SeznamMapMarkerLayer`, since we did not use it anywhere else, that was enough for us. If you are using it anywhere else, you should also remove that from the client, as it bloats the bundle
-   URQL is now loaded using dynamic import to load it in a separate chunk, same as other parts of the \_app file, which means you should ideally do it with other large parts of your \_app as well, to separate it into multiple chunks
-   the GQL schema in a JSON format used for teh URQL client is now purified using `@urql/introspection` and the smaller file is used, so keep in mind to change all your imports

```diff
- import schema from 'schema.graphql.json';
+ import schema from 'schema-compressed.graphql.json';
```

-   inside i18n.js, we now report exceptions by sending it to a new Next.js API route `/api/log-exception`, which is done to avoid importing the entire Sentry package on the client, so you should also remove direct imports of Sentry from all the files which are not webpack-compiled, as that has an immense negative effect on performance
-   redis is now blocked from the client bundle by specifying it in the webpack config inside next.config.js, and you should block all other packages which are in your client bundle, but should not be there (check by running `npm run analyze` in the SF folder)

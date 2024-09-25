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
    -   run `docker compose down --volumes` to turn off your containers
    -   _(macOS only)_ run `mutagen-compose down --volumes` instead
    -   follow upgrade notes in the _Infrastructure_ section (related to `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    -   _(MacOS, Windows only)_ run `docker-sync start` to create volumes
    -   run `docker compose build --no-cache --pull` to build your images without cache and with the latest version
    -   run `docker compose up -d --force-recreate --remove-orphans` to start the application again
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
    -   one way or another, you should pay a special attention to the database migrations that were added with the feature movement. If they suit your needs, you should keep them and remove the original migrations from your project, otherwise, you should skip the newly added migrations.

#### Introduction of strict types

-   with each change, we are updating most classes that have been altered by that change to use strict types
-   this means that you will need to update your project to use strict types as well
-   we do not see writing upgrade notes for such changes as beneficial, as it would mean for you to check every single change manually even if only a few occurrences would apply to your project
-   we are currently not aware of easy way to automate this process, so you will need to do it manually
-   probably the easiest way is to run `composer install`, `php phing standards-fix` and `php phing phpstan` commands, which will fail on errors caused by incompatibility strict types and fix those errors manually

## [Upgrade from v15.0.0 to v15.0.1-dev](https://github.com/shopsys/shopsys/compare/v15.0.0...15.0)

## [Upgrade from v14.0.0 to v15.0.0](https://github.com/shopsys/shopsys/compare/v14.0.0...v15.0.0)

#### update FE adverts query filter arguments ([#3211](https://github.com/shopsys/shopsys/pull/3211))

-   this change moves adverts filtering logic from FE to BE
-   this does not affect ad banners functionality
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/623cd5f07a3696d560c0bb5e19e825e6a2294a73) to update your project

#### fix promo code mass generation ([#3039](https://github.com/shopsys/shopsys/pull/3039))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/21c63121dbef663da3198af7c02f2bc6bd8a9a05) to update your project

#### fix display advert in categories ([#3040](https://github.com/shopsys/shopsys/pull/3040))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/23ca69c657204d90b878d690b58c7220310e9cf6) to update your project

#### remove unused order flow ([#3046](https://github.com/shopsys/shopsys/pull/3046))

-   class `Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade` was removed
-   class `Shopsys\FrameworkBundle\Model\Order\OrderFlowFactoryInterface` was removed
-   constructor `Shopsys\FrameworkBundle\Model\Security\LoginListener` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
    -       protected readonly OrderFlowFacade $orderFlowFacade,
            protected readonly AdministratorActivityFacade $administratorActivityFacade,
    ```
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/3ba7bb6d82fc7ed02acdd137a4bbdf998c89341d) to update your project

#### Added check vulnerabilities for javascripts in storefront ([#2993](https://github.com/shopsys/shopsys/pull/2993))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/fb0a908fd7dc41739c38caa5f866f96e0b518fc8) to update your project

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
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/842e1424c80a6289dc939aad8580bd2f192e5ff6) to update your project

#### set version of `friendsofphp/php-cs-fixer` >= `3.50` as conflicting to resolve problems in tests ([#3042](https://github.com/shopsys/shopsys/pull/3042))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/09c4ef167ec90a7ac6d17f7e46ff2eb02cce6c06) to update your project

#### fix removing promo code from cart ([#3043](https://github.com/shopsys/shopsys/pull/3043))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/0a192ab177e9b7faa9a958dfc4ae5a0d323e552b) to update your project

#### add doctrine backtrace collecting ([#3055](https://github.com/shopsys/shopsys/pull/3055))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/c21f5bf1ac26bcd412821f1d0544c8e7f613a11e) to update your project

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
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/18b1003e3ffd750cda55246897b35e6ee7b5f5ee) to update your project
    -   see [project-base diff](https://www.github.com/shopsys/project-base/commit/18b1003e3ffd750cda55246897b35e6ee7b5f5ee) to update your project

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
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4dba9b4748f9331a6cd495064a4b489565694315) to update your project

#### changed link URL for Catalog navigation element ([#3057](https://github.com/shopsys/shopsys/pull/3057))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/16109dbe3a608a1327b236fe9df56517386e517e) to update your project

#### add security headers for more safety in local development ([#3050](https://github.com/shopsys/shopsys/pull/3050))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b2c509dc8650e72cbf8b2b8a0c0de477c119b108) to update your project

### added indexes for columns which were used for order by and where in entites TransferIssue and CronModuleRun ([#3048](https://github.com/shopsys/shopsys/pull/3048))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b2c509dc8650e72cbf8b2b8a0c0de477c119b108) to update your project

#### fix persist on null object in create delivery address ([#2350](https://github.com/shopsys/shopsys/pull/2350))

-   `Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade` class has changed:
    -   `create` method was renamed to `createIfAddressFilled` and return type was changed to `?DeliveryAddress`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b1e883f645e50e6b453c6df9e96b2e94d8f43bf4) to update your project

#### remove UUIDs pools and replace them by UUID generated by entity datas to avoid UUIDs changes in the future ([#3075](https://github.com/shopsys/shopsys/pull/3075))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/3f378284db4c13b7e09569bd3bb90fdfaa8f7e8a) to update your project

#### product recalculations scoping ([#3051](https://github.com/shopsys/shopsys/pull/3051))

-   `Shopsys\FrameworkBundle\Command\DispatchRecalculationMessageCommand` class was changed:
    -   `executeAll()` method changed its interface:
        ```diff
        -   public function executeAll(SymfonyStyle $symfonyStyle): int
        +   public function executeAll(SymfonyStyle $symfonyStyle, array $scopes): int
        ```
    -   `executeIds()` method changed its interface:
        ```diff
            public function executeIds(
                array $productIds,
                SymfonyStyle $symfonyStyle,
                ProductRecalculationPriorityEnumInterface $priority,
        +       array $scopes,
        ```
-   `Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex` class was changed:
    -   `getExportDataForIds()` method changed its interface:
        ```diff
            abstract public function getExportDataForIds(
                int $domainId,
                array $restrictToIds,
        +       array $fields = [],
        ```
    -   `getExportDataForBatch()` method changed its interface:
        ```diff
            abstract public function getExportDataForBatch(
                int $domainId,
                 int $lastProcessedId,
                 int $batchSize,
        +        array $fields = [],
        ```
-   `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade` class was changed:
    -   `exportIds()` method changed its interface:
        ```diff
            public function exportIds(
                AbstractIndex $index,
                IndexDefinition $indexDefinition,
                array $restrictToIds,
        +       array $fields = [],
        ```
-   `Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleIndex` class was changed:
    -   `getExportDataForIds()` method changed its interface:
        ```diff
            public function getExportDataForIds(
                int $domainId,
                array $restrictToIds,
        +       array $fields = [],
        ```
    -   `getExportDataForBatch()` method changed its interface:
        ```diff
            public function getExportDataForBatch(
                int $domainId,
                int $lastProcessedId,
                int $batchSize,
        +       array $fields = [],
        ```
-   `Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleIndex` class was changed:
    -   `getExportDataForIds()` method changed its interface:
        ```diff
            public function getExportDataForIds(
                int $domainId,
                array $restrictToIds,
        +       array $fields = [],
        ```
    -   `getExportDataForBatch()` method changed its interface:
        ```diff
            public function getExportDataForBatch(
                int $domainId,
                int $lastProcessedId,
                int $batchSize,
        +       array $fields = [],
        ```
-   `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository` class was changed:
    -   the class now implements `Symfony\Contracts\Service\ResetInterface`
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
                protected readonly HreflangLinksFacade $hreflangLinksFacade,
        +       protected readonly ProductExportFieldProvider $productExportFieldProvider,
        +       protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        +       protected readonly ProductRepository $productRepository,
        +       protected ?array $variantCache = null,
        ```
    -   `getProductsData()` method changed its interface:
        ```diff
            public function getProductsData(
                int $domainId,
                string $locale,
                int $lastProcessedId,
                int $batchSize,
        +       array $fields = [],
        ```
    -   `getProductsDataForIds()` method changed its interface:
        ```diff
            public function getProductsDataForIds(
                int $domainId,
                string $locale,
                array $productIds,
        +       array $fields,
        ```
    -   `extractResult()` method changed its interface:
        ```diff
            protected function extractResult(
                Product $product,
                int $domainId,
                string $locale,
        +       array $fields,
        ```
-   `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex` class was changed:
    -   `getExportDataForIds()` method changed its interface:
        ```diff
            public function getExportDataForIds(
                int $domainId,
                array $restrictToIds,
        +       array $fields = [],
        ```
    -   `getExportDataForBatch()` method changed its interface:
        ```diff
            public function getExportDataForBatch(
                int $domainId,
                int $lastProcessedId,
                int $batchSize,
        +       array $fields = [],
        ```
-   `Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage::__construct` changed its interface:
    ```diff
        public function __construct(
            public readonly int $productId,
    +       public readonly array $exportScopes = [],
    ```
-   `Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher` class was changed:
    -   `dispatchProducts()` method changed its interface:
        ```diff
            public function dispatchProducts(
                array $products,
                ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        +       array $exportScopes = [],
        ```
    -   `dispatchProductIds()` method changed its interface:
        ```diff
            public function dispatchProductIds(
                array $productIds,
                ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        +       array $exportScopes = [],
        ```
    -   `dispatchSingleProductId()` method changed its interface:
        ```diff
            public function dispatchSingleProductId(
                int $productId,
                ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        +       array $exportScopes = [],
        ```
    -   `dispatchAllProducts()` method changed its interface:
        ```diff
            public function dispatchAllProducts(
        +       array $exportScopes = [],
        ```
-   `Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
                protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
        +       protected readonly ProductExportScopeConfigFacade $productExportScopeConfigFacade,
        +       protected readonly ProductElasticsearchProvider $productElasticsearchProvider,
        ```
    -   `recalculate()` method changed its interface:
        ```diff
            public function recalculate(
                array $productIds,
        +       array $exportScopesIndexedByProductId,
        ```
-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    -   product variants cache in `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/763c4ebaf78dd4a2d3c4898d9db7b67afbb849f8) to update your project

#### update Elasticsearch index files structure ([#2567](https://github.com/shopsys/shopsys/pull/2567))

-   elastic index definition files where sorted by order of columns in `ExportRepository` classes. These are not mandatory changes, and you can decide to skip them.
-   add missing properties and remove unnecessary properties from elastic structure
-   changed indent count for json files. These are not mandatory changes, and you can decide to skip them.
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/73e820c317deb44ed01124525658c01fe43ab7de) to update your project

#### rename reserved database function `normalize` to non-reserved name `normalized` ([#3072](https://github.com/shopsys/shopsys/pull/3072))

-   create migration to change `normalize()` function to `normalized()` if you had used it in some indexes, functions, or somewhere else
-   don't forget to rename this function in SQLs in repositories, commands, or somewhere else where is used
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8d8b93f417e95f8e0a3fa0050f2e5042622a84be) to update your project

#### fix seo pages urls ([#3079](https://github.com/shopsys/shopsys/pull/3079))

-   this change adds migration `Version20240325165512` that changes friendly urls. If you do not want to change friendly urls in your project, add it as skipped migration to `migrations-lock.yaml`

#### make delivery address fields not nullable ([#2494](https://github.com/shopsys/shopsys/pull/2494))

-   class `Shopsys\FrameworkBundle\Form\Admin\Customer\DeliveryAddressFormType` was removed
-   check migration `Version20220725104726` if suits your needs
-   following fields in `Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress` are no longer nullable:
    -   `$firstName`
    -   `$lastName`
    -   `$street`
    -   `$city`
    -   `$postcode`
    -   `$country`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e28fb496ee49db7f01db0fc452398b1bc505f1a7) to update your project

#### restrict limit of requests how many can robot ask from eshop ([#2820](https://github.com/shopsys/shopsys/pull/2820))

-   this change adds migration `Version20230919173422` that adds SEO robot limits to your `robots.txt` file. If you do not want to change it in your project, add it as skipped migration to `migrations-lock.yaml`
-   if you have defined rules for all robots (`User-agent: *`), then you have to manually add these lines to your robots.txt
    ```diff
        User-agent: *
    +   Crawl-delay: 3
    +   Request-rate: 300/1m
    ```
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/9ecc1644df0163430b716c6ccbf4c65c91890fdc) to update your project

#### apply new coding standards for alphabetical ordering of YAML files ([#2278](https://github.com/shopsys/shopsys/pull/2278))

-   run `php phing yaml-standards-fix` to apply the new coding standards
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4851cea50a70391c58ee642a56f2b405d8ac5183) to update your project

#### allow open Cypress GUI on Windows with WSL2 ([#3116](https://github.com/shopsys/shopsys/pull/3116))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/3294e859d9b95383003776236e17c621986ceaaf) to update your project

#### fix friendly URLs ([#3115](https://github.com/shopsys/shopsys/pull/3115))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/1dab08828fad18750f194a79a341f9ba135b9efb) to update your project

#### move order and cart related logic from project-base to packages ([#3088](https://github.com/shopsys/shopsys/pull/3088))

-   `Shopsys\FrameworkBundle\Controller\Admin\DefaultController::__construct` interface has changed:
    ```diff
        public function __construct(
            // ...
    +       protected readonly TransferIssueFacade $transferIssueFacade,
    ```
-   `Shopsys\FrameworkBundle\Controller\Admin\ProductController::__construct` interface has changed:
    ```diff
        public function __construct(
            // ...
    -       protected readonly ProductDataFactoryInterface $productDataFactory,
    +       protected readonly ProductDataFactory $productDataFactory,
    ```
-   `Shopsys\FrameworkBundle\Controller\Admin\PromoCodeController::__construct` interface has changed:
    ```diff
        public function __construct(
            // ...
    +       protected readonly PromoCodeMassGeneratedBatchGridFactory $promoCodeMassGeneratedBatchGridFactory,
    ```
-   `Shopsys\FrameworkBundle\Model\Cart\CartFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        -       protected readonly CartWatcherFacade $cartWatcherFacade,
        +       protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        ```
    -   `addProductToCart()` method was removed, use `addProductToExistingCart()` instead
    -   `changeQuantities()` method was removed, use `addProductToExistingCart()` with properly set parameters (`$isAbsoluteQuantity` and `$quantity`) instead
    -   `deleteCartItem()` method was removed, use `removeItemFromExistingCartByUuid()` instead
    -   `deleteCartOfCurrentCustomerUser()` method was removed, use `deleteCart()` instead
    -   `cleanAdditionalData()` method was removed without replacement
    -   `getCartOfCurrentCustomerUserCreateIfNotExists()` method was removed, use `findCartByCustomerUserIdentifier()` instead
    -   `getQuantifiedProductsOfCurrentCustomer()` method was removed, use `Cart::getQuantifiedProducts()` instead
-   `Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade` class was removed, use `Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade` instead
-   `Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview::__construct` interface has changed:
    ```diff
        public function __construct(
            protected readonly array $quantifiedProductsByIndex,
            protected readonly array $quantifiedItemsPricesByIndex,
            protected readonly array $quantifiedItemsDiscountsByIndex,
            protected readonly Price $productsPrice,
            protected readonly Price $totalPrice,
    +       protected readonly Price $totalPriceDiscount,
    +       protected readonly Price $totalPriceWithoutDiscountTransportAndPayment,
            protected readonly ?Transport $transport = null,
            protected readonly ?Price $transportPrice = null,
            protected readonly ?Payment $payment = null,
            protected readonly ?Price $paymentPrice = null,
            protected readonly ?Price $roundingPrice = null,
    -       $promoCodeDiscountPercent = null,
    +       protected readonly ?string $promoCodeDiscountPercent = null,
    +       protected readonly ?Store $personalPickupStore = null,
    +       protected readonly ?PromoCode $promoCode = null,
    ``
    ```
-   `Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewCalculation` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        ```
    -   `calculatePreview()` method changed its interface:
        ```diff
            public function calculatePreview(
                Currency $currency,
                int $domainId,
                array $quantifiedProducts,
                ?Transport $transport = null,
                ?Payment $payment = null,
                ?CustomerUser $customerUser = null,
                ?string $promoCodeDiscountPercent = null,
        +       ?Store $personalPickupStore = null,
        +       ?PromoCode $promoCode = null,
        ```
-   `Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory` class was changed:
    -   `create()` method changed its interface:
        ```diff
            public function create(
                Currency $currency,
        -       $domainId,
        +       int $domainId,
                array $quantifiedProducts,
                ?Transport $transport = null,
                ?Payment $payment = null,
                ?CustomerUser $customerUser = null,
        +       ?string $promoCodeDiscountPercent = null,
        +       ?Store $personalPickupStore = null,
        +       ?PromoCode $promoCode = null,
        +   ): OrderPreview {
        ```
    -   `createForCurrentUser()` method was removed, use `create()` instead
-   `Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly PromoCodeFacade $promoCodeFacade,
        -       protected readonly RequestStack $requestStack,
        +       protected readonly PromoCodeProductRepository $promoCodeProductRepository,
        +       protected readonly Domain $domain,
        +       protected readonly ProductPromoCodeFiller $productPromoCodeFiller,
        +       protected readonly PromoCodeLimitResolver $promoCodeLimitByCartTotalResolver,
        +       protected readonly CurrentCustomerUser $currentCustomerUser,
        +       protected readonly PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
        ```
    -   `PROMO_CODE_SESSION_KEY` constant was removed
    -   `getValidEnteredPromoCodeOrNull()` method was removed, use `getValidatedPromoCode()` instead
    -   `setEnteredPromoCode()` method was removed, use `Cart::applyPromoCode()` instead
    -   `removeEnteredPromoCode()` method was removed, use `Cart::removePromoCodeById()` instead
-   `Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory` class was changed
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
                protected readonly GridFactory $gridFactory,
        +       protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        +       protected readonly PromoCodeLimitRepository $promoCodeLimitRepository,
        ```
    -   `create()` method changed its interface:
        ```diff
        -   public function create($withEditButton = false)
        +   public function create(bool $withEditButton = true, ?string $search = null): Grid
        ```
-   `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode::$percent` property (and `getPercent()` method) was removed, use `PromoCodeLimit::$discount` instead
-   `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData::$percent` property was removed, use `PromoCodeLimit::$discount` instead
-   `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade` class was changed:
    -   the methods are now type-hinted
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
                protected readonly PromoCodeRepository $promoCodeRepository,
                protected readonly PromoCodeFactoryInterface $promoCodeFactory,
        +       protected readonly PromoCodeLimitRepository $promoCodeLimitRepository,
        +       protected readonly PromoCodeProductRepository $promoCodeProductRepository,
        +       protected readonly PromoCodeCategoryRepository $promoCodeCategoryRepository,
        +       protected readonly PromoCodeProductFactory $promoCodeProductFactory,
        +       protected readonly PromoCodeCategoryFactory $promoCodeCategoryFactory,
        +       protected readonly PromoCodeBrandRepository $promoCodeBrandRepository,
        +       protected readonly PromoCodeBrandFactory $promoCodeBrandFactory,
        +       protected readonly PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
        +       protected readonly PromoCodePricingGroupFactory $promoCodePricingGroupFactory,
        +       protected readonly PromoCodeFlagRepository $promoCodeFlagRepository,
        +       protected readonly HashGenerator $hashGenerator,
        ```
    -   `findPromoCodeByCode()` method was removed, use `findPromoCodeByCodeAndDomain()` instead
-   `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository::findByCode()` method was removed, use `findByCodeAndDomainId()` instead
-   `Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
        +       protected readonly PromoCodeLimitResolver $promoCodeLimitResolver,
                protected readonly PriceCalculation $priceCalculation,
                protected readonly Rounding $rounding,
        +       protected readonly PromoCodeApplicableProductsTotalPriceCalculator $promoCodeApplicableProductsTotalPriceCalculator,
        ```
    -   `calculateDiscountsRoundedByCurrency()` method was removed, use `calculateDiscountsPerProductRoundedByCurrency()` instead
-   `Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface` interface was removed
-   `Shopsys\FrameworkBundle\Model\Store\StoreIdsToStoresTransformer` class was removed
-   `Shopsys\FrontendApiBundle\Component\Constraints\PaymentCanBeUsed` class was removed
-   `Shopsys\FrontendApiBundle\Component\Constraints\PaymentCanBeUsedValidator` class was removed
-   `Shopsys\FrontendApiBundle\Component\Constraints\PaymentInOrder` class was renamed to `Shopsys\FrontendApiBundle\Component\Constraints\PaymentInExistingOrder`
-   `Shopsys\FrontendApiBundle\Component\Constraints\PaymentInOrderValidator` class was renamed to `Shopsys\FrontendApiBundle\Component\Constraints\PaymentInExistingOrderValidator`
-   `Shopsys\FrontendApiBundle\Component\Constraints\PaymentTransportRelationValidator::__construct` method changed its interface:
    ```diff
        public function __construct(
    -       protected readonly PaymentFacade $paymentFacade,
    -       protected readonly TransportFacade $transportFacade,
    +       protected readonly CurrentCustomerUser $currentCustomerUser,
    +       protected readonly CartApiFacade $cartApiFacade,
    ```
-   `Shopsys\FrontendApiBundle\Component\Constraints\ProductCanBeOrdered` class was removed
-   `Shopsys\FrontendApiBundle\Component\Constraints\ProductCanBeOrderedValidator` class was removed
-   `Shopsys\FrontendApiBundle\Component\Constraints\TransportCanBeUsed` class was removed
-   `Shopsys\FrontendApiBundle\Component\Constraints\TransportCanBeUsedValidator` class was removed
-   `Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
                protected readonly CountryFacade $countryFacade,
                protected readonly ProductFacade $productFacade,
        +       protected readonly StoreFacade $storeFacade,
        ```
    -   `createQuantifiedProductsFromArgument()` method was removed, use `Cart::getQuantifiedProducts()` instead
-   `Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
                protected readonly PlacedOrderMessageDispatcher $placedOrderMessageDispatcher,
        +       protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        +       protected readonly DeliveryAddressDataFactory $deliveryAddressDataFactory,
        +       protected readonly DeliveryAddressFactory $deliveryAddressFactory,
        +       protected readonly NewsletterFacade $newsletterFacade,
        +       protected readonly PromoCodeLimitResolver $promoCodeLimitResolver,
        ```
    -   `placeOrder()` method changed its interface:
        ```diff
            public function placeOrder(
                 OrderData $orderData,
                 array $quantifiedProducts,
        +        ?PromoCode $promoCode = null,
        +        ?DeliveryAddress $deliveryAddress = null,
        + ): Order {
        ```
    -   `createOrderPreview()` method was removed
-   `Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportsQuery` class was changed:
-   `__construct()` method changed its interface:
    ```diff
        public function __construct(
            protected readonly TransportFacade $transportFacade,
    -       protected readonly PaymentFacade $paymentFacade,
    +       protected readonly CartApiFacade $cartApiFacade,
    +       protected readonly CurrentCustomerUser $currentCustomerUser,
    ```
-   `transportsQuery()` method changed its interface:

    ```diff
    -    public function transportsQuery(): array
    -    public function transportsQuery(?string $cartUuid = null): array
    ```

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    -   `Cart` properties (and all the related logic):
        -   `$promoCodes`
        -   `$transport`
        -   `$transportWatchedPrice`
        -   `$payment`
        -   `$paymentWatchedPrice`
        -   `$paymentGoPayBankSwift`
        -   `$pickupPlaceIdentifier`
    -   `CartItem::$uuid` property
    -   `Order` properties (and all the related logic):
        -   `$pickupPlaceIdentifier`
        -   `$trackingNumber`
    -   `Administrator::$transferIssuesLastSeen` property (and all the related logic)
    -   `Transport` properties (and all the related logic):
        -   `$trackingUrl`
        -   `$trackingInstruction`
        -   `$transportType`
        -   `$maxWeight`
    -   `Product::$weight` property (and all the related logic)
    -   `PromoCode` properties (and all the related logic):
        -   `$discountType`
        -   `$registeredOnly`
        -   `$domainId`
        -   `$datetimeValidFrom`
        -   `$datetimeValidTo`
        -   `$remainingUses`
        -   `$massGenerate`
        -   `$prefix`
        -   `$massGenerateBatchId`
    -   `PromoCodeBrand` entity (and all the related logic)
    -   `PromoCodeCategory` entity (and all the related logic)
    -   `PromoCodeProduct` entity (and all the related logic)
    -   `PromoCodeFlag` entity (and all the related logic)
    -   `PromoCodePricingGroup` entity (and all the related logic)
    -   `PromoCodeLimit` entity (and all the related logic)
    -   `Transfer` and `TransferIssue` entities (and all the related logic)
-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    -   `CartMutation` and all the related logic
    -   `CartQuery` and all the related logic
    -   `CreateOrderMutation` and all the related logic
    -   see classes in the `Shopsys\FrontendApiBundle\Model\Cart` namespace
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/3fae840765556f5dba025da132631a336c5461bb) to update your project

#### fix issues reported by phpstan ([#3134](https://github.com/shopsys/shopsys/pull/3134))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/d587b64b1d31a3fc5621cdde5db3ae727671d026) to update your project

#### fix builds ([#3131](https://github.com/shopsys/shopsys/pull/3131))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/62a1b33c77c88a4e93dcf4d4f4a689f0e00a17f5) to update your project

#### create order with preselected delivery address ([#3105](https://github.com/shopsys/shopsys/pull/3105))

-   `Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS` constant was renamed to `VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS_WITHOUT_PRESELECTED`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/cbc38814e60916e8c78c95743dabfe418bd47c5a) to update your project

#### dispatch product stocks export after Setting::TRANSFER_DAYS_BETWEEN_STOCKS is set ([#3104](https://github.com/shopsys/shopsys/pull/3104))

-   `Shopsys\FrameworkBundle\Model\Stock\StockSettingsDataFacade::__construct` interface has changed:
    ```diff
        public function __construct(
            // ...
    +       protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ```

#### remove unused operator info ([#3133](https://github.com/shopsys/shopsys/pull/3133))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c1c7b0c61780627e6d7ffc199e1c760fc20158a) to update your project

#### display error messages in admin if legal conditions articles are not set ([#3128](https://github.com/shopsys/shopsys/pull/3128))

-   `Shopsys\FrameworkBundle\Controller\Admin\DefaultController::__construct` interface has changed:

    ```diff
        public function __construct(
            // ...
    +       protected readonly Domain $domain,
    ```

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4e70464973f2e1ede5b3c1aff7f84eee6dfa3037) to update your project

#### load products iteratively while generating image sitemaps ([#3144](https://github.com/shopsys/shopsys/pull/3144))

-   `Shopsys\FrameworkBundle\Model\Product\ProductRepository` class was changed:
    -   `getAllOfferedProducts()` method was removed, use `getAllOfferedProductsPaginated()` instead

#### rename variable differentDeliveryAddress into isDeliveryAddressDifferentFromBilling ([#3161](https://github.com/shopsys/shopsys/pull/3161))

-   FE API: `OrderDecorator.types.yaml` and `OrderInputDecorator.types.yaml`: differentDeliveryAddress was renamed into more suitable isDeliveryAddressDifferentFromBilling
-   `Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS_WITHOUT_PRESELECTED` constant was renamed to `VALIDATION_GROUP_IS_DELIVERY_ADDRESS_DIFFERENT_FROM_BILLING_WITHOUT_PRESELECTED`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/3096101a49d6099602aa4d6475948ae97f27b319) to update your project

#### include domain sale exclusion in product querying more appropriately ([#3141](https://github.com/shopsys/shopsys/pull/3141))

-   `Shopsys\FrameworkBundle\Model\Product\ProductRepository::__construct` interface has changed:

    ```diff
        public function __construct(
            // ...
    +       protected readonly QueryBuilderExtender $queryBuilderExtender,
    ```

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/9903345fe7ffde8925d9c3d215b01d1cab6852c0) to update your project

#### add a way to use Blackfire profiling ([#3168](https://github.com/shopsys/shopsys/pull/3168))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/c2fbe39db430e1b38975e3efb9fe4ce4ca0b71de) to update your project

#### parameter data fixture refactoring ([#3170](https://github.com/shopsys/shopsys/pull/3170))

-   parameter and product parameter value definition in demo data is now simpler and more readable
-   demo data parameters are now created exclusively in ParameterDataFixture, and it is no longer possible to ad hoc create not existing parameter in ProductDataFixture when defining product parameter values
-   ParameterColorValueDataFixture is added to handle assigning hex values to color parameters
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/58abc5c4ad897cb8da030f1245aa51fbec92d71d) to update your project

#### refactor the place order process ([#3084](https://github.com/shopsys/shopsys/pull/3084))

-   see the specialized upgrade note in [upgrade-order-processing.md](./upgrade-order-processing.md)
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/9cdf71c2209c02a4870b8db4f9022d9867855c9c) to update your project

#### rename user-consent related code accordingly ([#3181](https://github.com/shopsys/shopsys/pull/3181))

-   setting values in DB was renamed from `cookiesArticleId` to `userConsentPolicyArticleId` (via migration `Version20240527121326`)
-   controller `Shopsys\FrameworkBundle\Controller\Admin\CookiesController` was renamed to `Shopsys\FrameworkBundle\Controller\Admin\UserConsentPolicyController`
    -   route `/cookies/setting/` (`admin_cookies_setting`) was changed to `/user-consent-policy/setting/` (`admin_userconsentpolicy_setting`)
    -   template `@ShopsysFramework/Admin/Content/Cookies/setting.html.twig` was moved to `@ShopsysFramework/Admin/Content/UserConsentPolicy/setting.html.twig`
-   form type `Shopsys\FrameworkBundle\Form\Admin\Cookies\CookiesSettingFormType` was renamed to `Shopsys\FrameworkBundle\Form\Admin\UserConsentPolicy\UserConsentPolicySettingFormType`
-   constant `Shopsys\FrameworkBundle\Component\Setting\Setting::COOKIES_ARTICLE_ID` was renamed to `Shopsys\FrameworkBundle\Component\Setting\Setting::USER_CONSENT_POLICY_ARTICLE_ID`
-   class `Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade` was renamed to `Shopsys\FrameworkBundle\Model\UserConsentPolicy\UserConsentPolicyFacade`
    -   class no longer accepts `$environment` as a first argument in constructor
    -   method `isCookiesConsentGiven()` was removed without a replacement
    -   class is now strictly typed
-   class `Shopsys\FrameworkBundle\Twig\CookiesExtension` was removed
-   administrator roles `ROLE_COOKIES_FULL` and `ROLE_COOKIES_VIEW` were renamed to `ROLE_USER_CONSENT_POLICY_FULL` and `ROLE_USER_CONSENT_POLICY_VIEW`
    -   roles are renamed in migration `Version20240527121326`
-   graphql query `cookiesArticleQuery` was renamed to `userConsentPolicyArticleQuery`
    -   method `Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticleQuery::cookiesArticleQuery` was renamed to `userConsentPolicyArticleQuery`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6534bafb99697ac25d74a1ef376836141f26185b) to update your project

#### fix search testing blog article demo data ([#3182](https://github.com/shopsys/shopsys/pull/3182))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e8acbcadf531d833ee443a833adafe6bc782e7cb) to update your project

#### stop processing images by PHP to avoid decreasing quality ([#3169](https://github.com/shopsys/shopsys/pull/3169))

-   `Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor` class was changed:
    -   all the methods and constants are now strictly typed
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
        -        protected readonly ImageManager $imageManager,
                 protected readonly FilesystemOperator $filesystem,
        ```
    -   `createInterventionImage()` method was removed
    -   `resize()` method was removed
-   `Shopsys\FrameworkBundle\Component\Image\Processing\ImageThumbnailFactory` class was removed
-   `Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
        -        protected readonly ImageThumbnailFactory $imageThumbnailFactory,
        +        protected readonly ImageProcessor $imageProcessor,
        ```
    -   `DEFAULT_ICON_TYPE` constant is now strictly typed
    -   `IMAGE_THUMBNAIL_QUALITY` constant was removed
    -   all the methods are now strictly typed
-   `Shopsys\FrameworkBundle\Component\Domain\DomainFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
        -       $domainImagesDirectory,
        +       protected readonly string $domainImagesDirectory,
                protected readonly Domain $domain,
        -       protected readonly DomainIconResizer $domainIconResizer,
        -       FilesystemOperator $fileSystem,
        +       protected readonly DomainIconProcessor $domainIconProcessor,
        +       protected readonly FilesystemOperator $filesystem,
                protected readonly FileUpload $fileUpload,
        +       protected readonly ImageProcessor $imageProcessor,
        ```
    -   all the methods are now strictly typed
-   `Shopsys\FrameworkBundle\Component\Domain\DomainIconResizer` class was removed, use `Shopsys\FrameworkBundle\Component\Domain\DomainIconProcessor` instead
-   `Shopsys\FrameworkBundle\Component\Domain\DomainIconProcessor` class was changed:
    -   `convertToDomainIconFormatAndSave()` method was renamed to `saveIcon()`
-   `Shopsys\FrameworkBundle\Component\Image\Processing\Exception\OriginalSizeImageCannotBeGeneratedException` class was removed
-   `Shopsys\FrameworkBundle\Twig\ImageExtension` class was changed:
    -   `getImageUrl()` method changed its interface:
        ```diff
            public function getImageUrl(
                $imageOrEntity,
        -       ?string $type = null
        +       array $attributes
        ```
-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    -   `imageuploadFields.html.twig` Twig template extension
    -   `Advert/listGrid.html.twig` Twig template extension
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/9d623b34cb9ed1a5bb87ddd60ef38d53fa326f1f) to update your project

#### a little spring cleanup ([#3157](https://github.com/shopsys/shopsys/pull/3157))

-   `Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly string $environment = EnvironmentType::PRODUCTION,
        ```
-   `Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade` class was changed:
    -   `getAllOfferedBestsellingProducts()` method was removed, use `CachedBestsellingProductFacade::getAllOfferedBestsellingProductIds()` instead
    -   `getSortedProducts()` method was removed without replacement
-   `Shopsys\FrameworkBundle\Twig\LocalizationExtension` class was changed:
    -   `getLocaleFlagHtml()` method changed its interface:
        ```diff
        -   getLocaleFlagHtml($locale, $showTitle = true)
        +   getLocaleFlagHtml(string $locale, bool $showTitle = true, int $width = 16, int $height = 11): string
        ```
-   `Shopsys\FrameworkBundle\Twig\PriceExtension` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        ```
-   `Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestListener` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
        +       protected readonly Traversable $transactionalMasterRequestConditionProviders,
                protected readonly EntityManagerInterface $em,
        ```
-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    -   `FormBuilderHelper` class
    -   `ArrayWithPaginationDataSource` class
    -   `LocaleHelper` class
    -   `DomainController::localeTabsAction()` method
    -   `RedisController` class
    -   `UploadedFileController` class
    -   `CsrfExtension` class
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4b09aae9bf3b35e452103538c136ab27f585fd4d) to update your project

#### update easy-coding-standard to version 12.2 ([#3192](https://github.com/shopsys/shopsys/pull/3192))

-   update configuration file to new version
-   skip rules are now defined in the separate `ecs-skip-rule.php` file
-   paths to check are now defined directly in the `ecs.php` file
-   fixer `RedundantMarkDownTrailingSpacesFixer` was removed as markdown files are formatted by prettier
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/ca3c80b5c5a7f4c3b54c004aa2c246747ae1ba7d) to update your project

#### fix VatDeletionCronModule ([#3195](https://github.com/shopsys/shopsys/pull/3195))

-   `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceRecalculator` class was removed
-   `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
                protected readonly ProductRepository $productRepository,
        -       protected readonly ProductInputPriceRecalculator $productInputPriceRecalculator,
                protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        ```
    -   the class is now strictly typed
-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    -   `ProductInputPriceFacade::replaceBatchVatAndRecalculateInputPrices()` logic
    -   `Product::getProductDomains()` method
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/11e4f006adf7dd7b63a475c9acff76764b9e24f9) to update your project

#### improve OrderSequence related types ([#3206](https://github.com/shopsys/shopsys/pull/3206))

-   class `Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceFactory` was removed
-   interface `Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceFactoryInterface` was removed
-   method `Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository::getNextNumber()` now returns `string`
-   class `Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository` is now strictly typed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/259288fcbdf88101ea71d5a6f431007c78771d69) to update your project

#### minor improvements related to deployment ([#3201](https://github.com/shopsys/shopsys/pull/3201))

-   not used database tables `advert_category` and `entity` were removed in migration `Version20240604152553`
-   update `shopsys/deployment` package to a new major version
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/d036cf2ce696392927b5deefa2747b391dc8a9f3) to update your project

#### add nominal promo code to demo data ([#3197](https://github.com/shopsys/shopsys/pull/3197))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/34bce64eff86304aeaf993c85d2e5de34bafb59a) to update your project

#### product data fixture refactoring ([#3187](https://github.com/shopsys/shopsys/pull/3187))

-   There was some unnecessary repeating in product data fixtures - it is now simplified in places where it made sense for project base purposes.
-   `ProductDemoDataFactory` class was added as a foundation for creating demo data templates for certain groups of products that has a lot of common data which is not very applicable for project base demo data but could be in real projects.

#### implemented registration and login via social networks ([#3154](https://github.com/shopsys/shopsys/pull/3154))

-   implemented are login via Facebook, Google, and Seznam
-   add url `{eshop_domain}/social-network/login/{type}` as redirect url in you social network. `type` is implemented service e.g.: `google`, `facebook`, `seznam`
-   see the [docs](https://docs.shopsys.com/en/15.0/integration/social-networks/) for more information about the functionality
-   `Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade::isAdministratorLoggedAsCustomer()` method was removed
-   `Shopsys\FrameworkBundle\Controller\Admin\AdministratorController` constructor was changed:
    ```diff
        public function __construct(
            // ...
    +       protected readonly AdministratorTwoFactorAuthenticationFacade $administratorTwoFactorAuthenticationFacade,
    ```
-   `Shopsys\FrameworkBundle\Controller\Admin\CustomerController` class was changed:
    -   `__construct()` method changed its interface:
    ```diff
        public function __construct(
            // ...
            protected readonly OrderFacade $orderFacade,
    -       protected readonly LoginAsUserFacade $loginAsUserFacade,
            protected readonly DomainRouterFactory $domainRouterFactory,
            // ...
    +       protected readonly LoginAdministratorAsUserUrlProvider $loginAdministratorAsUserUrlProvider,
    ```
    -   `LOGIN_AS_TOKEN_ID_PREFIX` constant was removed
    -   `loginAsUserAction()` method was removed, use `Shopsys\FrontendApiBundle\Controller\CustomerUserController::loginAsCustomerUserAction()` instead
    -   `getSsoLoginAsCustomerUserUrl()` method was removed, use `Shopsys\FrontendApiBundle\Model\Security\LoginAdministratorAsUserUrlProvider::getSsoLoginAsCustomerUserUrl()` instead
-   `Shopsys\FrameworkBundle\Model\Administrator\Administrator` class was changed:
    -   the class now implements `Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface` and `Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface`
    -   `checkRolesContainAdminRole()` method was removed
-   `Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade` constructor was changed:
    ```diff
        public function __construct(
            // ...
    +       protected readonly CustomerActivationMail $customerActivationMail,
    ```
-   `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade` class was changed:
    -   `__construct()` changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly NewsletterFacade $newsletterFacade,
        +       protected readonly HashGenerator $hashGenerator,
        ```
    -   `edit()` function is now public
    -   `addRefreshTokenChain()` method changed its interface:
        ```diff
            public function addRefreshTokenChain(
                // ...
        +       ?Administrator $administrator,
        ```
-   `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade` class was changed:
    -   `createCustomerUserRefreshTokenChain()` method changed its interface:
        ```diff
            public function createCustomerUserRefreshTokenChain(
                // ...
        +       ?Administrator $administrator,
        ```
    -   `findCustomersTokenChainByCustomerUserAndSecretChain()` method was removed, use `findCustomersTokenChainByCustomerUserAndSecretChainAndDeviceId()` instead
-   `Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade` class was changed:
    -   `__construct()` changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly MailTemplateBuilder $mailTemplateBuilder,
        ```
-   `Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository` class was changed:
    -   `__construct()` changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly Localization $localization,
        ```
-   `Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly MergeCartFacade $mergeCartFacade,
        +       protected readonly TokensDataFactory $tokensDataFactory,
        +       protected readonly LoginResultDataFactory $loginResultDataFactory,
        ```
    -   `loginMutation()` method now returns an instance of `LoginResultData`
-   `Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation` class was changed:
    -   `__contsruct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly RegistrationFacade $registrationFacade,
        +       protected readonly RegistrationDataFactory $registrationDataFactory,
        +       protected readonly MergeCartFacade $mergeCartFacade,
        +       protected readonly OrderApiFacade $orderFacade,
        +       protected readonly LoginResultDataFactory $loginResultDataFactory,
        +       protected readonly TokensDataFactory $tokensDataFactory,
        ```
    -   `registerMutation()` now returns an instance of `LoginResultData`
-   `Shopsys\FrontendApiBundle\Model\Mutation\Login\RefreshTokensMutation` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly TokensDataFactory $tokensDataFactory,
        ```
    -   `refreshTokensMutation()` now returns an instance of `TokensData`
-   `Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade::__construct()` changed its interface:
    ```diff
        public function __construct(
            // ...
    +       protected readonly EntityManagerInterface $em,
    ```
-   `Shopsys\FrontendApiBundle\Model\Token\TokenFacade` clas was changed:
    -   `createAccessTokenAsString()` method changed its interface:
        ```diff
            public function createAccessTokenAsString(
                // ...
        +       ?Administrator $administrator = null,
        ```
    -   `createRefreshTokenAsString()` method changed its interface:
        ```diff
            public function createRefreshTokenAsString(
                // ...
        +       ?Administrator $administrator = null,
        ```
    -   `generateRefreshTokenByCustomerUserAndSecretChain()` method was removed, use `generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId()` instead
-   `Shopsys\FrontendApiBundle\Model\User\FrontendApiUser::__construct()` changed its interface:
    ```diff
        public function __construct(
            // ...
    +       protected readonly ?string $administratorUuid,
    ```
-   `Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade` was removed, use `Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade` instead
-   `Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory::__construct()` changed its interface:

    ```diff
         public function __construct(
            protected readonly CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
    +       protected readonly BillingAddressDataFactory $billingAddressDataFactory,
    +       protected readonly \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory,
    ```

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    -   `AdministratorRoleGroup` entity, `Administrator::$roleGroup` and all the related logic
    -   `Roles`, `RolesType`, `MenuItemsGrantedRolesSetting`, and all the related logic
        -   removing not granted items from the menu is now moved from `SideMenuConfigurationSubscriber` to the new `MenuItemsGrantedRolesSubscriber` class
    -   `Administrator::$uuid` property
    -   `Administrator` two factor authentication feature
    -   not nullable setting for `BillingAddress` properties `$street`, `$city`, `$postcode`, and `$country`
    -   `BillingAddress::$activated` property and all the related logic
    -   `DeliveryAddress::$uuid` property and all the related logic
    -   `CustomerUser::$newsletterSubscription` and all the related logic
    -   `CustomerUserRefreshTokenChain::$administrator`
    -   `RemoveOldCustomerUserRefreshTokenChainsCronModule` and all the related logic
    -   `CustomerUserUpdateDataFactory::createAmendedByOrder()`
    -   `MailTemplate::$orderStatus` property and all the related logic
    -   `MailTemplateBuilder` class
    -   `MailSetting` class constants `MAIL_FACEBOOK_URL`, `MAIL_INSTAGRAM_URL`, `MAIL_YOUTUBE_URL`, `MAIL_LINKEDIN_URL`, `MAIL_TIKTOK_URL`, and `MAIL_FOOTER_TEXT` and all the related logic
    -   `Order::setCustomerUser()` method
-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    -   `CustomerController::loginAsCustomerUserAction()` and all the related logic
        -   `Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade`
        -   `admin_customer_loginascustomeruser` route was renamed to `admin_customeruser_loginascustomeruser`
    -   `CustomerUserUpdateDataFactory::createFromRegistrationData()`
    -   `OrderApiFacade::ONE_HOUR_REGISTRATION_WINDOW` constant
    -   `OrderApiFacade` methods:
        -   `getByOrderNumberAndCustomerUser()`
        -   `findLastOrderByCustomerUser()`
        -   `pairCustomerUserWithOrderByOrderUuid()`
    -   `OrderRepository` methods:
        -   `getByOrderNumberAndCustomerUser()`
        -   `findByOrderNumberAndCustomerUser()`
        -   `getByUuidAndCustomerUser()`
    -   `RegistrationData` class
    -   `RegistrationDataFactory` class
    -   `RegistrationFacade` class
    -   `TokenAuthenticator::HEADER_AUTHORIZATION` constant
    -   `TokenAuthenticator` methods:
        -   `getCredentials()`
        -   `onAuthenticationFailure()`
    -   `TokenFacade` methods:
        -   `createAccessTokenAsString()`
        -   `createRefreshTokenAsString()`
        -   `generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId()`
    -   `CustomerUserMutation` methods:
        -   `registerMutation()`
        -   `changePersonalDataMutation()`
        -   `computeValidationGroups()`
    -   `RefreshTokensMutation::refreshTokensMutation()`
    -   `CustomerUserRefreshTokenChainFacade` methods:
        -   `findCustomersTokenChainByCustomerUserAndSecretChainAndDeviceId()`
        -   `removeCustomerRefreshTokenChain()`
    -   `FrontendApiUser::CLAIM_ADMINISTRATOR_UUID` constant
    -   `FrontendApiUser::$administratorUuid` property
    -   `LoginMutation::loginWithResultMutation()` logic was moved into `LoginMutation::loginMutation()`
    -   `CustomerUserResolverMap`
    -   `CompanyCustomerUser`, `RegularCustomerUser` graphql types
    -   `CurrentCustomerUser` graphql type fields: `street`, `city`, `postcode`, `country`, `defaultDeliveryAddress`, `deliveryAddresses`, and `pricingGroup`
-   see also [#3276](https://github.com/shopsys/shopsys/pull/3276) and [#3277](https://github.com/shopsys/shopsys/pull/3277) where the functionality was further extended
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/22ee0ba15cda535c97b0894741f5e648527dc52c) to update your project
-   see also [project-base diff](https://www.github.com/shopsys/project-base/commit/da2d84850c5c4c7af0cd6787a12781c27a8660d5) of [#3315](https://github.com/shopsys/shopsys/pull/3315) (the PR fixes missing environment variable in `deploy-project.sh`)
-   see also [project-base diff](https://www.github.com/shopsys/project-base/commit/58a8e19f1deb14b2cacd4f1157630f60f174973e) of [#3348](https://github.com/shopsys/shopsys/pull/3348) (the PR fixes missing environment variables default values in `app/.env`)

#### move navigation feature from project-base to the packages ([#3218](https://github.com/shopsys/shopsys/pull/3218))

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework and frontend-api packages:
    -   `NavigationItem` entity and all the related logic
    -   `NavigationQuery`
    -   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b66b2763c9ea4f6b58bc6555282bcdcae4ee7db6) to update your project

#### change free transport limit in demo data ([#3199](https://github.com/shopsys/shopsys/pull/3199))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/70a1bdfd46436f906498d528e5c5a1b7b7b168ee) to update your project

#### add packeta type transport to demo data ([#3198](https://github.com/shopsys/shopsys/pull/3198))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6311bb5328e2c21d4b918cd5661da571433ec472) to update your project

#### fix Luigi's Box filters might miss already selected parameters ([#3220](https://github.com/shopsys/shopsys/pull/3220))

-   `\Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper,
                protected readonly TypeInLuigisBoxEnum $typeInLuigisBoxEnum,
                protected readonly RecommendationTypeEnum $recommendationTypeEnum,
        +       protected readonly FacetFactory $facetFactory,
        +       protected readonly ParameterFacade $parameterFacade,
            ) {
        ```
    -   `createForSearch()` method changed its interface:
        ```diff
            public function createForSearch(
                string $type,
                int $limit,
                int $page,
                Argument $argument,
                array $luigisBoxFilter = [],
        +       array $facetNames = [],
            ): LuigisBoxBatchLoadData {
        ```
    -   `getFacetNamesByType()` method has been replaced by `\Shopsys\LuigisBoxBundle\Model\Facet\FacetFactory::getDefaultFacetNamesByType()`
-   `\Shopsys\LuigisBoxBundle\Model\Product\ProductSearchResultsProvider` class was changed:

    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                string $enabledDomainIds,
                protected readonly ProductConnectionFactory $productConnectionFactory,
                protected readonly LuigisBoxClient $client,
                protected readonly Domain $domain,
                protected readonly ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper,
                protected readonly DataLoaderInterface $luigisBoxBatchLoader,
                protected readonly LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory,
                protected readonly ProductFilterDataMapper $productFilterDataMapper,
        +       protected readonly FacetFactory $facetFactory,
            ) {
        ```

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8b592fad1a89e43770bf8bb4e8b29c317e0666e3) to update your project

#### removed ReadyCategorySeoMixDataForForm ([#3214](https://github.com/shopsys/shopsys/pull/3214))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/957b25e3bc8a059579438affce95b98b3d15bbd0) to update your project

#### update composer dependencies to newer versions ([#3213](https://github.com/shopsys/shopsys/pull/3213))

-   PHPUnit has been updated to version 11 with many other dependencies
    -   many changes have been introduced since previously used version 9 e.g. configuration options, deprecated or removed methods, deprecated doc-blocks, etc.
    -   see https://github.com/sebastianbergmann/phpunit/blob/11.1/DEPRECATIONS.md and https://github.com/sebastianbergmann/phpunit/blob/10.5/DEPRECATIONS.md for deprecations in PHPUnit that you need to solve in your tests
    -   see #project-base diff to see changes you might need to apply in your tests
-   `commerceguys/intl` has been updated to the latest version
    -   `IntlCurrencyRepository` and `NumberFormatterExtension` class methods have updated their interfaces to include strict types, you will need to update your usages of such methods in your project
    -   `Phing` is no longer direct dependency, but is downloaded separately via `php project-base/app/app/downloadPhing.php` command. This is done directly in `composer.json` file, so you should follow changes in project-base to update `composer.json`, `phing` and other files accordingly
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/629ac9f4f31a0744ede722dbc42d3a1d7af9b3a8) to update your project

#### all Shopsys packages are included in ClassExtensionRegistry so annotation fixer now works for all Shopsys packages ([#3230](https://github.com/shopsys/shopsys/pull/3230))

-   `Shopsys\FrameworkBundle\Command\ExtendedClassesAnnotationsCommand` class was changed:
    -   method `replaceFrameworkWithProjectAnnotations()` renamed to `replaceShopsysWithProjectAnnotations()`
-   `Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
        -       protected readonly string $frameworkRootDir,
        -       protected readonly array $entityExtensionMap = [],
        +       protected readonly array $entityExtensionMap,
        +       protected readonly array $packagesRegistry,
            ) {
        ```
    -   run `php phing annotations-fix` to apply changes in your project
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/a6da4692763ace5d02e5508e20c350fd8d2d0478) to update your project

#### move part of the parameter and filters functionality from project-base to framework and frontend-api packages ([#3221](https://github.com/shopsys/shopsys/pull/3221))

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework and frontend-api packages:
    -   `Parameter` properties (and all the related logic):
        -   `$parameterType`
        -   `$orderingPriority`
        -   `$unit`
    -   `ParameterValue` properties (and all the related logic):
        -   `$rgbHex`
        -   `$colourIcon`
        -   `$unit`
        -   `$text` is now text instead of string(100)
-   `Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
        -       ?Parameter $parameter = null,
        +       protected readonly Parameter $parameter,
                protected readonly array $values = [],
            ) {
        ```
-   `Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
                protected readonly ParameterRepository $parameterRepository,
                protected readonly ParameterFactoryInterface $parameterFactory,
                protected readonly EventDispatcherInterface $eventDispatcher,
                protected readonly CategoryParameterRepository $categoryParameterRepository,
        +       protected readonly UploadedFileFacade $uploadedFileFacade,
            ) {
        ```
-   `Shopsys\FrontendApiBundle\Model\Product\Filter\FlagFilterOption` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                public readonly Flag $flag,
                public readonly int $count,
                public readonly bool $isAbsolute,
        +       public bool $isSelected,
            ) {
        ```
-   `Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterFilterOption` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                public readonly Parameter $parameter,
                public readonly array $values,
            +   public bool $isCollapsed,
            +   public bool $isSelectable,
            +   public ?float $selectedValue = null,
            ) {
        ```
-   `Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterFilterOption` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
        -       ParameterValue $parameterValue,
        +       public readonly ParameterValue $parameterValue,
                public readonly int $count,
                public readonly bool $isAbsolute,
        +       public readonly bool $isSelected,
            ) {
        ```
-   `Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly ModuleFacade $moduleFacade,
        -       ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade,
        +       protected readonly ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade,
            ) {
        ```
    -   `createFlagFilterOption()` method changed its interface:
        ```diff
            protected function createFlagFilterOption(
                Flag $flag,
                int $count,
                bool $isAbsolute,
        +       bool $isSelected = false,
            ): FlagFilterOption {
        ```
    -   `createParameterFilterOption()` method changed its interface:
        ```diff
            protected function createParameterFilterOption(
                Parameter $parameter,
                array $parameterValueFilterOptions,
        +       bool $collapsed,
        +       bool $isSliderAllowed,
        +       ?float $selectedValue = null,
            ): ParameterFilterOption {
        ```
    -   `createParameterFilterOption()` method changed its interface:
        ```diff
            protected function createParameterValueFilterOption(
                ParameterValue $brand,
                int $count,
                bool $isAbsolute,
        +       bool $isSelected = false,
            ): ParameterValueFilterOption {
        ```
    -   many strict types have been introduced, see [Introduction of strict types](#introduction-of-strict-types) to learn how to solve it in your project
    -   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6f9633f73733ac35866b63982bd84ef7b33cd317) to update your project

#### move features from project-base to the packages ([#3210](https://github.com/shopsys/shopsys/pull/3210))

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    -   `Order::$gtmCoupon` (renamed to `Order::$promoCode`)
    -   `CategoriesBatchLoader`
    -   `ContactFormMutation` and related graphql types
        -   `ContactInput` was renamed to `ContactFormInput`
        -   `Contact` mutation was renamed to `ContactForm`
    -   `CountriesQuery` and `Country` graphql type
    -   `File` graphql type
    -   `Payment::mainImage` and `Payment::type` graphql fields
    -   `AdvertImage::mainImage` graphql field
    -   `PriceInterface` graphql type was removed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/97aac9fdeb46563e33919e3ca6286072f9ddbf6c) to update your project

#### resolve some deprecations to prepare for easier upgrade to Symfony 6 ([#3209](https://github.com/shopsys/shopsys/pull/3209))

-   `nyholm/psr7` package was removed from the project
-   `sensio/framework-extra-bundle` package was removed from the project
-   the description of every Symfony command is now defined in the attribute `#[AsCommand]` instead of the `configure()` method
-   the `@Route` annotation in the Admin controllers was replaced with the `#[Route]` attribute
-   the method `Shopsys\FormTypesBundle\YesNoType::getParent()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Component\AnnotatedRouteControllerLoader::getDefaultRouteName()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigDefinition::getConfigTreeBuilder()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Component\Domain\Config\DomainsUrlsConfigDefinition::getConfigTreeBuilder()` is now strictly typed
-   the constructor of `Shopsys\FrameworkBundle\Component\Domain\DomainIconProcessor` accepts `Monolog\Logger` as a first argument instead of `\Symfony\Bridge\Monolog\Logger`
-   the method `Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigDefinition::getConfigTreeBuilder()` is now strictly typed
-   the class `Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter::setContext()` is now strictly typed
-   the class `Shopsys\FrameworkBundle\Component\Translation\CustomTransFiltersVisitor` no longer extends `AbstractNodeVisitor`, but implements `NodeVisitorInterface` instead
-   the class `Shopsys\FrameworkBundle\Component\Translation\Translator` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfigDefinition::getConfigTreeBuilder()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Form\Admin\Login\LoginFormType::getBlockPrefix()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Form\DisplayVariablesType::getParent()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Form\GrapesJsType::getParent()` is now strictly typed
-   the class `Shopsys\FrameworkBundle\Form\Transformers\MailWhitelistTransformer` is now strictly typed
-   the class `Shopsys\FrameworkBundle\Form\Transformers\WysiwygCdnDataTransformer` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Model\Administrator\Administrator::getRoles()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorUserProvider::refreshUser()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorUserProvider::supportsClass()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider::refreshUser()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider::supportsClass()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Model\Product\ProductBreadcrumbGenerator::getBreadcrumbItems()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Model\Security\CustomerLoginHandler::onAuthenticationSuccess()` is now strictly typed
-   the method `Shopsys\FrameworkBundle\Model\Security\CustomerLoginHandler::onAuthenticationFailure()` is now strictly typed
-   the method `Shopsys\FrontendApiBundle\Component\Constraints\DeliveryAddressUuid::getTargets()` is now strictly typed
-   the method `Shopsys\FrontendApiBundle\Component\Constraints\PaymentInCart::getTargets()` is now strictly typed
-   the method `Shopsys\FrontendApiBundle\Component\Constraints\PaymentInExistingOrder::getTargets()` is now strictly typed
-   the method `Shopsys\FrontendApiBundle\Component\Constraints\PaymentInOrder::getTargets()` is now strictly typed
-   the method `Shopsys\FrontendApiBundle\Component\Constraints\ProductInOrder::getTargets()` is now strictly typed
-   the method `Shopsys\FrontendApiBundle\Component\Constraints\PromoCode::getTargets()` is now strictly typed
-   the method `Shopsys\FrontendApiBundle\Component\Constraints\TransportInCart::getTargets()` is now strictly typed
-   the method `Shopsys\FrontendApiBundle\Component\Constraints\TransportInOrder::getTargets()` is now strictly typed
-   the method `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersQuery::ordersQuery()` is now strictly typed
-   you may simplify the [upgrade of your project with Rector](https://docs.shopsys.com/en/15.0/project/upgrade-your-project-with-rector/)
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4d17906ee38281a8952cc8880de36c75b611a5c0) to update your project

#### SF optimizations based on projects ([#3222](https://github.com/shopsys/shopsys/pull/3222))

-   `Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticleQuery` class was changed:

    -   `termsAndConditionsArticleQuery()` method was renamed to `termsAndConditionsArticleUrlQuery()` and changed its interface:
        ```diff
        -   termsAndConditionsArticleQuery(): array
        +   termsAndConditionsArticleUrlQuery(): string
        ```
    -   `privacyPolicyArticleQuery()` method was renamed to `privacyPolicyArticleUrlQuery()` and changed its interface:
        ```diff
        -   privacyPolicyArticleQuery(): array
        +   privacyPolicyArticleUrlQuery(): string
        ```
    -   `userConsentPolicyArticleQuery()` method was renamed to `userConsentPolicyArticleUrlQuery()` and changed its interface:
        ```diff
        -   userConsentPolicyArticleQuery(): array
        +   userConsentPolicyArticleUrlQuery(): string
        ```

-   queries `termsAndConditionsArticle`, `privacyPolicyArticle`, `userConsentPolicyArticle`were removed
    -   you can use `termsAndConditionsArticleUrl`, `privacyPolicyArticleUrl`, `userConsentPolicyArticleUrl` properties/resolvers on `SettingsQuery` instead
-   functional tests regarding special articles were removed from `GetArticleTest.php` and the newly added functionality is now covered by tests inside `GetSettingsTest.php`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/5d1b45dc0d522cd9b5c2ab3ccc114aa0a46e7148) to update your project

#### allow to be order items related to each other ([#3229](https://github.com/shopsys/shopsys/pull/3229))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/7d2f5a826709227dd6f2ca085c503389b7279d63) to update your project

#### install a new administration bundle ([#3239](https://github.com/shopsys/shopsys/pull/3239))

-   the new bundle is a first step to separate the administration
    -   installation is strongly recommended as is it possible that some features will be moved to the new bundle in the future
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/dd7f6c13058c4e7b17d0f1b07ccf3d1ff012f690) to update your project

#### add category tree capabilities for articles ([#3237](https://github.com/shopsys/shopsys/pull/3237))

-   constructor `Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportRepository::__construct()` changed its interface
    ```diff
        public function __construct(
            // ...
            protected readonly HreflangLinksFacade $hreflangLinksFacade,
    +       protected readonly BlogCategoryFacade $blogCategoryFacade,
    ```
-   constructor `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryRepository::__construct()` changed its interface
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
    +       protected readonly Domain $domain,
    ```
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/bd2f041dfcdbc19c20d69b6233446dce312bdb84) to update your project

#### new cart items are adedd to the end of cart item list ([#3231](https://github.com/shopsys/shopsys/pull/3231))

-   Cart::$items sort order was changed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/552567b80c32860419e9779fc4682ad6409c716c) to update your project

#### top products are now in elasticsearch ([#3242](https://github.com/shopsys/shopsys/pull/3242))

-   constructor `Shopsys\FrontendApiBundle\Model\Resolver\Products\PromotedProductsQuery` changed its interface:
    ```diff
        public function __construct(
            protected readonly CurrentCustomerUser $currentCustomerUser,
    +       protected readonly ProductFacade $productFacade,
    ```
-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    -   `PromotedProductsQuery` and all the related logic
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e1029cdb51ca747f22d355ee49c97e6502ef1355) to update your project

#### easier mail template extending ([#3232](https://github.com/shopsys/shopsys/pull/3232))

-   there is new cookbook example for how to add custom variables to an existing mail template in our docs https://docs.shopsys.com/en/latest/cookbook/adding-a-new-email-template/
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/52457244a57a1818375406e1f22ebc69f0dc804f) to update your project

#### administration optimization ([#3246](https://github.com/shopsys/shopsys/pull/3246))

-   `Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator` class was changed:
    -   `getResult()` method changed its interface:
        ```diff
            public function getResult(
                int $page = 1,
                ?int $pageSize = null,
        +       ?int $totalCount = null,
            ): PaginationResult {
        ```
-   many strict types have been introduced, see [Introduction of strict types](#introduction-of-strict-types) to learn how to solve it in your project
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/557be8bdc4cc48db757af9c3fe1c06d0fc9b118c) to update your project

#### remove delete button from banner slider image when editing ([#3247](https://github.com/shopsys/shopsys/pull/3247))

-   scenario where banner slider image is already deleted is not fixed by these changes and to fix error state image need to be uploaded again, please check all your banner sliders
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/fbb237111e21a6c5dce90ff3aba81d8709c09fdd) to update your project

#### clean your Twig templates ([#3257](https://github.com/shopsys/shopsys/pull/3257))

-   class `Shopsys\FrameworkBundle\Form\Constraints\DeliveryAddressOfCurrentCustomer` was removed
-   class `Shopsys\FrameworkBundle\Form\Constraints\DeliveryAddressOfCurrentCustomerValidator` was removed
-   class `Shopsys\FrameworkBundle\Form\DeliveryAddressChoiceType` was removed
-   class `Shopsys\FrameworkBundle\Twig\FormThemeExtension` was removed
-   template `@ShopsysFramework/Common/Form/theme.html.twig` was removed
-   template `@ShopsysFramework/Common/robots.txt.twig` was removed
-   template `@ShopsysFramework/Front/Form/deliveryAddressChoiceFields.html.twig` was removed
-   template `@ShopsysFramework/Debug/Elasticsearch/template.html.twig` moved to `@ShopsysFramework/Components/Collector/elasticSearch.html.twig`
-   template `@ShopsysFramework/Common/Inline/Icon/icon.html.twig` moved to `@ShopsysFramework/Components/Icon/icon.html.twig`
-   template `@ShopsysFramework/Common/Mailer/settingInfo.html.twig` moved to `@ShopsysFramework/Components/MailerSettingInfo/mailerSettingInfo.html.twig`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/687ec171ff0196bacd066c10b90f16846b22f363) to update your project

#### Remove akeneo and associated features ([#3258](https://github.com/shopsys/shopsys/pull/3258))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6d778d11f9aefa3a5b5c31f68db546b6dbf47e2c) to update your project

#### fix singledomain/multidomain tests ([#3256](https://github.com/shopsys/shopsys/pull/3256))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/bbbf2e7487f2c005e83328ec70062f04054c952e) to update your project

#### Admin can manage customer structure on B2B domains ([#3261](https://github.com/shopsys/shopsys/pull/3261))

-   there is a new option in domains.yaml (when it is not provided, is set to `b2c` by default) to set domain type:

    ```diff
        domains:
        -   id: 1
          + type: b2c
            locale: en
            name: shopsys
            styles_directory: common
            timezone: Europe/Prague
    ```

-   constructor `Shopsys\FrameworkBundle\Controller\Admin\CustomerController` changed its interface:
    ```diff
        public function __construct(
            protected readonly CustomerUserDataFactoryInterface $customerUserDataFactory,
            protected readonly CustomerUserListAdminFacade $customerUserListAdminFacade,
            protected readonly CustomerUserFacade $customerUserFacade,
            protected readonly BreadcrumbOverrider $breadcrumbOverrider,
            protected readonly AdministratorGridFacade $administratorGridFacade,
            protected readonly GridFactory $gridFactory,
            protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
            protected readonly OrderFacade $orderFacade,
            protected readonly LoginAsUserFacade $loginAsUserFacade,
         -  protected readonly DomainRouterFactory $domainRouterFactory,
            protected readonly CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
            protected readonly Domain $domain,
         +  protected readonly CustomerFacade $customerFacade,
         +  protected readonly LoginAsCustomerUserUrlProvider $loginAsCustomerUserUrlProvider,
    ```
-   constructor `Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade` changed its interface:

    ```diff
        public function __construct(
            protected readonly BillingAddressFactory $billingAddressFactory,
            protected readonly BillingAddressRepository $billingAddressRepository,
            protected readonly EntityManagerInterface $em,
         +  protected readonly BillingAddressUniquenessChecker $billingAddressUniquenessChecker,
    ```

-   constructor `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory` changed its interface:
    ```diff
        public function __construct(
            protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
         +  protected readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
         +  protected readonly CustomerRepository $customerRepository,
        ) {
        }
    ```
-   factory interface `Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface` added new function:
    ```diff
     +  /**``
     +  * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer``
     +  * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     +  */
     +  public function createForCustomer(Customer $customer): DeliveryAddressData;
    ```
-   factory interface `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface` added new function:

    ```diff
     +  /**
     +   * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     +   * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     +   */
     +  public function createForCustomerWithPresetPricingGroup(Customer $customer): CustomerUserData;
    ```

-   protected method `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::createCustomerUser` changed its visibility to public

-   protected method `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::createCustomerWithBillingAddress` changed its visibility to public

-   allow new delete route prefix in `Tests\App\Smoke\Http\RouteConfigCustomization`

    ```diff
     -  if (preg_match('~(_delete$)|(^admin_mail_deletetemplate$)|(^admin_(stock|store)_setdefault$)~', $info->getRouteName())) {
     +  if (preg_match('~(_delete$)|(_delete_all$)|(^admin_mail_deletetemplate$)|(^admin_(stock|store)_setdefault$)~', $info->getRouteName())) {
    ```

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/9412c172eff11fa0d320f2c374afa10d8a8d5b56) to update your project

#### FE API delivery address mutations ([#3265](https://github.com/shopsys/shopsys/pull/3265))

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:

    -   `DeliveryAddress::uuid` property (and all the related logic)
    -   `DeliveryAddressFacade` methods:
        -   `deleteByUuidAndCustomer`
        -   `editByCustomer`
        -   `getByUuidAndCustomer`
    -   `DeliveryAddressRepository::getByUuidAndCustomer` method

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:

    -   `DeliveryAddressInput` GQL type
    -   `DeliveryAddress` GQL type
    -   `CustomerMutation` GQL type
    -   `DeliveryAddressMutation` class and all the related logic
    -   `CustomerMutation` and all the related logic
    -   `InvalidCredentialsUserError` exception
    -   `DeliveryAddressNotFoundUserError` exception

-   added a new test `Tests\FrontendApiBundle\Functional\Customer\CreateDeliveryAddressTest`

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/610cebc85a50733d5a122546676d2a5dea39d67d) to update your project

#### limit item counts in sliders ([#3244](https://github.com/shopsys/shopsys/pull/3244))

-   `Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade` class was changed:
    -   `getTopOfferedAccessories()` method was renamed to `getOfferedAccessories()` and it is strict-typed now
-   `Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository` class was changed:
    -   `getTopOfferedAccessories()` method was renamed to `getOfferedAccessories()` and it is strict-typed now
-   `Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade` class was changed:
    -   `getAllOfferedBestsellingProducts()` method was renamed to `getOfferedBestsellingProducts()` and changed its interface
        ```diff
            public function getOfferedBestsellingProducts(
                // ...
        +       int $limit
        ```
-   `Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade::MAX_SHOW_RESULTS` constant was removed, you can use `Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider->getProductsFrontendLimit()` instead
-   `Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade` class was changed:
    -   `getAllOfferedBestsellingProductIds()` method was renamed to `getOfferedBestsellingProductIds()` and changed its interface
        ```diff
            public function getOfferedBestsellingProductIds(
                // ...
        +       int $limit
        ```
-   `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository` class was changed:
    -   `extractAccessoriesIds()` method changed its interface
        ```diff
            public function getOfferedBestsellingProductIds(
                // ...
        +       int $domainId
        +       ?int $limit
        ```
-   `Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade` class was changed:
    -   `getAllOfferedProducts()` method was renamed to `getOfferedProducts()`, it is strict-typed now and changed its interface
        ```diff
            public function getOfferedProducts(
                // ...
        +       ?int $limit
        ```
-   `Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductRepository` class was changed:
    -   `getOfferedProductsForTopProductsOnDomain()` method is strict-typed now and changed its interface
        ```diff
            public function getOfferedProductsForTopProductsOnDomain(
                // ...
        +       ?int $limit
        ```
-   `Shopsys\FrontendApiBundle\Model\Product\BestsellingProductsQuery` class was moved from project-base into packages
-   `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
        ```
-   `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductEntityFieldMapper` class was changed:
-   `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
        ```
-   `Shopsys\FrontendApiBundle\Model\Resolver\Products\PromotedProductsQuery` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
        ```
-   `Shopsys\LuigisBoxBundle\FrontendApi\Resolver\Recommendation\RecommendationQuery` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
        ```
-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    -   `BestsellingProductsQuery` and all the related logic
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/0693cfd01d638f64785c78556538d4b49e7fd988) to update your project

#### upgrade dependencies ([#3273](https://github.com/shopsys/shopsys/pull/3273))

-   support of older Symfony versions was dropped in `shopsys/http-smoke-testing`
    -   only `^5.4` Symfony version is now supported
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/14e8b52ed1a52781f2ca1c4c431a96e5b3526d02) to update your project

#### remove obsolete frontend logout related classes ([#3274](https://github.com/shopsys/shopsys/pull/3274))

-   class `Shopsys\FrameworkBundle\Component\Error\LogoutExceptionSubscriber` was removed
-   class `Shopsys\FrameworkBundle\Model\Security\FrontLogoutHandler` was removed
-   constructor `Shopsys\FrameworkBundle\Model\Security\LogoutListener` changed its interface
    ```diff
        public function __construct(
    -      protected readonly FrontLogoutHandler $frontLogoutHandler,
           protected readonly AdminLogoutHandler $adminLogoutHandler,
    ```

#### Switch from Seznam Maps to Google Maps ([#3268](https://github.com/shopsys/shopsys/pull/3268))

-   to simplify and unify `Store` location naming for both BE and FE, `locationLatitude` and `locationLongitude` were renamed to `latitude` and `longitude` respectively.
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/252e498ecb9b46dd693b274b36dc36d4e65b579c) to update your project

#### add customer user login types to the data layer ([#3276](https://github.com/shopsys/shopsys/pull/3276))

-   `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser` class was changed:
    -   `$lastLogin` property and `getLastLogin()` method were removed, use `CustomerUserLoginInformationProvider::getLastLogin()` instead
    -   `onLogin()` method was removed
-   `Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation::__construct()` method changed its interface:
    ```diff
        public function __construct(
            TokenStorageInterface $tokenStorage,
            protected readonly FrontendCustomerUserProvider $frontendCustomerUserProvider,
            protected readonly UserPasswordHasherInterface $userPasswordHasher,
            protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
            protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
            protected readonly CustomerUserFacade $customerUserFacade,
            protected readonly CustomerUserDataFactory $customerUserDataFactory,
            protected readonly TokenFacade $tokenFacade,
            protected readonly ProductListFacade $productListFacade,
            protected readonly RegistrationFacade $registrationFacade,
            protected readonly RegistrationDataFactory $registrationDataFactory,
            protected readonly MergeCartFacade $mergeCartFacade,
            protected readonly OrderApiFacade $orderFacade,
            protected readonly LoginResultDataFactory $loginResultDataFactory,
            protected readonly TokensDataFactory $tokensDataFactory,
    +       protected readonly CustomerUserLoginTypeFacade $customerUserLoginTypeFacade,
    +       protected readonly CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory,
    ```
-   `Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation::__construct()` method changed its interface:
    ```diff
        public function __construct(
            protected readonly FrontendCustomerUserProvider $frontendCustomerUserProvider,
            protected readonly UserPasswordHasherInterface $userPasswordHasher,
            protected readonly TokenFacade $tokenFacade,
            protected readonly DefaultLoginRateLimiter $loginRateLimiter,
            protected readonly RequestStack $requestStack,
            protected readonly ProductListFacade $productListFacade,
            protected readonly MergeCartFacade $mergeCartFacade,
            protected readonly TokensDataFactory $tokensDataFactory,
            protected readonly LoginResultDataFactory $loginResultDataFactory,
    +       protected readonly CustomerUserLoginTypeFacade $customerUserLoginTypeFacade,
    +       protected readonly CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory,
    ```
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/de84d0547df5c8d1141878b189a31626e23f8f41) to update your project

#### refactor product price form type in product detail edit ([#3279](https://github.com/shopsys/shopsys/pull/3279))

-   custom form fields for product price and vat select in product detail edit were replaced by single `ProductPricesWithVatSelectType` wrapped in `MultidomainType`
-   class `Shopsys\FrameworkBundle\Form\ProductCalculatedPricesType` was removed
-   `Shopsys\FrameworkBundle\Model\Product\ProductData`: properties `$manualInputPricesByPricingGroupId` and `$vatsIndexedByDomainId` were removed and replaced with compound property `$productInputPricesByDomain`
    -   this property is an array of `ProductInputPriceData` objects
    -   this change is propagated to other classes and methods:
        -   set VAT value in `Shopsys\FrameworkBundle\Model\Product\Product::setDomains()` method
        -   argument `$manualInputPrices` in `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade::refreshProductManualInputPrices()`
        -   return type of method `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade::getManualInputPricesDataIndexedByDomainIdAndPricingGroupId()` (renamed from `getManualInputPricesDataIndexedByPricingGroupId()`)
        -   creating an object with factory in `Shopsys\FrameworkBundle\Model\Product\ProductDataFactory`
-   template `@ShopsysFramework/Admin/Form/productCalculatedPrices.html.twig` was removed
-   method `Shopsys\FrameworkBundle\Model\Product\ProductDataFactory::getNullForAllPricingGroups()` was removed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/eb12db4a594107b614075c46d6e0adcf9b75b598) to update your project

#### remove unused code for the styleguide for twig storefront ([#3281](https://github.com/shopsys/shopsys/pull/3281))

-   following Phing properties were removed from `build.xml`:
    -   `path.web.styles.admin`
    -   `path.web.styles.front`
    -   `path.web.styles.styleguide`
-   Phing target `clean-styles` was removed
-   directories `app/web/assets/admin/styles` and `app/web/assets/frontend/styles` are no longer created automatically
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/79ffa96962541bab0eb75054858980828302af4f) to update your project

#### fix closed day data fixture for domains without any store created ([#3283](https://github.com/shopsys/shopsys/pull/3283))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/753415e39039c8621b033d754927adf348a94132) to update your project

#### fix data fixtures for more than two domains ([#3284](https://github.com/shopsys/shopsys/pull/3284))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/7df4de9fc05ef5146685b8e41c7b9cb0a77ac2b2) to update your project

#### address, name and some other user data is now nullable ([#3285](https://github.com/shopsys/shopsys/pull/3285))

-   following fields in `Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress` are now nullable:
    -   `$firstName`
    -   `$lastName`
    -   `$street`
    -   `$city`
    -   `$postcode`
    -   `$country`
-   following fields in `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser` are now nullable:
    -   `$firstName`
    -   `$lastName`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/0e328563c3fa474aa7316febf037c06e24839129) of additional fix (https://github.com/shopsys/shopsys/pull/3314)

#### product files support ([#3288](https://github.com/shopsys/shopsys/pull/3288))

-   The filesystem directory structure for `UploadedFile` has been changed. Files are now stored directly in the parent directory, rather than in subdirectories organized by entity name.
    The migration command `Shopsys\FrameworkBundle\Command\MigrateUploadedFilesCommand` is available to move files to the new structure. This command runs automatically as a part of the phing target `build-deploy-part-2-db-dependent`.
    Alternatively, you can run it manually using the `migrate-uploaded-files` phing target. Make sure to back up all uploaded files before deploying this change as it may result in data loss.
-   `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile` entity was updated:
    -   Properties `entityName`, `entityId`, `type` and `position` were removed from `UploadedFile` and moved to `UploadedFileRelation` along with getters and setters.
    -   `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile::__construct()` was changed
        ```diff
            public function __construct(
        -       string $entityName,
        -       int $entityId,
        -       string $type,
                string $temporaryFilename,
                string $uploadedFilename,
        -       int $position,
        +       array $namesIndexedByLocale,
            ) {
        ```
    -   `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile::checkForDelete()` method was removed without replacement
-   `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade` was updated:
    -   `UploadedFileFacade::__construct()` was updated
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
                protected readonly UploadedFileConfig $uploadedFileConfig,
                protected readonly UploadedFileRepository $uploadedFileRepository,
                protected readonly FilesystemOperator $filesystem,
                protected readonly UploadedFileLocator $uploadedFileLocator,
                protected readonly UploadedFileFactoryInterface $uploadedFileFactory,
        +       protected readonly UploadedFileRelationFactory $uploadedFileRelationFactory,
        +       protected readonly UploadedFileRelationRepository $uploadedFileRelationRepository,
            ) {
        ```
    -   `UploadedFileFacade::uploadFile()` method was updated to accept an array of names indexed by locale
        ```diff
            protected function uploadFile(
                object $entity,
                string $entityName,
                string $type,
                string $temporaryFilename,
                string $uploadedFilename,
        +       array $namesIndexedByLocale = [],
            ): void {
        ```
    -   `UploadedFileFacade::uploadFiles()` method visibility changed from protected to public and signature updated to accept an array of names indexed by file id and locale
        ```diff
        -    protected function uploadFiles(
        +    public function uploadFiles(
                object $entity,
                string $entityName,
                string $type,
                array $temporaryFilenames,
                array $uploadedFilenames,
                int $existingFilesCount,
        +       array $namesIndexedByFileIdAndLocale = [],
            ): void {
        ```
    -   `UploadedFileFacade::deleteFiles()` method was removed without replacement
    -   `UploadedFileFacade::deleteAllUploadedFilesByEntity()` method was removed without replacement
    -   `UploadedFileFacade::updateFilesOrder()` method was updated to accept an array of `UploadedFileRelation` entities
        ```diff
        -    protected function updateFilesOrder(array $uploadedFiles): void
        +    protected function updateFilesOrder(array $uploadedFiles, array $relations): void
        ```
-   `Shopsys\FrameworkBundle\Component\FileUpload\FileUpload::getTemporaryFilename()` changed its visibility from protected to public
-   `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactoryInterface` entity was updated:
    -   `UploadedFileFactoryInterface::create()` method signature was updated
        ```diff
            public function create(
        -       string $entityName,
        -       int $entityId,
        -       string $type,
                string $temporaryFilename,
                string $uploadedFilename,
        -       int $position = 0,
        +       array $namesIndexedByLocale = [],
            ): UploadedFile {
        ```
    -   `UploadedFileFactoryInterface::createMultiple()` method signature was updated
        ```diff
            public function createMultiple(
        -       string $entityName,
        -       int $entityId,
        -       string $type,
                array $temporaryFilenames,
                array $uploadedFilenames,
        -       int $existingFilesCount,
        +       array $namesIndexedByFileIdAndLocale = [],
            ): array {
        ```
-   `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactory` entity was updated:
    -   `UploadedFileFactory::create()` method signature was updated
        ```diff
            public function create(
        -       string $entityName,
        -       int $entityId,
        -       string $type,
                string $temporaryFilename,
                string $uploadedFilename,
        -       int $position = 0,
        +       array $namesIndexedByLocale = [],
            ): UploadedFile {
        ```
    -   `UploadedFileFactory::createMultiple()` method signature was updated
        ```diff
            public function createMultiple(
        -       string $entityName,
        -       int $entityId,
        -       string $type,
                array $temporaryFilenames,
                array $uploadedFilenames,
        -       int $existingFilesCount,
        +       array $namesIndexedByFileIdAndLocale = [],
            ): array {
        ```
-   `Shopsys\FrameworkBundle\Model\Product\ProductDataFactory::__construct()` was updated:

    ```diff
        public function __construct(
            protected readonly VatFacade $vatFacade,
            protected readonly ProductInputPriceFacade $productInputPriceFacade,
            protected readonly UnitFacade $unitFacade,
            protected readonly Domain $domain,
            protected readonly ParameterRepository $parameterRepository,
            protected readonly FriendlyUrlFacade $friendlyUrlFacade,
            protected readonly ProductAccessoryRepository $productAccessoryRepository,
            protected readonly PluginCrudExtensionFacade $pluginDataFormExtensionFacade,
            protected readonly ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
            protected readonly PricingGroupFacade $pricingGroupFacade,
            protected readonly ImageUploadDataFactory $imageUploadDataFactory,
            protected readonly ProductStockFacade $productStockFacade,
            protected readonly StockFacade $stockFacade,
            protected readonly ProductStockDataFactory $productStockDataFactory,
    +       protected readonly UploadedFileDataFactory $uploadedFileDataFactory,
        ) {
    ```

-   `Shopsys\FrameworkBundle\Model\Product\ProductFacade::__construct()` was updated:

    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly ProductRepository $productRepository,
            protected readonly ProductVisibilityFacade $productVisibilityFacade,
            protected readonly ParameterRepository $parameterRepository,
            protected readonly Domain $domain,
            protected readonly ImageFacade $imageFacade,
            protected readonly PricingGroupRepository $pricingGroupRepository,
            protected readonly ProductManualInputPriceFacade $productManualInputPriceFacade,
            protected readonly FriendlyUrlFacade $friendlyUrlFacade,
            protected readonly ProductAccessoryRepository $productAccessoryRepository,
            protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
            protected readonly ProductFactoryInterface $productFactory,
            protected readonly ProductAccessoryFactoryInterface $productAccessoryFactory,
            protected readonly ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
            protected readonly ProductParameterValueFactoryInterface $productParameterValueFactory,
            protected readonly ProductVisibilityFactoryInterface $productVisibilityFactory,
            protected readonly ProductPriceCalculation $productPriceCalculation,
            protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
            protected readonly ProductStockFacade $productStockFacade,
            protected readonly StockFacade $stockFacade,
    +       protected readonly UploadedFileFacade $uploadedFileFacade,
        ) {
    ```

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/2af0e63a6804ee299818c069fe219205ba309a52) to update your project

#### slider parameter values now have mandatory numeric value and parameter has its edit page including all attributes ([#3262](https://github.com/shopsys/shopsys/pull/3262))

-   `\Shopsys\FrameworkBundle\Controller\Admin\DefaultController` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly StatisticsFacade $statisticsFacade,
                protected readonly StatisticsProcessingFacade $statisticsProcessingFacade,
                protected readonly MailTemplateFacade $mailTemplateFacade,
                protected readonly UnitFacade $unitFacade,
                protected readonly Setting $setting,
                protected readonly CronModuleFacade $cronModuleFacade,
                protected readonly GridFactory $gridFactory,
                protected readonly CronConfig $cronConfig,
                protected readonly CronFacade $cronFacade,
                protected readonly BreadcrumbOverrider $breadcrumbOverrider,
                protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
                protected readonly TransferIssueFacade $transferIssueFacade,
                protected readonly Domain $domain,
        +       protected readonly ParameterFacade $parameterFacade,
            ) {
        ```
-   `\Shopsys\FrameworkBundle\Controller\Admin\ParameterValueController` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly GridFactory $gridFactory,
                protected readonly ParameterRepository $parameterRepository,
                protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
                protected readonly ParameterFacade $parameterFacade,
                protected readonly ParameterValueDataFactory $parameterValueDataFactory,
                protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        +       protected readonly ParameterValueConversionDataFactory $parameterValueConversionDataFactory,
            ) {
        ```
-   `\Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
                protected readonly ParameterRepository $parameterRepository,
                protected readonly ParameterFactoryInterface $parameterFactory,
                protected readonly EventDispatcherInterface $eventDispatcher,
                protected readonly CategoryParameterRepository $categoryParameterRepository,
                protected readonly UploadedFileFacade $uploadedFileFacade,
        +       protected readonly ParameterValueDataFactory $parameterValueDataFactory,
        +       protected readonly ParameterValueFactory $parameterValueFactory,
            ) {
        ```
-   `\Shopsys\FrameworkBundle\Controller\Admin\ParameterController` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly ParameterFacade $parameterFacade,
        -       protected readonly ParameterInlineEdit $parameterInlineEdit,
        +       protected readonly ParameterGridFactory $parameterGridFactory,
        +       protected readonly ParameterDataFactoryInterface $parameterDataFactory,
            ) {
        ```
-   `\Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterInlineEdit` class was removed, an edition of parameters is now done in separate form
-   `\Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository::getProductParameterValuesByProductSortedByNameQueryBuilder()` method was renamed to `getProductParameterValuesByProductSortedByOrderingPriorityAndNameQueryBuilder`
-   `\Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade::getProductParameterValuesByProductSortedByNameQueryBuilder()` method was renamed to `getProductParameterValuesByProductSortedByOrderingPriorityAndNameQueryBuilder`
-   newly you are required to you docker compose in version 2.0 or newer
-   you will need to convert parameter values of slider parameters to the numeric value in administration
    -   you will see an error message on the dashboard with a link to the conversion page where you can convert the values manually
        -   there is no migration for this as there is no ultimate solution for all possible formats of data, so you need to solve this on your own
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/ab68baa24c6671a4642e2bee16ce33540d447249) to update your project

#### added mutations and queries to work with customer structure ([#3286](https://github.com/shopsys/shopsys/pull/3286))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/594fff94bad340cf87340a01c3c31aa95e3b6d89) to update your project

#### Send mail adding new customer user to customer ([#3291](https://github.com/shopsys/shopsys/pull/3291))

-   constructor `Shopsys\FrameworkBundle\Controller\Admin\CustomerController` changed its interface:
    ```diff
        public function __construct(
            protected readonly CustomerFacade $customerFacade,
    +       protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
    ```
-   field `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData::sendRegistrationMail` moved to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData::sendRegistrationMail`.
-   method `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::register` was removed
-   method `Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserDataFactory::createWithArgument` was removed

*   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b1b1a16215f6c345978ee5be7d76e526d729b9ed) to update your project

#### allow `ROLE_ALL_API` to see all customer user orders from common customer ([#3296](https://github.com/shopsys/shopsys/pull/3296))

-   constructor `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderQuery` changed its interface:
    ```diff
        public function __construct(
            protected readonly OrderApiFacade $orderApiFacade,
            protected readonly CustomerFacade $customerFacade,
    +       protected readonly Security $security,
    ```
-   constructor `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersQuery` changed its interface:

    ```diff
        public function __construct(
            protected readonly OrderApiFacade $orderApiFacade,
    +       protected readonly Security $security,
    ```

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:

    -   `OrderQuery::orderByUuidOrUrlHashQuery` method logic
    -   `OrdersQuery::ordersQuery` method logic

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6b68d635709837d686861862e1df4784c9e9bc51) to update your project

#### blog category is no longer visible if it has no visible articles ([#3292](https://github.com/shopsys/shopsys/pull/3292))

-   `\Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
                protected readonly BlogCategoryRepository $blogCategoryRepository,
                protected readonly FriendlyUrlFacade $friendlyUrlFacade,
                protected readonly ImageFacade $imageFacade,
                protected readonly BlogCategoryFactory $blogCategoryFactory,
                protected readonly BlogCategoryWithPreloadedChildrenFactory $blogCategoryWithPreloadedChildrenFactory,
                protected readonly BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler,
                protected readonly BlogArticleExportQueueFacade $blogArticleExportQueueFacade,
                protected readonly BlogArticleFacade $blogArticleFacade,
                protected readonly Domain $domain,
        +       protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
            ) {
        ```
-   `\Shopsys\FrameworkBundle\Model\Article\ArticleFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
                protected readonly ArticleRepository $articleRepository,
                protected readonly Domain $domain,
                protected readonly FriendlyUrlFacade $friendlyUrlFacade,
                protected readonly ArticleFactoryInterface $articleFactory,
                protected readonly ArticleExportScheduler $articleExportScheduler,
        +       protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
            ) {
        ```
-   `\Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityRecalculationListener` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler,
                protected readonly BlogVisibilityFacade $blogVisibilityFacade,
        +       protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
            ) {
        ```
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/beebd997354111359f77a3608dbb97e719495153) to update your project

#### exclude main variant from selling when it has no selling or visible variants ([#3303](https://github.com/shopsys/shopsys/pull/3303))

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    -   `ProductSellingDeniedRecalculator` and all related logic
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/a8d56a62568d5f1b1801882862b58fa12a5a4ad1) to update your project

#### allow loading demo data only for selected domains ([#3293](https://github.com/shopsys/shopsys/pull/3293))

-   you can set `load_demo_data` config value for each domain in `domains.yaml` file to enable/disable loading demo data for this particular domain during data fixture load
    -   see https://docs.shopsys.com/en/15.0/introduction/basic-and-demo-data-during-application-installation/#loading-demo-data-only-for-certain-domains
-   class `Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture` is now strictly typed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6f2ca527bd5643e4da00391672cfad8585cb77b5) to update your project
-   see also [project-base diff](https://www.github.com/shopsys/project-base/commit/beb65654c9f11844c78b029384d582e3e08f4e08) of [#3391](https://github.com/shopsys/shopsys/pull/3391) with additional fix

#### refactoring: OrderStatus::$type is now string ([#3313](https://github.com/shopsys/shopsys/pull/3313))

-   `Shopsys\FrameworkBundle\Model\Order\Status\Exception\InvalidOrderStatusTypeException` was removed
-   `Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus` class was changed:
    -   `TYPE_NEW`, `TYPE_IN_PROGRESS`, `TYPE_DONE`, and `TYPE_CANCELED` constants were removed, use `OrderStatusTypeEnum` constants instead
    -   `$type` property type was changed from `int` to `string`
        -   check `Version20240614144002` migration. If you have any additional order statuses on your project, you need to add a similar migration for them
    -   `setType()` method was removed
        -   the proper type check is now performed in `OrderStatusFactory::create()` method (using `OrderStatusTypeEnum::validateCase()` method)
-   `Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade` class was changed:
    -   `__construct()` interface was changed:
    ```diff
        public function __construct(
            // ...
    -       protected readonly OrderStatusFactoryInterface $orderStatusFactory,
    +       protected readonly OrderStatusFactory $orderStatusFactory,
    ```
    -   `getAllIndexedById()` method was removed
-   `Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFactory` class was changed:
    -   `__construct()` method was changed:
    ```diff
        public function __construct(
            protected readonly EntityNameResolver $entityNameResolver,
    +       protected readonly OrderStatusTypeEnum $orderStatusTypeEnum,
    ```
    -   `create()` method was changed:
    ```diff
    -   public function create(OrderStatusData $data, int $type): OrderStatus
    +   public function create(OrderStatusData $data, string $type): OrderStatus
    ```
-   `Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFactoryInterface` interface was removed
-   `Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository::getAllIndexedById()` method was removed

#### validate parameter name uniqueness ([#3317](https://github.com/shopsys/shopsys/pull/3317))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/dd5059697bb1b82197803a2f72e0c0cec5e20aee) to update your project

#### complaint support ([#3295](https://github.com/shopsys/shopsys/pull/3295))

-   `Shopsys\FrameworkBundle\Command\CreateApplicationDirectoriesCommand::__construct()` was changed
    ```diff
        public function __construct(
            $defaultInternalDirectories,
            $defaultPublicDirectories,
            $internalDirectories,
            $publicDirectories,
            private readonly FilesystemOperator $filesystem,
            private readonly Filesystem $localFilesystem,
            private readonly ImageDirectoryStructureCreator $imageDirectoryStructureCreator,
            private readonly UploadedFileDirectoryStructureCreator $uploadedFileDirectoryStructureCreator,
    +       private readonly CustomerUploadedFileDirectoryStructureCreator $customerUploadedFileDirectoryStructureCreator,
        ) {
    ```
-   `Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload` class was changed
    -   `FileForUpload::__construct()` was changed
        ```diff
        -    public function __construct($temporaryFilename, $isImage, $category, $targetDirectory, $nameConventionType)
        +    public function __construct($temporaryFilename, $fileClass, $category, $targetDirectory, $nameConventionType)
        ```
    -   `FileForUpload::isImage()` was removed and replaced by `FileForUpload::getFileClass()`
-   `Shopsys\FrameworkBundle\Component\FileUpload\FileUpload` class was changed
    -   `FileUpload::$uploadedFileDir` and `FileUpload::$imageDir` properties were removed and replaced with `FileUpload::$directoriesByFileClass` property
    -   `FileUpload::__construct()` was changed
        ```diff
            public function __construct(
                $temporaryDir,
        -       $uploadedFileDir,
        -       $imageDir,
        +       protected array $directoriesByFileClass,
                protected readonly FileNamingConvention $fileNamingConvention,
                protected readonly MountManager $mountManager,
                protected readonly FilesystemOperator $filesystem,
                protected readonly ParameterBagInterface $parameterBag,
            ) {
        ```
-   `Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade` methods were changed
    -   `OrderApiFacade::getCustomerUserOrderLimitedList()` argument added
        ```diff
            public function getCustomerUserOrderLimitedList(
                CustomerUser $customerUser,
                int $limit,
                int $offset,
        +       OrderFilter $filter,
            ): array {
        ```
    -   `OrderApiFacade::getCustomerUserOrderCount()` argument added
        ```diff
        -    public function getCustomerUserOrderCount(CustomerUser $customerUser): int
        +    public function getCustomerUserOrderCount(CustomerUser $customerUser, OrderFilter $filter): int
        ```
-   `Shopsys\FrontendApiBundle\Model\Order\OrderRepository` methods were changed
    -   `OrderRepository::getCustomerUserOrderLimitedList()` argument added
        ```diff
            public function getCustomerUserOrderLimitedList(
                CustomerUser $customerUser,
                int $limit,
                int $offset,
        +       ?OrderFilter $filter = null,
            ): array {
        ```
    -   `OrderRepository::getCustomerUserOrderCount()` argument added
        ```diff
        -    public function getCustomerUserOrderCount(CustomerUser $customerUser): int
        +    public function getCustomerUserOrderCount(CustomerUser $customerUser, OrderFilter $filter): int
        ```
-   `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersQuery::__construct()` was changed
    ```diff
        public function __construct(
            protected readonly CurrentCustomerUser $currentCustomerUser,
            protected readonly OrderApiFacade $orderApiFacade,
    +       protected readonly OrderFilterFactory $orderFilterFactory,
        ) {
    ```
-   `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade` was changed:

    -   `UploadedFileFacade::__construct()` was changed:
        ```diff
            public function __construct(
        -       protected readonly EntityManagerInterface $em,
        +       FilesystemOperator $filesystem,
        +       EntityManagerInterface $em,
                protected readonly UploadedFileConfig $uploadedFileConfig,
                protected readonly UploadedFileRepository $uploadedFileRepository,
        -       protected readonly FilesystemOperator $filesystem,
                protected readonly UploadedFileLocator $uploadedFileLocator,
                protected readonly UploadedFileFactoryInterface $uploadedFileFactory,
                protected readonly UploadedFileRelationFactory $uploadedFileRelationFactory,
                protected readonly UploadedFileRelationRepository $uploadedFileRelationRepository,
            ) {
        ```
    -   Following methods moved to new parent class `Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFile` and arguments changed

        ```diff
        # Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade
        -   public function getById(int $uploadedFileId): UploadedFile
        -   public function getAbsoluteUploadedFileFilepath(UploadedFile $uploadedFile): string
        -   public function deleteFileFromFilesystem(UploadedFile $uploadedFile): void

        # Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFile
        +   public function getById(int $uploadedFileId): UploadedFileInterface
        +   public function getAbsoluteUploadedFileFilepath(UploadedFileInterface $uploadedFile): string
        +   public function deleteFileFromFilesystem(UploadedFileInterface $uploadedFile): void
        ```

-   `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator` was changed:

    -   Following methods moved to new parent class `Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileLocator` and arguments changed

    ```diff
    # Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator
    -   public function getRelativeUploadedFileFilepath(UploadedFile $uploadedFile): string
    -   public function getAbsoluteUploadedFileFilepath(UploadedFile $uploadedFile): string
    -   public function fileExists(UploadedFile $uploadedFile): bool

    # Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileLocator
    +   public function getRelativeUploadedFileFilepath(UploadedFileInterface $uploadedFile): string
    +   public function getAbsoluteUploadedFileFilepath(UploadedFileInterface $uploadedFile): string
    +   public function fileExists(UploadedFileInterface $uploadedFile): bool
    ```

-   composer package `shopsys/deployment` version changed to `^3.2.4` (see https://github.com/shopsys/deployment/blob/main/UPGRADE.md)
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/9513e52540a6f652c45fe9def57353baafac3240) to update your project

#### admin can manage customer user group roles ([#3323](https://github.com/shopsys/shopsys/pull/3323))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/59445438cd31a933c03d1eb3ad0370a4cd334154) to update your project

#### adjust the GoPay integration to be bound to domains ([#3308](https://github.com/shopsys/shopsys/pull/3308))

-   GoPay is now configured with a single `GOPAY_CONFIG` environment variable
    -   the `GOPAY_CONFIG` environment variable is a JSON object with the following structure:
    ```json
    [
        {
            "goid": "<goid1>",
            "clientId": "<clientId1>",
            "clientSecret": "<clientSecret1>",
            "isProductionMode": false,
            "domains": [1,2]
        },
        {
            "goid": "<goid2>",
            "clientId": "<clientId2>",
            "clientSecret": "<clientSecret2>",
            "isProductionMode": true,
            "domains": [3]
        }
    ]'
    ```
    -   this allows you more flexibility in configuring GoPay for different domains
    -   environment variables `GOPAY_EN_GOID`, `GOPAY_CS_GOID`, `GOPAY_EN_CLIENTID`, `GOPAY_CS_CLIENTID`, `GOPAY_EN_CLIENTSECRET`, `GOPAY_CS_CLIENTSECRET` are no longer used
    -   parameter `%gopay_config%` is no longer used
-   it is recommended to download the GoPay payment method with the cron `GoPayAvailablePaymentsCronModule` and verify all payment methods that are correctly configured
    -   it's no longer necessary to create a separate payment method for each domain due to different GoPay payment method as the form now allows you to select the method for each domain
-   payments and GoPay payment methods are migrated automatically in database migration
    -   see the database migration `Version20240803005343` if you use a different configuration than the default one
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/762ad0fd1e9f2310388fb7fef6b05124f53c3e36) to update your project
-   `Shopsys\FrameworkBundle\Model\GoPay\GoPayAvailablePaymentsCronModule::__construct()` changed its interface:
    ```diff
        public function __construct(
    -       protected readonly array $goPayConfig,
            protected readonly GoPayPaymentMethodFacade $paymentMethodFacade,
            protected readonly EntityManagerInterface $em,
            protected readonly Domain $domain,
    ```
-   `Shopsys\FrameworkBundle\Model\GoPay\GoPayClientFactory::createByLocale()` was removed, use `createByDomain()` instead
-   `Shopsys\FrameworkBundle\Model\GoPay\GoPayClientFactory::getConfigByLocale()` was removed
-   `Shopsys\FrameworkBundle\Model\GoPay\GoPayFacade::getGoPayClientByDomainConfig()` was removed, use `Shopsys\FrameworkBundle\Model\GoPay\GoPayClientFactory::createByDomain()` instead
-   `Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodRepository::getAllIndexedByIdentifierByCurrencyId()` was removed, use `getAllIndexedByIdentifierByDomainId()` instead
-   `Shopsys\FrameworkBundle\Model\Payment\Payment::$goPayPaymentMethod` is now domain-specific (moved to `PaymentDomain`)
-   `Shopsys\FrameworkBundle\Model\Payment\Payment::$hiddenByGoPay` is now domain-specific (moved to `PaymentDomain`)
-   `Shopsys\FrameworkBundle\Model\Payment\PaymentData::$hiddenByGoPay` now contain boolean values for each domain
-   `Shopsys\FrameworkBundle\Model\Payment\PaymentData::goPayPaymentMethod` was removed, use `$goPayPaymentMethodByDomainId` that contains string values for each domain
-   `Shopsys\FrameworkBundle\Model\Payment\PaymentFacade::hideByGoPayPaymentMethod()` now requires domain ID as a second parameter
-   `Shopsys\FrameworkBundle\Model\Payment\PaymentFacade::unHideByGoPayPaymentMethod()` now requires domain ID as a second parameter
-   `Shopsys\FrameworkBundle\Model\Payment\PaymentRepository::getByGoPayPaymentMethod()` now requires domain ID as a second parameter

#### fix saving packetery address as a new delivery address ([#3333](https://github.com/shopsys/shopsys/pull/3333))

-   `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade`
    -   constructor `__construct()` changed its interface
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly CustomerUserRepository $customerUserRepository,
            protected readonly CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
            protected readonly CustomerMailFacade $customerMailFacade,
    -       protected readonly BillingAddressDataFactoryInterface $billingAddressDataFactory,
            protected readonly CustomerUserFactoryInterface $customerUserFactory,
            protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
            protected readonly CustomerFacade $customerFacade,
            protected readonly DeliveryAddressFacade $deliveryAddressFacade,
            protected readonly CustomerDataFactoryInterface $customerDataFactory,
            protected readonly BillingAddressFacade $billingAddressFacade,
            protected readonly CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
    -       protected readonly DeliveryAddressFactory $deliveryAddressFactory,
    -       protected readonly DeliveryAddressDataFactory $deliveryAddressDataFactory,
            protected readonly NewsletterFacade $newsletterFacade,
            protected readonly HashGenerator $hashGenerator,
    ```
    -   method `createDeliveryAddressForAmendingCustomerUserData()` was removed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b5c8f9e90d3a9131fa66b20705961eb96c3d9263) to update your project

#### remove productList and productListMiddle advert positions ([#3335](https://github.com/shopsys/shopsys/pull/3335))

-   productList and productListMiddle advert position were decided to be removed
-   existing adverts were changed to remaining productListSecondRow position
-   `Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry` class was changed:
    -   `POSITION_PRODUCT_LIST` constant was removed
    -   `POSITION_CATEGORIES_ABOVE_PRODUCT_LIST` constant was removed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/c1ab21e52dc3bcf9f921b4f0df6302eeec286aae) to update your project

#### do not show prices for customer user with role `ROLE_API_CUSTOMER_SEE_PRICES` ([#3319](https://github.com/shopsys/shopsys/pull/3319))

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    -   `App\Controller\Front\PersonalDataController` class and all related logic
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/26b4f84f45b39160f98de4ef5cf8489c53590e4f) to update your project
-   see also [project-base diff](https://github.com/shopsys/project-base/commit/58c86ac442d801d9a4f7f88d69cd7488a14f7c6c) to fix test

#### Developers now can generate new empty migration ([#2878](https://github.com/shopsys/shopsys/pull/2878))

-   Now, you can call function `php phing upgrade-generate` and empty migration will be generated

#### Image::$position is not nullable anymore ([#3343](https://github.com/shopsys/shopsys/pull/3343))

-   double-check `Shopsys\FrameworkBundle\Migrations\Version20240814132958` whether it suits your needs
-   `Shopsys\FrameworkBundle\Model\Product\Image\Image` class was changed:
    -   `$position` property is not nullable anymore
    -   `UPLOAD_KEY` constant is now strictly typed
-   `Shopsys\FrameworkBundle\Component\FileUpload\FileUpload` class was changed:
    -   it now implements ` Symfony\Contracts\Service\ResetInterface`
    -   `__construct()` changed its interface:
    ```diff
        public function __construct(
    -       $temporaryDir,
    -       $uploadedFileDir,
    -       $imageDir,
    +       protected readonly string $temporaryDir,
    +       protected readonly string $uploadedFileDir,
    +       protected readonly string $imageDir,
            protected readonly FileNamingConvention $fileNamingConvention,
            protected readonly MountManager $mountManager,
            protected readonly FilesystemOperator $filesystem,
            protected readonly ParameterBagInterface $parameterBag,
    +       protected readonly ImageRepository $imageRepository,
        ) {
    ```
-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    -   `FileUpload::getPositionForNewEntity()` method and the related functionality
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4273e340869e061f4f9fea72634a7cac9deabc94) to update your project

#### check usage of removed constraint([#3252](https://github.com/shopsys/shopsys/pull/3252))

-   class `Shopsys\FrameworkBundle\Form\Constraints\ConstraintValue` was removed

#### {limited user can't user free transport and gateway payments ([#3355](https://github.com/shopsys/shopsys/pull/3355))

-   constructor `Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation` changed its interface
    ```diff
        public function __construct(
            protected readonly Domain $domain,
    +       protected readonly CustomerUserRoleProvider $customerUserRoleProvider,
        ) {
    ```
-   constructor `Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation` changed its interface
    ```diff
        public function __construct(
            protected readonly BasePriceCalculation $basePriceCalculation,
            protected readonly PricingSetting $pricingSetting,
    +       protected readonly CustomerUserRoleProvider $customerUserRoleProvider,
        ) {
    ```
-   constructor `Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade` changed its interface
    ```diff
        public function __construct(
            protected readonly PricingSetting $pricingSetting,
    +       protected readonly CustomerUserRoleProvider $customerUserRoleProvider,
        ) {
    ```
-   constructor `Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation` changed its interface

```diff
    public function __construct(
          protected readonly BasePriceCalculation $basePriceCalculation,
          protected readonly PricingSetting $pricingSetting,
    +     protected readonly CustomerUserRoleProvider $customerUserRoleProvider,
    ) {
```

-   method `Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade::isFree` changed visibility to `protected`
-   method `Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation::getCalculatedPricesIndexedByPaymentId` was removed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/eeb74b92513741063b273dd0540bc055b7b22488) to update your project

#### limited user can't use filter by price and order by price ([#3356](https://github.com/shopsys/shopsys/pull/3356))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/2919a7d5fef055b82b93334869e21f8f291a950f) to update your project

#### admin can remove an image and upload a new one ([#3166](https://github.com/shopsys/shopsys/pull/3166))

-   `Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface` was removed
-   `Shopsys\FrameworkBundle\Component\Image\ImageFactory::create()` method visibility changed from public to protected
-   `Shopsys\FrameworkBundle\Component\Image\ImageFacade::uploadImage()` was removed

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8a4a66699f26383e920dcc24a51a30481b082d41) to update your project

#### the price filter now takes into account the pricing group ([#3361](https://github.com/shopsys/shopsys/pull/3361))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/7942331783433c4cff76719f12c8d654591f9224) to update your project

#### list of company customer orders on company detail in administration ([#3365](https://github.com/shopsys/shopsys/pull/3365))

-   `OrderListType` now accepts `Customer` instead of `CustomerUser` as required parameter
-   `OrderListType` is now limited by new parameter `limit` that defaults to `10`, set your limit to different number if needed
-   `\Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchConfig` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                OrderNumberFilter $orderNumberFilter,
                OrderCreateDateFilter $orderCreateDateFilter,
                OrderPriceFilterWithVatFilter $orderPriceFilterWithVatFilter,
                OrderStatusFilter $orderStatusFilter,
                OrderProductFilter $orderProductFilter,
                OrderPhoneNumberFilter $orderPhoneNumberFilter,
                OrderStreetFilter $orderStreetFilter,
                OrderNameFilter $orderNameFilter,
                OrderLastNameFilter $orderLastNameFilter,
                OrderEmailFilter $orderEmailFilter,
                OrderCityFilter $orderCityFilter,
        +       OrderCustomerIdFilter $orderCustomerIdFilter,
            ) {
        ```
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b3f0568d994056a4683f94e667cd3077cd49bb51) to update your project

#### remove usage of bind DataLoaderInterface services ([#3350](https://github.com/shopsys/shopsys/pull/3350))

-   bind variables for `DataLoaderInterface` services are removed from services definition
    -   arguments are passed directly to the appropriate services definition
    -   when extending services, remember to add appropriate tags – especially for ResolverMap classes
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/9b96a98e234ffd073d0d97c178be15386341e580) to update your project

#### moved most of the FriendlyUrl functionality from project-base to the framework ([#3369](https://github.com/shopsys/shopsys/pull/3369))

-   99% of functionality has been moved; only functionality connected to ReadyCategorySeoMix is still kept in project-base, until SEO categories are moved to the framework
-   for more information, see the [section about the features movement](#movement-of-features-from-project-base-to-packages)
-   method `\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository::__construct` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
    +       protected readonly EntityNameResolver $entityNameResolver,
        ) {
    ```
-   property `\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData::$id` has been removed, use `\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData::$entityId` instead
-   method `\Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDetailFriendlyUrlDataProvider::createFromIdAndName()` has been removed, use `\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactory::createFromIdAndName()` instead
-   method `\Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDetailFriendlyUrlDataProvider::createFromIdAndName()` has been removed, use `\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactory::createFromIdAndName()` instead
-   method `\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade::removeFriendlyUrlsForAllDomains()` has been removed without a replacement
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/2d9808a72e16674849270529bff7684e4b6969c7) to update your project

#### complaint mail templates ([#3364](https://github.com/shopsys/shopsys/pull/3364))

-   `Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade` class was changed:
    -   `createMailTemplateForAllDomains()` method changed its interface:
        ```diff
            public function createMailTemplateForAllDomains(
                string $name,
        +       ?OrderStatus $orderStatus = null,
        +       ?ComplaintStatus $complaintStatus = null,
            ): void {
        ```
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/55168fa3c6f0b343021d961a3bf8bff688b47af6) to update your project

#### moved two attributes of ProductFormType from project-base to the framework ([#3376](https://github.com/shopsys/shopsys/pull/3376))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c2516fea7303de01223342d502d7c0b10efdcb4) to update your project

#### fixed image resolving for transport in FrontendAPI ([#3377](https://github.com/shopsys/shopsys/pull/3377))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/f284dbbc0a66546602a3520aa954efb258d6057b) to update your project

#### limiting image size options propagated for resizing in CDN to predefined set of options ([#3349](https://github.com/shopsys/shopsys/pull/3349))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e135f3dd1953f970ae8b90492dc6bf69ab070403) to update your project

#### upgrade nginx to the new version ([#3347](https://github.com/shopsys/shopsys/pull/3347))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e7ccca2d206adfdef84b472150f9fb3c260a3739) to update your project
-   remember to update your local docker-compose.yml file and rebuild the containers
-   if necessary, update the CI configuration with the new version of the nginx image

#### fixed duplicate addDomain to query builder ([#3384](https://github.com/shopsys/shopsys/pull/3384))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/fd625d52004419aca4a5f2adce3b0523f8f6a4df) to update your project

#### administration of complaints ([#3354](https://github.com/shopsys/shopsys/pull/3354))

-   check `Shopsys\FrameworkBundle\Migrations\Version20240816221930`, it adds essential complaint statuses and their Czech and English translations
-   you should add translations for other languages if needed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e097093fab4b1758749e91cdefb8d8b25b852193) to update your project

#### moved category parameters to framework package ([#3387](https://github.com/shopsys/shopsys/pull/3387))

-   method `\Shopsys\FrameworkBundle\Model\Category\CategoryFacade::__construct` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly CategoryRepository $categoryRepository,
            protected readonly Domain $domain,
            protected readonly CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler,
            protected readonly FriendlyUrlFacade $friendlyUrlFacade,
            protected readonly ImageFacade $imageFacade,
            protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
            protected readonly CategoryWithPreloadedChildrenFactory $categoryWithPreloadedChildrenFactory,
            protected readonly CategoryWithLazyLoadedVisibleChildrenFactory $categoryWithLazyLoadedVisibleChildrenFactory,
            protected readonly CategoryFactoryInterface $categoryFactory,
            protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
            protected readonly EventDispatcherInterface $eventDispatcher,
    +       protected readonly CategoryParameterFacade $categoryParameterFacade,
        ) {
    ```
-   method `\Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory::__construct` changed its interface:
    ```diff
        public function __construct(
            protected readonly FriendlyUrlFacade $friendlyUrlFacade,
            protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
            protected readonly Domain $domain,
            protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    +       protected readonly CategoryParameterRepository $categoryParameterRepository,
        ) {
    ```
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/5034fa5b964ed6932ee8e04d3aae7d10e70d543d) to update your project

#### edit complaint items in admin ([#3388](https://github.com/shopsys/shopsys/pull/3388))

-   constructor `Shopsys\FrameworkBundle\Twig\UploadedFileExtension` changed its interface:
    ```diff
        public function __construct(
            ...
            protected readonly UploadedFileLocator $uploadedFileLocator,
    +       protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
    +       protected readonly CustomerUploadedFileLocator $customerUploadedFileLocator,
    ```
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/a592640a05a4cfb4937c0e7245fdd470a52ff5f6) to update your project

#### Enable pagination and quick search in blog articles in the admin ([#3393](https://github.com/shopsys/shopsys/pull/3393))

-   constructor `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository::__construct()` changed its interface
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
    +       protected readonly Domain $domain,
    ```

#### moved migration of stock settings from project-base to framework ([#3401](https://github.com/shopsys/shopsys/pull/3401))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/24415445e895daadf3b9c756c5bac0e04da03ff0) to update your project

#### Redesign store list ([#3399](https://github.com/shopsys/shopsys/pull/3399))

-   `OpeningHours` GraphQL type now returns `status` field instead of `isOpen` and returns type of `StoreOpeningTypeEnum` enum
-   `StoreOpeningHoursApiProvider::isOpenNow` method was removed. Use `StoreOpeningHoursApiProvider::getStatus` instead that returns `StoreOpeningTypeEnum` enum which now returns `STATUS_OPEN`, `STATUS_CLOSED`, `STATUS_OPEN_SOON`, `STATUS_CLOSED_SOON`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/39a7379c615b462c32fe5927e3b8fffa470e9566) to update your project

#### Complaint list ([#3362](https://github.com/shopsys/shopsys/pull/3362))

-   `\Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly Domain $domain,
                protected readonly ProductCollectionFacade $productCollectionFacade,
                protected readonly ProductAccessoryFacade $productAccessoryFacade,
                protected readonly CurrentCustomerUser $currentCustomerUser,
                protected readonly ParameterWithValuesFactory $parameterWithValuesFactory,
                protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
                protected readonly HreflangLinksFacade $hreflangLinksFacade,
                protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
                protected readonly DataLoaderInterface $productsSellableByIdsBatchLoader,
        +       protected readonly ProductVisibilityFacade $productVisibilityFacade,
            ) {
        ```
-   `\Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper` class was changed:

    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                protected readonly CategoryFacade $categoryFacade,
                protected readonly FlagFacade $flagFacade,
                protected readonly BrandFacade $brandFacade,
                protected readonly ProductElasticsearchProvider $productElasticsearchProvider,
                protected readonly ParameterWithValuesFactory $parameterWithValuesFactory,
                protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
                protected readonly DataLoaderInterface $productsSellableByIdsBatchLoader,
        +       protected readonly CurrentCustomerUser $currentCustomerUser,
            ) {
        ```

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/3ea9d05711be3fb99e500c2c2125f0df508f3032) to update your project

#### remove unused demo image([#3224](https://github.com/shopsys/shopsys/pull/3224))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/63e47a2134940210640a7abc26be6de2276499f0) to update your project

#### fixed wrong columns used in migration Version20240102112523 ([#3402](https://github.com/shopsys/shopsys/pull/3402))

-   WARNING! This migration can take up to several hours to run, depending on the size of your database. We recommend running it in a staging environment first to estimate the time it will take to run on production. You can run this migration before deploying the new version to production so your project is not locked during deployment and then once again after deployment only for entries created in the meantime.

#### moved FlagDetailFriendlyUrlDataProvider from project-base to the framework ([#3416](https://github.com/shopsys/shopsys/pull/3416))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/0f962703ecb45e7d1e43ed866c133dbf81456012) to update your project

#### revoke access token validity immediately after refresh token removal ([#3417](https://github.com/shopsys/shopsys/pull/3417))

-   method `Shopsys\FrameworkBundle\Component\EntityLog\Detection\DetectionFacade::__construct()` changed its interface
    ```diff
        public function __construct(
            protected readonly Security $security,
    -       protected readonly CurrentCustomerUser $currentCustomerUser,
    ```
-   method `Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator::__construct()` changed its interface
    ```diff
        public function __construct(
            protected readonly TokenFacade $tokenFacade,
            protected readonly FrontendApiUserProvider $frontendApiUserProvider,
    +       protected readonly CustomerUserFacade $customerUserFacade,
    ```
-   check if you have extended `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade::changePassword()` method and if so,
    remember to call `$this->customerUserRefreshTokenChainFacade->removeAllCustomerUserRefreshTokenChains($customerUser);` after password change
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/5699f2df64cdb28c01a86514d573d4fed8d327ae) to update your project

#### fixed EntityLogger to no longer empty collection that is cleared and filled on every update ([#3418](https://github.com/shopsys/shopsys/pull/3418))

-   method `\Shopsys\FrameworkBundle\Component\EntityLog\EventListener\EntityLogEventListener::__construct` changed its interface:
    ```diff
        public function __construct(
    -       protected readonly EntityManagerInterface $em,
    +       protected readonly EntityManagerInterface $entityLogEntityManager,
    +       protected readonly EntityManagerInterface $applicationEntityManager,
            protected readonly LoggerInterface $monolog,
            protected readonly LoggableEntityConfigFactory $loggableEntityConfigFactory,
            protected readonly ChangeSetResolver $changeSetResolver,
            protected readonly EntityLogFacade $entityLogFacade,
        ) {
    ```
-   definition of service `Shopsys\FrameworkBundle\Component\EntityLog\EventListener\EntityLogEventListener` changed:
    ```diff
        Shopsys\FrameworkBundle\Component\EntityLog\EventListener\EntityLogEventListener:
    +    arguments:
    +        $entityLogEntityManager: '@doctrine.orm.entity_logging'
         tags:
             - { name: doctrine.event_listener, event: postPersist, priority: 1 }
             - { name: doctrine.event_listener, event: postUpdate, priority: 1 }
             - { name: doctrine.event_listener, event: preRemove, priority: 1 }
             - { name: doctrine.event_listener, event: postFlush, priority: 1 }
    ```

#### fixed redundant log for the Money type if the scale of compared object was different ([#3405](https://github.com/shopsys/shopsys/pull/3405))

-   class `\Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges` now implements `\JsonSerializable` interface to provide output for the `json_encode` function, update your implementation if you need to provide more than default data

#### fixed duplicate video forms in administration product ([#3422](https://github.com/shopsys/shopsys/pull/3422))

-   changed structure to common solution
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/9bc410a1f7e3dd21868058906c32efb0e3cd57de) to update your project

#### improved formatting of Entity logs ([#3423](https://github.com/shopsys/shopsys/pull/3423))

-   method `\Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ResolvedChangesFormatter::__construct` changed its interface:
    ```diff
        public function __construct(
            protected readonly CollectionChangesFormatter $collectionChangesFormatter,
            protected readonly ScalarDataTypeFormatter $scalarDataTypeFormatter,
            protected readonly MoneyDataTypeFormatter $moneyDataTypeFormatter,
            protected readonly DateTimeDataTypeFormatter $dateTimeDataTypeFormatter,
    +       protected readonly BooleanDataTypeFormatter $booleanDataTypeFormatter,
        ) {
    ```

#### make tests resistant against admin locale change ([#3430](https://github.com/shopsys/shopsys/pull/3430))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/173db4fe948c1fbb5656e6c264d09149662a7184) to update your project

#### Remove the transport type entity and replace it with enum class ([#3431](https://github.com/shopsys/shopsys/pull/3431))

-   method `Shopsys\FrameworkBundle\Component\Packetery\PacketeryCronModule::__construct()` changed its interface
    ```diff
        public function __construct(
            protected readonly PacketeryClient $packeteryClient,
            protected readonly OrderFacade $orderFacade,
    -       protected readonly TransportTypeFacade $transportTypeFacade,
    ```
-   the transport type entity was removed and replaced by an enumeration class `Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum`
-   controller `Shopsys\FrameworkBundle\Controller\Admin\TransportTypeController` was removed
-   form type `Shopsys\FrameworkBundle\Form\Admin\Transport\TransportTypeFormType` was removed
-   method `Shopsys\FrameworkBundle\Model\Order\OrderFacade::getAllWithoutTrackingNumberByTransportType()` changed its interface:
    ```diff
        public function getAllWithoutTrackingNumberByTransportType(
    -       TransportType $transportType,
    +       string $transportType,
    ```
-   method `Shopsys\FrameworkBundle\Model\Order\OrderRepository::getAllWithoutTrackingNumberByTransportType` changed its interface:
    ```diff
        public function getAllWithoutTrackingNumberByTransportType(
    -       TransportType $transportType,
    +       string $transportType,
    ```
-   property `Shopsys\FrameworkBundle\Model\Transport\Transport::$transportType` was removed
-   method `Shopsys\FrameworkBundle\Model\Transport\Transport::getTransportType()` was removed
-   property `Shopsys\FrameworkBundle\Model\Transport\TransportData::$transportType` was removed
-   method `Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory::__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly VatFacade $vatFacade,
            protected readonly Domain $domain,
            protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    -       protected readonly TransportTypeFacade $transportTypeFacade,
    ```
-   class `Shopsys\FrameworkBundle\Model\Transport\Type\Exception\TransportTypeNotFoundException` was removed
-   class `Shopsys\FrameworkBundle\Model\Transport\Type\TransportType` was removed
-   class `Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData` was removed
-   class `Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeDataFactory` was removed
-   class `Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeFacade` was removed
-   class `Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeRepository` was removed
-   class `Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeTranslation` was removed
-   graphql field `Transport::transportType` was removed, use `Transport::transportTypeCode` instead
-   graphql decorator `TransportTypeDecorator` was removed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8cf2a24b3934b69a22a225bddd7c6c6dad68bf3f) to update your project

#### Two level cache ([#3031](https://github.com/shopsys/shopsys/pull/3031))

-   `Shopsys\FrameworkBundle\Component\Cache\InMemoryCache` class was introduced to replace all the custom implementations of local application cache
-   `InMemoryCache` dependency was added while `ResetInterface` implementation was removed along with custom local cache properties from the following classes:
    -   `Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade`
    -   `Shopsys\FrameworkBundle\Component\FileUpload\FileUpload`
    -   `Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser`
    -   `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade`
    -   `Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade`
    -   `Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader`
    -   `Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader`
    -   `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository`
    -   `Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade`
    -   `Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository`
    -   `Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItemFactory`
    -   `Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/634d19de21703f68f7c6b0201cf0181de82fbcc4) to update your project

#### update Twig to the latest version to prevent security issues ([#3443](https://github.com/shopsys/shopsys/pull/3443))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/ec49f0c5b52fac0d8fe92ac6565449648b83a684) to update your project

#### add complaint detail url to mail templates ([#3445](https://github.com/shopsys/shopsys/pull/3445))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/542cd22341912a919d0e773725c330fbf5d09f27) to update your project

#### add timeouts to snc_redis clients ([#3226](https://github.com/shopsys/shopsys/pull/3226))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/902b3ad408d38d9212cccb8e01a10b51cdc1dba9) to update your project

<!-- backendNotes -->

### Storefront

#### added query/mutation name to URL and headers ([#3041](https://github.com/shopsys/shopsys/pull/3041))

-   queries and mutation names are now part of the request URL, which means query is not made to `/graphql/` but `/graphql/<QueryName>/`
-   if you do not want this, you can skip the changes (ensure there is no `operationNameExchange` used in your URQL client)
-   if you apply this change, it should be easier for you to debug requests in tools like Kibana and also see operation names in browser network tab
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6556e62029fbb13a3f942e6eab1f1d92bc035a31) to update your project

#### fix display advert in categories ([#3040](https://github.com/shopsys/shopsys/pull/3040))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/23ca69c657204d90b878d690b58c7220310e9cf6) to update your project

#### refactored different levels of error debugging on SF ([#3033](https://github.com/shopsys/shopsys/pull/3033))

-   we now have three levels (`console`, `toast-and-console`, `no-debug`) based on which verbose error messages are shown to developers
-   in your projects, you should use constants `isWithConsoleErrorDebugging`, `isWithToastAndConsoleErrorDebugging`,`isWithErrorDebugging` to control different debugging in all places where you handle errors or exceptions
-   docs were rewritten to match this new approach, so you can read them to get a better idea
-   verbose logging was also added for mutations, so if you need to handle this differently, check `errorExchange.ts`
-   added .env.development for SF, so you should put all your env variables for development there, if you need to handle them in a specific way, differently from the app being in production mode
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/13e4364b882664a4aa2dafcecd1623fc7cdaaa5b) to update your project

#### refactor mobile menu ([#3035](https://github.com/shopsys/shopsys/pull/3035))

-   now the whole component is refactored and is included with new design
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/9e741a5ab5e3e1b90539642ef765744b88b7e747) to update your project

#### added visitAndWaitForStableDOM for visiting pages in cypress ([#3071](https://github.com/shopsys/shopsys/pull/3071))

-   change all `cy.visit` to `cy.visitAndWaitForStableDOM`, to make sure that cypress waits for the DOM to be stable before interacting
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e7b14e33b72af91aa67de2fc414c65fc05125bcb) to update your project

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
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/fe6dffd3fd29603adb069f0491bd3214c9b9506e) to update your project

#### GQL generated files split ([#3078](https://github.com/shopsys/shopsys/pull/3078))

-   you should update all the necessary imports of GQL generated files
    -   remove all imports from `/graphql/generated`
    -   instead import from the given `/graphql/requests/**/*.generated.tsx` file or from `/graphql/types.ts`
-   remove all `Api` suffixes from all generated types
    -   or if you want to keep them, do not accept this change to `codegen-config.ts`:

```diff
- typesSuffix: 'Api',
+
```

#### order process fixes ([#3032](https://github.com/shopsys/shopsys/pull/3032))

-   to handle access management on order pages, use the new `useOrderPagesAccess` hook, which handles both loading state and possible redirects
-   there was a bug with manual cache updates for mutations which are deduplicated, to fix this for any other mutations you might have, follow the example added to `graphcache.md`
-   move all your cypress API custom commands to `/cypress/support/api.ts`
-   in cache updates, make sure you do not condition your code on properties, which are sometimes not available
    -   this issue was for example present during transport and payment change for logged in user, where it was caused by the manual cache updates being conditioned on the presence of cart UUID, which is never present for a logged in user
    -   in this case, we caused it by now checking the entire response object (cart nullability)
-   make sure you do not make any actions conditioned on presence of transport/payment during the initial renders, as they are null, because the cart is not loaded yet
    -   this issue was for example present during transport and payment pre-selection based on previous order, where because of asynchronicity of zustand store hydration, when the decision was made to pre-select transport/payment from previous order, transport and payment were always null, thus the transport and payment were always pre-selected, even though the user has already changed his decision
-   check that if you provide multiple identical handlers to any type of inputs (such as multiple `onBlur` or `onChange` as we have in `TextInputControlled`), you correctly combine them into a single handler and call that
    -   you can get insipred in the aforementioned `TextInputControlled`
-   use the newly provided `useOnFinishHydrationDefaultValuesPrefill` if you want to prefill your form with default values which might not be available during the initial load (e.g. because of store hydration)
-   fixed issue with email value update in order third step, where the value was not correctly updated, because the update function used an outdated value of the field. Instead, we now use the event target value, which solves this issue
    -   you should check for similar issues in your code, and make sure that any `onBlur` or `onChange` handlers operate on the newest value
-   contact information page (and form) now loads only after cart is fully loaded, which means you can remove any custom async logic which reacts to cart being loaded and depend on it being there from the beginning
-   `CurrentCustomerUser.ts` was removed and the code moved, as it was used only in a single place, so if you are using it in multiple places, you should keep it
-   radiobutton component now does not accept `onChangeCallback` prop anymore, so you should provide your actions and callbacks via `onClick` if you need to to provide "unchecking" functionality, or `onChange` if you do not
-   select component now accepts ID which is given to the select input and can be used for tests and similar
-   `ContactInformationFormWrapper` was renamed to a better-suited `ContactInformationFormContent`, as it does not wrap the form, but rather contains it
-   `deliveryAddressUuid` is now never null, but we operate with an empty string or an actual UUID, which was done for easier operations with the property, so your application does not need to check for null, but you should check your conditions if they correctly check the empty string value (however, it still has to be mapped to null just before sending to API)
-   removed various useEffects for contact information and rather approached the problem from a synchronous and controlled POV
    -   countries are now loaded during SSR, so we do not have to set the default value using useEffect
    -   we do not set delivery address fields based on `deliveryAddressUuid` using useEffect in the form, but rather just put it in the mutation later, which simplifies the logic
    -   we do the same with pickup place details, which required a more complex validation logic, where we validate delivery address fields based on pickup place, delivery address UUID, and currently logged-in user
    -   what this means for you is that you should ideally also avoid these hacky useEffects, ideally only fill your form with the data you (really) need, and map it later for your mutation
    -   you also now cannot expect the contact information form to contain the information from the delivery address selected by UUID (deliveryAddressUuid) and from the pickup place
-   contact information is now removed from store (and session storage) after logout, so you should either not expect it there, or not accept these changes
-   `useCountriesAsSelectOptions` was added to get mapped countries as select options, which uses `mapCountriesToSelectOptions` internally and thus the function is not exported anymore
    `useCountriesAsSelectOptions` should be used to get countries as select options now
-   removed `useHandleContactInformationNonTextChanges` and instead use `onChange` handlers
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b160927801a711cc6c548fba1a510b61df7342e4) to update your project

#### SF large files split ([#3081](https://github.com/shopsys/shopsys/pull/3081))

-   icons are now in separate files, so update your imports and do the same with any further icons you might have
-   large GTM files were split, so update your imports and do the same to any other GTM files you might have
-   large helper files were split, so update your imports and do the same to any other helper files you might have
-   large hook files were split, so update your imports and do the same to any other hook files you might have
-   `useQueryParams` hook was split into separate files, so update your code and imports
-   `hooks` and `helpers` folders were unified under a new `utils` name, so update your imports and move your files there
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/cc72f41a8c887f667cc9cf10cc3727ce45ead1f1) to update your project

#### codegen types and values differentiation ([#3085](https://github.com/shopsys/shopsys/pull/3085))

-   all codegen-generated GQL types now have a `Type` prefix (suffix did not work as expected)
-   you should update all your imports and make sure to apply the new config
-   you should also regenerate your codegen-generated files to make sure your own files apply the new config
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/10ac31c81954ca2087d070f7ddf188406efedcb1) to update your project

#### cypress make command fix ([#3090](https://github.com/shopsys/shopsys/pull/3090))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b90061eb42f5754f049ef47976a70397290ea7c5) to update your project

#### cypress stability fixes ([#3093](https://github.com/shopsys/shopsys/pull/3093))

-   make sure all your links which wait for the `href` to be fetched dynamically use the same styling even before the `href` is available
    -   you can use the newly provided `linkPlaceholderTwClass` as seen below

```diff
components={{
   lnk1: privacyPolicyArticleUrl ? (
       <Link isExternal href={privacyPolicyArticleUrl} target="_blank" />
   ) : (
-       <span />
   ),
}}
components={{
   lnk1: privacyPolicyArticleUrl ? (
       <Link isExternal href={privacyPolicyArticleUrl} target="_blank" />
   ) : (
+       <span className={linkPlaceholderTwClass} />
   ),
}}
```

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6139394334359cb6771a05642ba6696bf4b72a27) to update your project

#### fixed incorrect keys in cache exchange config ([#3094](https://github.com/shopsys/shopsys/pull/3094))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/d1d2e740a2733168c3d53c915549819289a27e2c) to update your project

#### Add Developer Styleguide ([#3083](https://github.com/shopsys/shopsys/pull/3083))

During new project implementation phase it is important for a developer who is adjusting new design to be able to see how those changes affect important/base components. This is why we have Styleguide where we have those base components so implementation is faster and developer has better overview of the changes.

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e4dd9d4941fd1c75dfbf83627d3d4333cf5d9dfc) to update your project

#### fixed fix SEO page title, description and heading H1 ([#3108](https://github.com/shopsys/shopsys/pull/3108))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6a5191a773cef3dce05905e98385118a285c1214) to update your project

#### SF defer parts of DOM ([#3089](https://github.com/shopsys/shopsys/pull/3089))

-   This PR introduces the possibility of deferring non-critical SF content
-   It also implements it in various places, which you might or might not want to implement as well
-   The best set of steps for you to apply these changes is to read the documentation at `docs/storefront/component-deferring.md` and decide if you want to implement this logic
    -   If you do, then apply it to the already implemented places and use the documentation to pinpoint any other possible places
    -   If you do not, then you can completely omit these changes, as they are not necessary for any future implementation
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/2f9967bfaabe67f8230e9e2499c90bfd55dd3665) to update your project

#### removed duplicated price display on product detail ([#3150](https://github.com/shopsys/shopsys/pull/3150))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/80ca9c2bbdedd06db18a985f1bdfb1e4a47fcd52) to update your project

#### cookies store smarter init ([#3145](https://github.com/shopsys/shopsys/pull/3145))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/a4af4bf2e8054f03b363e30fee4cdd3ef4a33d24) to update your project

#### cypress tests extra scenarios ([#3052](https://github.com/shopsys/shopsys/pull/3052))

-   you should rewrite all `it()` blocks within your test suites from arrow syntax `() => {}` to function syntax `function () {}` as you need to be able to access the `this` keyword
-   `takeSnapshotAndCompare` now accepts the title of the test plus a suffix

```ts
takeSnapshotAndCompare(this.test?.title, 'something to add');
```

-   if the `this.test?.title` is equal to, let's say, `My great test`, this results in the snapshot file being named `My great test (something to add).png`
-   if you use our Docker setup for cypress tests, you should remove setting of the device pixel ratio using `cypress-set-device-pixel-ratio`, as it is not necessary (you can also remove the package)
-   you should not use `waitForStableDOM` in your tests, but rather use `waitForStableAndInteractiveDOM`, as this also checks for skeletons and the NProgress bar
-   `visitAndWaitForStableDOM` was renamed to `visitAndWaitForStableAndInteractiveDOM` and now uses `waitForStableAndInteractiveDOM` internally
-   `reloadAndWaitForStableDOM`was renamed to `reloadAndWaitForStableAndInteractiveDOM` and now uses `waitForStableAndInteractiveDOM` internally
-   you should regenerate your cypress screenshots
-   you should read the updated cypress documentation in our docs in order to understand all the new changes and be able to take advantage of them
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/5ceafe66364d90feea4b115531bde128e39be24e) to update your project

#### minor improvements to cypress tests ([#3163](https://github.com/shopsys/shopsys/pull/3163))

-   it is now possible to remove pointer events from elements during cypress screenshots, so you should use this if you have problems with failing screenshots because of different hover/active states
-   as always, read the docs regarding our cypress tests to learn more
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b0433f599ab7079bd5a422aaf792c036c1750816) to update your project

#### implement new base design + product page ([#3132](https://github.com/shopsys/shopsys/pull/3132))

We want to implement more usable UI design which will be better base for upcoming projects. These changes are about new design for basic stuff like colors and base elements + design for whole product page.

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/ffd3462989b9ada2d62783d59a4d21cc80d38485) to update your project

#### rename variable differentDeliveryAddress into isDeliveryAddressDifferentFromBilling ([#3161](https://github.com/shopsys/shopsys/pull/3161))

-   bool variable differentDeliveryAddress was renamed into more suitable isDeliveryAddressDifferentFromBilling across the project
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/3096101a49d6099602aa4d6475948ae97f27b319) to update your project

#### simple navigation images are now blacked-out during cypress tests ([#3174](https://github.com/shopsys/shopsys/pull/3174))

-   simple navgation images are now blacked-out during cypress tests
-   make sure you add the blackout everywhere where your snapshots contain simple navigation with images
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/f763201a72148f6bf1ccb867e147cd2cc631fc29) to update your project

#### removed invalid cache invalidation when adding to product list ([#3172](https://github.com/shopsys/shopsys/pull/3172))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/65d7014964881b3b482372f4367b7eba2acd91be) to update your project

#### bump versions of SF packages to fix security issues([#3191](https://github.com/shopsys/shopsys/pull/3191))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/ba8cc5811938b5e95695a439317b3d403a20c673) to update your project

#### useContext refactoring ([#3176](https://github.com/shopsys/shopsys/pull/3176))

-   move your contexts as high in the component tree as it makes sense (especially with regards to optimizations), in order to maximally avoid unavailable contexts, but to also not kill the performance of you app with unnecessary rerenders
    -   in our case it meant having all context providers in `_app.tsx`, which means less headaches, but might not be feasible for you
    -   we were only able to do this because we use all our contexts globally, so if you have a local context, do not move it to the root, as that will cause unnecessary rerenders
-   we now throw errors if contexts are used outside of providers, which is good if you want to discover bugs as soon as possible, but might not be the right choice for you
    -   if you can and want provide default state instead of throwing errors, you can do that as well
-   we never use `useContext` directly, but provide a wrapper which takes care of extra actions, such as throwing errors if used outside of a provider
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/81a86017413ed3216ebf368e263b9f55f87b9aa3) to update your project

#### minor array keys fix ([#3178](https://github.com/shopsys/shopsys/pull/3178))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/2d5d20be052cd113b47e6e02a627c48f1de88907) to update your project

#### fixed translation on customer's edit profile page ([#3179](https://github.com/shopsys/shopsys/pull/3179))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/269b8e1189c07aa496e9ef0071ab9ead09bc9370) to update your project

#### added option to migrate persist store ([#3171](https://github.com/shopsys/shopsys/pull/3171))

-   persist store can now be migrated (read docs in `store-management.md`)
-   all persist store slices should now expose default state as a constant
-   docs regarding store management (`store-management.md`) were improved, so make sure that you implement changes to store based on them
-   remember to update the `DEFAULT_PERSIST_STORE_STATE` constant in your cypress tests to suit the new version of persist store
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/ff6755cd76e18e6af9a23576aa8fa857125ca905) to update your project

#### rename user-consent related code accordingly ([#3181](https://github.com/shopsys/shopsys/pull/3181))

-   route /cookie-consent was renamed to /user-consent
-   components and translations were also renamed/rewritten
-   you should follow this naming convention in your app as well
-   if you have any other special articles (other than t&c, privacy policy, user consent), you should correctly handle their error user codes
-   if you want to use a different approach to non existent special articles (other than the one described in commit messages), be sure to modify the logic, as now
    -   if special articles are not found, error is now not thrown
    -   if t&c and privacy policy articles are not found, the text where they are used are the same, but without the link
    -   if user consent article is not found, we do not display the footer link to the user consent update page, the consent update bar (`UserConsent.tsx`), and the consent update page returns 404
    -   `article-not-found` error user code now displays a better message (not unknown error)
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6534bafb99697ac25d74a1ef376836141f26185b) to update your project

#### fix slider ([#3130](https://github.com/shopsys/shopsys/pull/3130))

On Chrome there was issue when clicking on next/previous button while slider is only partially visible, in this case scroll freeze after scroll onto slider element and is stuck. Fixed by scrolling first onto the slider element and with a little delay continue sliding onto an item in the slider.

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/cd1c833b0445a2361456921e54fab33aa53ecd09) to update your project

#### fix usage of INTERNAL_ENDPOINT env variable ([#3205](https://github.com/shopsys/shopsys/pull/3205))

-   INTERNAL_ENDPOINT env variable is defined with trailing slash, so any usage of it should not include slash at the beginning of the path
-   update your usages in a similar way as in the PR
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/69bcf570c77ee4eabc3f11fe7a1548408a501af8) to update your project

#### order process refactoring ([#3155](https://github.com/shopsys/shopsys/pull/3155))

-   `useCurrentCart` hook now returns `isCartFetchingOrUnavailable` instead of `isFetching`, which is also based on auth loading and cart not being `undefined`, so you should use this boolean pointer instead
    -   this means you also do not have to check for auth loading and cart being `undefined`, as this is already part of the pointer
-   `isWithCart` is not returned from `useCurrentCart` anymore, as it was only necessary inside the hook
    -   if you need this pointer, you should keep it
-   order actions (back and next) are now both buttons and always accept callbacks/handlers, instead of links
-   `SkeletonManager` now accepts `pageTypeOverride`, which can be used to set the page loading type even for the first render
    -   this is helpful for pagers where we want to display skeletons even for the first load (cart page, order process pages)
    -   with these changes, you can apply `SkeletonManager` anywhere in the app
-   `OrderLayout` now also contains `SkeletonManager`, so you can add a page type for all order process pages
    -   if you have any other layout, you can add `SkeletonManager` there as well
-   contact information page's logic was moved outside from the components (into utils)
-   delivery address UUID is now `new-delivery-address` if the user wants to fill a new delivery address, so you should change any conditions you might have where you check for it being an empty string
-   cypress `goTo` actions should have a check for the URL to which the test navigated, so make sure your `goTo` actions contain it
-   if your tests fill in a delivery address and then check that it is correctly changed, you should use `deliveryAddress2` from the demodata for the second (changed) delivery address in order to better check that everything works as expected
-   you should rename all `fetching`, `isFetching`, `loading`, and `isLoading` variables to more descriptive names, such as `isProductFetching`
-   you should rename all `result`, `response`, and `data` variables to more descriptive names, such as `productDetailData`
-   you should rename all mutation fetching states to names that describe what is happening, e.g. from `isCreateOrderMutationFetching` to `isCreatingOrder`
-   you should rename all your `utils.ts` and `utils.tsx` files to more descriptive names, such as `lastVisitedProductsUtils.ts`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/098d9e1a838209b833448c2e99afd3c6bdfe735c) to update your project

#### Luigi's Box search relations fix ([#3217](https://github.com/shopsys/shopsys/pull/3217))

-   `SearchQuery.graphql` now incldues products search query in order to relate searched categories, brands, and articles to searched products
    -   this is required for Luigi's Box, so if you are not using it for search, then you might skip this change
    -   make sure that `SearchQuery.graphql` includes the same products search query (same fragments, same variables) as `SearchProductsQuery.graphql` in order to utilize URQL cache
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/0798f7a79d5031c89c9d34d723d6f4e2d882890d) to update your project

#### change free transport limit in demo data ([#3199](https://github.com/shopsys/shopsys/pull/3199))

-   social links in footer were blacked-out for cypress snapshots as they caused issues, so do not forget to add the blackout to snapshots where these links are visible, and also regenerate your screenshots

#### add privacy policy checkbox to contact form ([#3219](https://github.com/shopsys/shopsys/pull/3219))

-   privacy policy checbox is required and needs to be checked in order to submit the contact form
-   the checkbox have replaced the text agreement of the privacy policy by clicking on the submit button
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/3e8c8d8c220ae6fdcb0159b964c6b70dfb143e9e) to update your project

#### restyle orders page ([#3123](https://github.com/shopsys/shopsys/pull/3123))

-   Introduced new CustomerLayout for user section
-   Introduced new user navigation for user section
-   Restyle orders page according to the new design
-   Implement a LinkButton, a link that looks like a button and has props from the Button component, but uses an anchor tag inside
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/ff7052d323752030058b1a0310d33473e29832d4) to update your project

#### implemented registration and login via social networks ([#3154](https://github.com/shopsys/shopsys/pull/3154))

-   implemented are login via Facebook, Google and Seznam
-   see the [docs](https://docs.shopsys.com/en/15.0/integration/social-networks/) for more information about the functionality
-   actions after login (various cleanups and updates) are now handled in a handler exposed by the `useHandleActionsAfterLogin` hook, so be sure to add all you actions there
    -   if there are specific callback or actions which should only happen if logged-in using social networks/native login, then be sure to mirror that in the aforementioned hook
-   because of the asynchronous logic of auth loading (auth loader messages are dispatched by `<Loaders />` which are loaded dynamically on the client) there was a race conditions which caused auth messages to be displayed only for a very short time, so because of that `<Loaders />` are now loaded during SSR as well
-   see also [#3276](https://github.com/shopsys/shopsys/pull/3276) and [#3277](https://github.com/shopsys/shopsys/pull/3277) where the functionality was further extended
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/22ee0ba15cda535c97b0894741f5e648527dc52c) to update your project

#### SF optimizations based on projects ([#3222](https://github.com/shopsys/shopsys/pull/3222))

-   settings query now contains URLs of all special articles, so you should
    -   use them from this query and remove the individual implementations
    -   add any other similar URLs which you usually query separately
-   certain homepage server-side-fetched queries were added to redis cache to improve performance
    -   see diff to see which queries are those
    -   validate that you cache queries which would otherwise unnecessarily call the API
-   cypress now considers loader (spinner) when waiting for stable DOM, which helps you to make sure that loaders will not break snapshots
    -   you can try and regenerate your snapshots to see if maybe some of them included loaders by accident
-   storefront is now refreshed when access token could not be refreshed (using refresh token failed)
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/5d1b45dc0d522cd9b5c2ab3ccc114aa0a46e7148) to update your project

#### cypress e2e tests improvement ([#3236](https://github.com/shopsys/shopsys/pull/3236))

-   added blackouts to cypress screenshots of product list item image, product detail main image, product detail variant main image
-   added 2 retries so the test wiil be retried up to 3 times before marking it as failed
-   added z-index to product flags so that they are not hidden by the image blackout
-   added option to specify a `callbackBeforeBlackout`, which can help if you need to perform some action after the scroll, but just before blackout
    -   we have used this for tests where blackout is specified for hovered elements
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8b3a67d77602d2ca023452fbea5469a79597dfa5) to update your project

#### added telephone to saved delivery addresses in order 3rd step ([#3235](https://github.com/shopsys/shopsys/pull/3235))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/201b229001ee389829aca9361febf3e2f5060754) to update your project

#### added functionality for notImplementedYet ([#3238](https://github.com/shopsys/shopsys/pull/3238))

-   you can now give a special `notImplementedYetHandler` to things that are currently under development, so that when a user triggers or uses that piece of the application, he is notified (via a toast message) that this piece of functionality is not finished yet
-   you can also use various `NotImplementedYet` elements, which you can find in the `NotImplementedYet.tsx` file
    -   these include various wrappers, tags, and tooltips to mark not implemented things
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/726a2433102a1d219c89f3e86fdedba5fe795ba7) to update your project

#### implemented new banners slider ([#3240](https://github.com/shopsys/shopsys/pull/3240))

-   removed `keen-slider` package from dependencies
-   implemented `BannersSlider` from scratch, using custom logic with `react-swipeable`
    -   if you do not care about optimisations in this component, you can skip these changes
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/2417cddacfa00af43cd553af33fcb6dcd1f081d3) to update your project

#### fix hydration error in wish list & product comparison pages ([#3243](https://github.com/shopsys/shopsys/pull/3243))

-   fixed hydration error by default loading state for both server and client
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/2351962e9219a8724bdfbc8ec512752a9ed25095) to update your project

#### add more open graph meta tags ([#3228](https://github.com/shopsys/shopsys/pull/3228))

-   add more open graph metatags like site name, twitter and more
-   to set the site name, edit the `metatagSiteName` key in the `common.json` translation file(s)
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b34eb26b31ee95055f88d5b2fd3b1d27d459f134) to update your project

#### add category tree capabilities for articles ([#3237](https://github.com/shopsys/shopsys/pull/3237))

-   all blog pages now use `BlogLayout` which includes the blog article navigation
    -   this means that blog articles now also contain blog navigation
-   blog article page now uses its own skeleton (`SkeletonPageBlogArticle`) instead of sharing one with a simple article page
-   query for blog category detail (more specifically the `BlogCategoryDetailFragment`) does not include blog category tree anymore, but instead the `BlogCategories` is used
    -   this query is now also cached in Redis to not fetch it unnecessarily
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/bd2f041dfcdbc19c20d69b6233446dce312bdb84) to update your project

#### added dynamic year to footer copyright ([#3248](https://github.com/shopsys/shopsys/pull/3248))

-   instead of changing copyright year manually, its changing automatically now
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e2b60d4b0ffcd939de7e78786bb6baaaa6f44ee2) to update your project

#### added disallow sort to robots.txt ([#3250](https://github.com/shopsys/shopsys/pull/3250))

-   prevent indexing products page when the sort is activated
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/11769b1719b93146ea26ba1f3b3f4744b5a9e4b0) to update your project

#### split transport stores into separate query ([#3251](https://github.com/shopsys/shopsys/pull/3251))

-   instead of getting all stores for each transport, fetching stores was split into separate query
-   after clicking on stores popup, stores are fetched just for a single transport and loading skeleton is displayed
    for better UX
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4f11a7431eaa0a3ebea04531d56cd20e165f46e9) to update your project

#### restyle forms ([#3245](https://github.com/shopsys/shopsys/pull/3245))

-   restyle forms:
    -   registration
    -   login
    -   contact information in cart
    -   contact
    -   edit profile
    -   new password
    -   reset password
    -   personal data overview and export
-   create components for forms:
    -   FormContentWrapper
    -   FormBlockWrapper
    -   FormButtonWrapper
    -   FormHeading
-   add global bottom margin for h1
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/cac5864fe2d4a05f35bcc136c22d76742a54ba8c) to update your project

#### Safari wheel click freez ([#3260](https://github.com/shopsys/shopsys/pull/3260))

-   safari needs a wheel click condition for opening links in a new tab without a loading skeleton in the current tab
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/7e34b7c6abfc39e93319992b7df883e11c2e7155) to update your project

#### restyle autocomplete ([#3254](https://github.com/shopsys/shopsys/pull/3254))

-   restyle autocomplete
    -   change the appearance of the autocomplete based on the new design
    -   added skeleton
    -   decompose autocomplete in components
    -   also added scroll to autocomplete when it overflows
-   add more variant for button
    -   secondary outlined, secondary
-   refactor product list item
    -   add ability to configure visible items in product item
    -   add sizes to product item
-   restyle product list item
    -   restyle the product item to suite the small size
    -   minor changes of background, gaps of product item
-   introduce react-remove-scroll library
    -   prevents the layout from shifting when scroll is removed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/a021691567212717fe34e424790417b5662d7a49) to update your project

#### Fix unknown error in navigation ([#3259](https://github.com/shopsys/shopsys/pull/3259))

-   type was removed from `ExtendedNextLink` in `NavigationItem` component
-   links in navigation can now lead to other places than homepage and categories
-   type is fetched dynamically in middleware for navigation links
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4ccd71e7ba731eb79ad76f80f423d18b990727d4) to update your project

#### refactored form validations ([#3263](https://github.com/shopsys/shopsys/pull/3263))

-   refactor and unification form validations
-   modify new validation rules (add their names) so that it matches your wanted validation logic
-   use new validation rules in other forms you might have to fully utilize the new code
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/1a71cb126bbbbac355bbb58f22121031c4f8f999) to update your project

#### Add secure flag to cookies on https ([#3253](https://github.com/shopsys/shopsys/pull/3253))

-   when adding `secure` flag to options of any helper function from `cookies-next`, use `getIsHttps` function to only include the flag for https protocol. Otherwise, the cookie would refuse to be set on unsecure http protocol.
-   when using on server side, make sure to also provide optional `protocol` parameter, which you can get from `getProtocol` helper function. If using `getIsHttps` exclusively on client, there is no need to provide one.
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/fe3a12153a6fd9fc9bdfbe51b58e804914f94d5e) to update your project

#### image gallery visibility ([#3266](https://github.com/shopsys/shopsys/pull/3266))

-   updated responsive design for image gallery on product detail page
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/f4e39c6e9b7f250b6fcb361245842b941527c9bc) to update your project

#### main variant info message ([#3267](https://github.com/shopsys/shopsys/pull/3267))

-   added informational message for main product variant when no variants are available for purchase
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8b1e68871078ebdb6d45423accfa0d64b19d9b7b) to update your project

#### flickering navigation menu ([#3270](https://github.com/shopsys/shopsys/pull/3270))

-   navigation menu is now more user-friendly with the addition of a delay
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/14a9a300f2461da7818ff90f4c0f30c6f28addaa) to update your project

#### Switch from Seznam Maps to Google Maps ([#3268](https://github.com/shopsys/shopsys/pull/3268))

-   The selection of stores is now based on identifier instead of index in mapped stores array
-   `google-map-react` was added to handle the map rendering
-   `use-supercluster` was added to handle the merging of neighbouring stores into clusters
-   For production don't forget to set up the `GOOGLE_MAP_API_KEY` in the`.env` file. For development you can leave it blank.
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/252e498ecb9b46dd693b274b36dc36d4e65b579c) to update your project
-   see also [this commit](https://github.com/shopsys/project-base/commit/0c3cf068e26260cf3186664e9878f6c0e6853e1f) that fixes selecting store by clicking on the map

#### product files support ([#3288](https://github.com/shopsys/shopsys/pull/3288))

-   `FileFragment` was introduced
-   `ProductDetailInterfaceFragment` was updated to include files
-   file tab was introduced in the product detail page
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/2af0e63a6804ee299818c069fe219205ba309a52) to update your project

#### added mutations and queries to work with customer structure ([#3286](https://github.com/shopsys/shopsys/pull/3286))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/594fff94bad340cf87340a01c3c31aa95e3b6d89) to update your project

#### management of delivery addresses ([#3290](https://github.com/shopsys/shopsys/pull/3290))

-   customer can create and edit delivery address in the edit profile page
-   customer can add multiple delivery addresses and choose one as default
-   customer type was modified to support delivery addresses
-   delivery address form is validated
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/75abf473537db45f7511c06e17ba59ac385ddb69) to update your project

#### Add search parameters to search queries ([#3298](https://github.com/shopsys/shopsys/pull/3298))

-   added search parameters to `SearchQuery` and `SearchProductsQuery`
-   now you should provide `parameters` of the previous search (for example from `mappedFilter.parameters` in `searchUtils.ts`) whenever they are available
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/43bfd57bc00c5c37a27848df8ac16de726630a11) to update your project

#### Add more info about user into GTM events ([#3276](https://github.com/shopsys/shopsys/pull/3276))

-   new gtm event types were added (`ec.login` and `ec.registration`)
-   utilizing `LoginInfo` data we get from BE to expand current gtm user data layer (for these events: `page_view`,
    `ec.create_order`, `ec.login` and `ec.registration`)
-   added new `useAfterUserEnry` hook that could be used to trigger any events after successful login/registration and
    page reload with data about current user ready be consumed
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/de84d0547df5c8d1141878b189a31626e23f8f41) to update your project

#### active filter slider is not showing up ([#3300](https://github.com/shopsys/shopsys/pull/3300))

-   users can now see the active filter slider at the top of the filter panel
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8ed487c71ef56d65d206bbc385aff89a8fb39ffb) to update your project

#### unification company user forms validation ([#3299](https://github.com/shopsys/shopsys/pull/3299))

-   unified validation logic for company user registration and profile edit forms to ensure consistency and maintainability.
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/fc2780bd25b981cf3e01170bd2288115c5338f24) to update your project

#### promocode button height ([#3302](https://github.com/shopsys/shopsys/pull/3302))

-   promocode button is now the same height as input field
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b8f62e2f11cdcaf7c381c740fa06188282aba42c) to update your project

#### Fix main navigation skeleton loaders ([#3287](https://github.com/shopsys/shopsys/pull/3287))

-   Show skeleton loaders for main navigation
-   You can choose default skeleton to show by changing `DEFAULT_SKELETON_TYPE` in `DefferedNavigation.tsx` component.
    Note that this skeleton is applied to ALL navigation links in the main navigation
-   `isHrefExternal` function was added to check for external links for which we don't want to trigger skeleton loader
    (this would result into issue with infinite skeleton loader bug)
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/dcdff163428ee87811f0f824386ecee9a2d462f6) to update your project

#### default address does not match in cart and edit profile ([#3307](https://github.com/shopsys/shopsys/pull/3307))

-   this update improves the useCurrentUserContactInformation hook, making data merging more reliable and ensuring consistent handling of user contact information from both the API and the store
-   customer default billing address in cart now matches the one in edit profile page
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/016328119edbeca6bb15845b8868efd671a224fa) to update your project

#### product detail responsive ([#3309](https://github.com/shopsys/shopsys/pull/3309))

-   the product detail page is now appearing correctly on mobile phones
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/393591083146b55d8d509abfef2ea432fbb64946) to update your project

#### Storefront color system implementation ([#3311](https://github.com/shopsys/shopsys/pull/3311))

-   `<SimpleNavigation />` now also accepts an icon (e.g. instead of an image)
-   all previous colors were removed from `tailwind.config.js` and instead new, semantically-oriented colors were added
    -   you should use these colors with the focus on their meaning, not the absolute value
    -   it is possible that multiple color variables have the same absolute value (have the same hexcode), but they should be used based on their semantic meaning (link colors for links, action colors for buttons, etc.)
-   some components were completely removed as we do not expect to use them (e.g `FooterBoxInfo`)
-   `<LabelLink />` component was added
    -   it should be used for simple clickable elements (links, divs) that should be easily noticeable in the UI
-   `Cart.tsx` which was used for the cart in header was renamed to `CartInHeader.tsx` (same for `DeferredCart.tsx`)
-   `<Webline />` does not accept a `type` prop anymore, but instead you can modify it using `wrapperClassName`
-   product cards (`<ProductListItem />`) now don't contain spinbox
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/083fb2daa5005a738c2c0ae0a3ac38a3991d5d6e) to update your project

#### fix blog category without articles ([#3292](https://github.com/shopsys/shopsys/pull/3292))

-   when there are no articles in the blog category, info text is displayed instead of never-ending skeleton loader
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/beebd997354111359f77a3608dbb97e719495153) to update your project

#### [storefront] fix customer user country presentation in case of null value ([#3314](https://github.com/shopsys/shopsys/pull/3314))

-   there were errors in presenting users billing address if country had null value in [#3285](https://github.com/shopsys/shopsys/pull/3285) and this PR fixes that
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/0e328563c3fa474aa7316febf037c06e24839129) to update your project

#### Add sales representative component ([#3301](https://github.com/shopsys/shopsys/pull/3301))

-   extended `useCurrentCustomerData` hook to also contain information about the sales representative
-   `SalesRepresentative` is shown only for authenticated user in the `MenuIconic` section
-   this component is part of the menu but could be easily moved
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8709e8a74afee0ebd381cceed5df77e0e068efb6) to update your project

#### fixed issue with invalid ssrExchange initialization ([#3321](https://github.com/shopsys/shopsys/pull/3321))

-   `isRedirectedFromSsr` was renamed to `getIsRedirectedFromSsr` as it more suits the function character
-   you should use a single instance of `ssrExchange` on the client, so make sure you make it a singleton (using `useMemo` as visible in `UrqlWrapper.tsx`)
-   you should call your queries inside `getServerSideProps` the same way both during the first page load and during client-side navigation
    -   this should be done because of
        -   consistency (same behavior during both types of page load)
        -   performance (should be quicker and more optimal to call queries from the server)
    -   for this, you will most likely have to move the condition using `getIsRedirectedFromSsr` lower in order to call the queries, but still not call `handleServerSideErrorResponseForFriendlyUrls` during client-side navigation, because it could cause some problems if the API query/mutation results in an error (this is a known issue)
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/924dbc30ae60d0e0673326fcca9c57f67d31ed4e) to update your project

#### fixed responsive design in mobile menu and on cart page ([#3324](https://github.com/shopsys/shopsys/pull/3324))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6d1ac40751dbaf70907b1cac31a6e0ffec67aa86) to update your project

#### disable middleware prefetch ([#3325](https://github.com/shopsys/shopsys/pull/3325))

-   Middleware prefetch is disabled now to prevent multiple useless requests to SF
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b0341236fed747d600e4d8e393db92ddfb9ebc34) to update your project

#### design fixes ([#3331](https://github.com/shopsys/shopsys/pull/3331))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/bef51f0499f984fc39ae4a254f52955e3c347670) to update your project

#### do not show prices for customer user with role `ROLE_API_CUSTOMER_SEE_PRICES` ([#3319](https://github.com/shopsys/shopsys/pull/3319))

-   added new function `isPriceVisible` which checks if the sent price is `'***'` (API's signal that the prices are hidden)
    -   this function should be used as a condition any time you want to conditionally do something or show something
-   if prices are hidden, GTM events should set all values and prices to `null`
    -   you can use `getGtmPriceBasedOnVisibility` for that, as it either parses the price as float (if visible) or returns null
-   if prices are hidden, GTM ecommerce events should contain a `arePricesHidden: true` boolean pointer
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/26b4f84f45b39160f98de4ef5cf8489c53590e4f) to update your project

#### cypress improvements ([#3337](https://github.com/shopsys/shopsys/pull/3337))

-   you can now select cypress tests (test files) to be run using `selected-acceptance-tests-`
-   scrollbar was removed from all cypress screenshots, so make sure to regenerate all screenshots
-   basic pages (homepage, product detail, category detail, blog article detail, stores page) are now tested and screenshoted
    -   make sure to extend this to any pages that you deem necessary
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/6970ea8498e8a1cda264edc6618bc6dd156136f1) to update your project

#### added opening hours to packetery selected transport label ([#3332](https://github.com/shopsys/shopsys/pull/3332))

-   changed typings of packetery point (updated some parts based on docs)
-   transport select label now contains all info about opening days and times if transport is packetery
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b56db6d968a016417381a3a8a9f84b715314e943) to update your project

#### sync product list between tabs ([#3341](https://github.com/shopsys/shopsys/pull/3341))

-   browser tabs now sync automatically when a user adds a product to comparison or wishlist
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/5bd0ce9d605496b0dff05fe57872281587c83c8f) to update your project

#### registration page skeleton ([#3342](https://github.com/shopsys/shopsys/pull/3342))

-   added a skeleton loader to the navigation process for the registration page to improve user experience during load times
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/35e8da84a0545a019f93e52df01afb22e7e0209c) to update your project

#### product slider arrows overlapping element above ([#3345](https://github.com/shopsys/shopsys/pull/3345))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8195ebd0d609d79fa8b1af07184bf9f92c09a439) to update your project

#### load more skeleton ([#3346](https://github.com/shopsys/shopsys/pull/3346))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/25ccb81414766993c822f4b5e2f51ca551c85265) to update your project

#### restyle order detail page ([#3164](https://github.com/shopsys/shopsys/pull/3164))

-   restyle order detail page
-   improve UX of change payment in order
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/a478d2dfa9ea3144f8e0ddea129624503e17a907) to update your project

#### disable grapejs image resize ([#3351](https://github.com/shopsys/shopsys/pull/3351))

-   in the GrapesJS editor, image resizing is now disabled
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/4aeaa6d7dfc7b570127487c47731e370f1fafb4f) to update your project

#### checkbox redesign ([#3352](https://github.com/shopsys/shopsys/pull/3352))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/0d3b87c3ba6791193bb7b6745c23418e71a21865) to update your project

#### radio redesign and inverted button hover ([#3358](https://github.com/shopsys/shopsys/pull/3358))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/de95a288332ba61a0580c71415ee8561d86096e4) to update your project

#### comparison and wishlist icon changes ([#3360](https://github.com/shopsys/shopsys/pull/3360))

-   icons for comparison and wishlist were changed
    -   wishlist icon is now red (filled with a custom color) when full
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/dd2034b56c0516bb4e46a2c8b75ac4710801bb99) to update your project

#### update yup package to v 1.4 ([#3367](https://github.com/shopsys/shopsys/pull/3367))

##Yup migration from 0.32 to 1.4

Type 'baseSchema' has been changed to 'Schema' - find all occurrences (at least in ValidationRules.ts)

-   `.oneOf(xxx, null, t(‘error’))` - second parameter 'null' is not present anymore. You have to delete it.

-   `Yup.string().when(xxxx` params: is, then, otherwise - all three parameters have to be functions

-   `.then: validateCity(t)` change to `then: () => validateCity(t)`

-   `.otherwise: Yup.String()` change to `otherwise: (schema) => schema`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/b1749c184b5868e72fb71787d51b7271bac56c84) to update your project

#### fix product count in autocomplete ([#3370](https://github.com/shopsys/shopsys/pull/3370))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/df387ee08bf01ad2707f76f2e4890b2443fa4e6f) to update your project

#### refresh usermenu styling ([#3373](https://github.com/shopsys/shopsys/pull/3373))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/57c394ac89adf65a29e6a1a77a619688eac15659) to update your project

#### unify z-indexes from manual added ([#3382](https://github.com/shopsys/shopsys/pull/3382))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e1859221d8e9765a8bfc1b02d5c1bdaba721de29) to update your project
-   z-flag value is for cypress tests, to let flag above blackouted images

#### Reset cache of all queries after turning off maintenance ([#3383](https://github.com/shopsys/shopsys/pull/3383))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/c67391c960591237e2320309b739326acb1bbd93) to update your project

#### complaint creation ([#3295](https://github.com/shopsys/shopsys/pull/3295))

-   complaint creation is now possible through the new complaint page and order detail page for logged in users
-   user can search for complaints by order number, product name, or product id
-   for the file upload is used react-dropzone library
-   maximum file size (in bytes) is configurable in `validationConstants.ts`
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/9513e52540a6f652c45fe9def57353baafac3240) to update your project

#### basket hover popup, Free transport progress bar ([#3389](https://github.com/shopsys/shopsys/pull/3389))

-   changed FreeTransport component to FreeTransportRange version with graphic progress bar.
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8f1098dd7eb58e346c971cbfdecc670a35bb7aef) to update your project

#### redesign sorting menu ([#3390](https://github.com/shopsys/shopsys/pull/3390))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/5d91e2deb1fe5e478877b6fb1213aa28c5a425cb) to update your project

#### fix page skeletons ([#3392](https://github.com/shopsys/shopsys/pull/3392))

-   fixed page skeletons for category and brand detail
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/487a6cfc510a654816fe57f8bc298eddd44f2952) to update your project

#### change gallery background ([#3396](https://github.com/shopsys/shopsys/pull/3396))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/865b4c0d795267589f494c00eb15b3b58ecaa3f2) to update your project

#### add complaint list and detail ([#3362](https://github.com/shopsys/shopsys/pull/3362))

-   user can see list of complaints and visit complaint detail page
-   user can search for complaints by complaint number
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/3ea9d05711be3fb99e500c2c2125f0df508f3032) to update your project

#### Design store detail page ([#3398](https://github.com/shopsys/shopsys/pull/3398))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/719a0dfda5fa5960a03e197c94e2da1724c6e0da) to update your project

#### Redesign store list ([#3399](https://github.com/shopsys/shopsys/pull/3399))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/39a7379c615b462c32fe5927e3b8fffa470e9566) to update your project

#### add usersnap widget ([#3408](https://github.com/shopsys/shopsys/pull/3408))

-   set a new ENV variable `USERSNAP_PROJECT_API_KEY` with your Usersnap API key
-   in the administration, the widget is always enabled (when a valid API key is set)
-   on the storefront, the widget can be enabled or disabled by the user on `/_feedback` page
-   you can set a new ENV variable `USERSNAP_STOREFRONT_ENABLED_BY_DEFAULT` if you want to enable the Usersnap widget on storefront by default (when a valid API key is set)
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/c5ca701a8412364a612a2fdb037f4d80e2f22584) to update your project
-   see also [project-base diff](https://www.github.com/shopsys/project-base/commit/e2865e220d254166504d1f3f5ae4a1dbe69ad38c) of [#3429](https://github.com/shopsys/shopsys/pull/3429) with additional env variable name fix

#### removed missing image ([#3414](https://github.com/shopsys/shopsys/pull/3414))

-   removed missing image in transport and payment select during the order process
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/60ae5efe19a46f56d2937ada78b345579732dbe9) to update your project

#### Replaced transportType type with transportTypeCode ([#3431](https://github.com/shopsys/shopsys/pull/3431))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/8cf2a24b3934b69a22a225bddd7c6c6dad68bf3f) to update your project

#### removed hp headlines when no data available ([#3436](https://github.com/shopsys/shopsys/pull/3436))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/bfc3ce149fdb2ced3ff41033cc91865634391d3a) to update your project

#### updated responsive toast ([#3437](https://github.com/shopsys/shopsys/pull/3437))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e4a5742d5adbdbaa142f55f0c93614d4240295e0) to update your project

#### store opening hours aligment ([#3438](https://github.com/shopsys/shopsys/pull/3438))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/515b12509be3796513dd5602451c9e30d0838600) to update your project

#### invalid sort input ([#3440](https://github.com/shopsys/shopsys/pull/3440))

-   When the user attempts to change the sort value in the URL to an invalid one, it won't result in an error. Instead, the default sort value will be applied
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/ae663ed972a1724bcfddfbac8b8ba4dd35e99bdc) to update your project

#### invalid slider input ([#3441](https://github.com/shopsys/shopsys/pull/3441))

-   when the user attempts to change the slider value in the URL from minimalValue to minimalvalue, it won't result in an error. Instead, the default value will be applied
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/e08cdecd02f65dbbde8463f6b59e3eba10914379) to update your project

#### Add customer users administration for B2B customer account ([#3353](https://github.com/shopsys/shopsys/pull/3353))

-   added a new section to customer account for B2B domain (/customer/users)
-   users with role `ROLE_API_ALL` are allowed to manage customer users
-   this change introduces new way of authorization for page and component level
    -   `authenticationRequired` in `initServerSideProps` is moved to `authenticationConfig` in which you can set `authenticationRequired` and also specify authorized role or area for each page
    -   for component level access use hook `useCurrentCustomerUserPermissions` in which you can add additional rules for restricting components based on role (or other criteria)
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/51c489b9f417fab045d46c0d87319e629b5b7f34) to update your project

#### unsupported broadcast channel ([#3448](https://github.com/shopsys/shopsys/pull/3448))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/1926b980f314782d5f8942e0dd443fe1456542d8) to update your project

#### edit profile duplicate company number error message ([#3449](https://github.com/shopsys/shopsys/pull/3449))

-   added an error message to notify users when attempting to change the company number to one that already exists
-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/a73746b3b594593c091ff91ecf90d01b61ba76ef) to update your project

#### fix hiding prices for limited user ([#3452](https://github.com/shopsys/shopsys/pull/3452))

-   see [project-base diff](https://www.github.com/shopsys/project-base/commit/7cf37ae73d52ea98f779e674f7ce69efdde996a0) to update your project

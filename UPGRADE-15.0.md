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
    -   one way or another, you should pay a special attention to the database migrations that were added with the feature movement. If they suit your needs, you should keep them and remove the original migrations from your project, otherwise, you should skip the newly added migrations.

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

#### remove UUIDs pools and replace them by UUID generated by entity datas to avoid UUIDs changes in the future ([#3075](https://github.com/shopsys/shopsys/pull/3075))

-   see #project-base-diff to update your project

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
-   see #project-base-diff to update your project

#### update Elasticsearch index files structure ([#2567](https://github.com/shopsys/shopsys/pull/2567))

-   elastic index definition files where sorted by order of columns in `ExportRepository` classes. These are not mandatory changes, and you can decide to skip them.
-   add missing properties and remove unnecessary properties from elastic structure
-   changed indent count for json files. These are not mandatory changes, and you can decide to skip them.
-   see #project-base-diff to update your project

#### rename reserved database function `normalize` to non-reserved name `normalized` ([#3072](https://github.com/shopsys/shopsys/pull/3072))

-   create migration to change `normalize()` function to `normalized()` if you had used it in some indexes, functions, or somewhere else
-   don't forget to rename this function in SQLs in repositories, commands, or somewhere else where is used
-   see #project-base-diff to update your project

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
-   see #project-base-diff to update your project

#### restrict limit of requests how many can robot ask from eshop ([#2820](https://github.com/shopsys/shopsys/pull/2820))

-   this change adds migration `Version20230919173422` that adds SEO robot limits to your `robots.txt` file. If you do not want to change it in your project, add it as skipped migration to `migrations-lock.yaml`
-   if you have defined rules for all robots (`User-agent: *`), then you have to manually add these lines to your robots.txt
    ```diff
        User-agent: *
    +   Crawl-delay: 3
    +   Request-rate: 300/1m
    ```
-   see #project-base-diff to update your project

#### apply new coding standards for alphabetical ordering of YAML files ([#2278](https://github.com/shopsys/shopsys/pull/2278))

-   run `php phing yaml-standards-fix` to apply the new coding standards
-   see #project-base-diff to update your project

#### allow open Cypress GUI on Windows with WSL2 ([#3116](https://github.com/shopsys/shopsys/pull/3116))

-   see #project-base-diff to update your project

#### fix friendly URLs ([#3115](https://github.com/shopsys/shopsys/pull/3115))

-   see #project-base-diff to update your project

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
-   see #project-base-diff to update your project

#### fix issues reported by phpstan ([#3134](https://github.com/shopsys/shopsys/pull/3134))

-   see #project-base-diff to update your project

#### addProductToListMutation: ensure new product list is created with non-conflicting uuid ([#3126](https://github.com/shopsys/shopsys/pull/3126))

-   add new tests to `ProductListLoggedCustomerTest` and `ProductListNotLoggedCustomerTest` classes
-   see #project-base-diff to update your project

#### fix builds ([#3131](https://github.com/shopsys/shopsys/pull/3131))

-   see #project-base-diff to update your project

#### create order with preselected delivery address ([#3105](https://github.com/shopsys/shopsys/pull/3105))

-   `Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS` constant was renamed to `VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS_WITHOUT_PRESELECTED`
-   see #project-base-diff to update your project

#### dispatch product stocks export after Setting::TRANSFER_DAYS_BETWEEN_STOCKS is set ([#3104](https://github.com/shopsys/shopsys/pull/3104))

-   `Shopsys\FrameworkBundle\Model\Stock\StockSettingsDataFacade::__construct` interface has changed:
    ```diff
        public function __construct(
            // ...
    +       protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ```

#### remove unused operator info ([#3133](https://github.com/shopsys/shopsys/pull/3133))

-   see #project-base-diff to update your project

#### display error messages in admin if legal conditions articles are not set ([#3128](https://github.com/shopsys/shopsys/pull/3128))

-   `Shopsys\FrameworkBundle\Controller\Admin\DefaultController::__construct` interface has changed:

    ```diff
        public function __construct(
            // ...
    +       protected readonly Domain $domain,
    ```

-   see #project-base-diff to update your project

#### load products iteratively while generating image sitemaps ([#3144](https://github.com/shopsys/shopsys/pull/3144))

-   `Shopsys\FrameworkBundle\Model\Product\ProductRepository` class was changed:
    -   `getAllOfferedProducts()` method was removed, use `getAllOfferedProductsPaginated()` instead
-   see #project-base-diff to update your project

#### rename variable differentDeliveryAddress into isDeliveryAddressDifferentFromBilling ([#3161](https://github.com/shopsys/shopsys/pull/3161))

-   FE API: `OrderDecorator.types.yaml` and `OrderInputDecorator.types.yaml`: differentDeliveryAddress was renamed into more suitable isDeliveryAddressDifferentFromBilling
-   `Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS_WITHOUT_PRESELECTED` constant was renamed to `VALIDATION_GROUP_IS_DELIVERY_ADDRESS_DIFFERENT_FROM_BILLING_WITHOUT_PRESELECTED`
-   see #project-base-diff to update your project

#### include domain sale exclusion in product querying more appropriately ([#3141](https://github.com/shopsys/shopsys/pull/3141))

-   `Shopsys\FrameworkBundle\Model\Product\ProductRepository::__construct` interface has changed:

    ```diff
        public function __construct(
            // ...
    +       protected readonly QueryBuilderExtender $queryBuilderExtender,
    ```

-   see #project-base-diff to update your project

#### add a way to use Blackfire profiling ([#3168](https://github.com/shopsys/shopsys/pull/3168))

-   see #project-base-diff to update your project

#### parameter data fixture refactoring ([#3170](https://github.com/shopsys/shopsys/pull/3170))

-   parameter and product parameter value definition in demo data is now simpler and more readable
-   demo data parameters are now created exclusively in ParameterDataFixture, and it is no longer possible to ad hoc create not existing parameter in ProductDataFixture when defining product parameter values
-   ParameterColorValueDataFixture is added to handle assigning hex values to color parameters
-   see #project-base-diff to update your project

#### refactor the place order process ([#3084](https://github.com/shopsys/shopsys/pull/3084))

-   see the specialized upgrade note in [upgrade-order-processing.md](./upgrade-order-processing.md)
-   see #project-base-diff to update your project

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
-   see #project-base-diff to update your project

#### fix search testing blog article demo data ([#3182](https://github.com/shopsys/shopsys/pull/3182))

-   see #project-base-diff to update your project

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
-   see #project-base-diff to update your project

#### a little spring cleanup ([#3157](https://github.com/shopsys/shopsys/pull/3157))

-   `Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly string $environment = EnvironmentType::PRODUCTION,
        ```
-   `Shopsys\FrameworkBundle\Controller\Admin\ArticleController` class was changed:
    -   `__construct()` method changed its interface:
        ```diff
            public function __construct(
                // ...
        +       protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
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
-   see #project-base-diff to update your project

#### update easy-coding-standard to version 12.2 ([#3192](https://github.com/shopsys/shopsys/pull/3192))

-   update configuration file to new version
-   skip rules are now defined in the separate `ecs-skip-rule.php` file
-   paths to check are now defined directly in the `ecs.php` file
-   fixer `RedundantMarkDownTrailingSpacesFixer` was removed as markdown files are formatted by prettier
-   see #project-base-diff to update your project

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
-   see #project-base-diff to update your project

#### improve OrderSequence related types ([#3206](https://github.com/shopsys/shopsys/pull/3206))

-   class `Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceFactory` was removed
-   interface `Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceFactoryInterface` was removed
-   method `Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository::getNextNumber()` now returns `string`
-   class `Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository` is now strictly typed
-   see #project-base-diff to update your project

#### minor improvements related to deployment ([#3201](https://github.com/shopsys/shopsys/pull/3201))

-   not used database tables `advert_category` and `entity` were removed in migration `Version20240604152553`
-   update `shopsys/deployment` package to a new major version
-   see #project-base-diff to update your project

#### add nominal promo code to demo data ([#3197](https://github.com/shopsys/shopsys/pull/3197))

-   see #project-base-diff to update your project

#### product data fixture refactoring ([#3187](https://github.com/shopsys/shopsys/pull/3187))

-   There was some unnecessary repeating in product data fixtures - it is now simplified in places where it made sense for project base purposes.
-   `ProductDemoDataFactory` class was added as a foundation for creating demo data templates for certain groups of products that has a lot of common data which is not very applicable for project base demo data but could be in real projects.
-   see #project-base-diff to update your project

#### move navigation feature from project-base to the packages ([#3218](https://github.com/shopsys/shopsys/pull/3218))

-   [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework and frontend-api packages:
    -   `NavigationItem` entity and all the related logic
    -   `NavigationQuery`
    -   see #project-base-diff to update your project

#### change free transport limit in demo data ([#3199](https://github.com/shopsys/shopsys/pull/3199))

-   see #project-base-diff to update your project

#### add packeta type transport to demo data ([#3198](https://github.com/shopsys/shopsys/pull/3198))

-   see #project-base-diff to update your project

#### removed ReadyCategorySeoMixDataForForm ([#3214](https://github.com/shopsys/shopsys/pull/3214))

-   see #project-base-diff to update your project

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
    -   after these manual changes run `composer install`, `php phing standards-fix` and `php phing phpstan` commands, which will probably fail on errors caused by incompatibility strict types, if so fix them manually
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

#### SF large files split ([#3081](https://github.com/shopsys/shopsys/pull/3081))

-   icons are now in separate files, so update your imports and do the same with any further icons you might have
-   large GTM files were split, so update your imports and do the same to any other GTM files you might have
-   large helper files were split, so update your imports and do the same to any other helper files you might have
-   large hook files were split, so update your imports and do the same to any other hook files you might have
-   `useQueryParams` hook was split into separate files, so update your code and imports
-   `hooks` and `helpers` folders were unified under a new `utils` name, so update your imports and move your files there

#### codegen types and values differentiation ([#3085](https://github.com/shopsys/shopsys/pull/3085))

-   all codegen-generated GQL types now have a `Type` prefix (suffix did not work as expected)
-   you should update all your imports and make sure to apply the new config
-   you should also regenerate your codegen-generated files to make sure your own files apply the new config

#### cypress make command fix ([#3090](https://github.com/shopsys/shopsys/pull/3090))

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

#### fixed incorrect keys in cache exchange config ([#3094](https://github.com/shopsys/shopsys/pull/3094))

#### Add Developer Styleguide ([#3083](https://github.com/shopsys/shopsys/pull/3083))

During new project implementation phase it is important for a developer who is adjusting new design to be able to see how those changes affect important/base components. This is why we have Styleguide where we have those base components so implementation is faster and developer has better overview of the changes.

#### fixed fix SEO page title, description and heading H1 ([#3108](https://github.com/shopsys/shopsys/pull/3108))

#### SF defer parts of DOM ([#3089](https://github.com/shopsys/shopsys/pull/3089))

-   This PR introduces the possibility of deferring non-critical SF content
-   It also implements it in various places, which you might or might not want to implement as well
-   The best set of steps for you to apply these changes is to read the documentation at `docs/storefront/component-deferring.md` and decide if you want to implement this logic
    -   If you do, then apply it to the already implemented places and use the documentation to pinpoint any other possible places
    -   If you do not, then you can completely omit these changes, as they are not necessary for any future implementation

#### removed duplicated price display on product detail ([#3150](https://github.com/shopsys/shopsys/pull/3150))

#### cookies store smarter init ([#3145](https://github.com/shopsys/shopsys/pull/3145))

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

#### minor improvements to cypress tests ([#3163](https://github.com/shopsys/shopsys/pull/3163))

-   it is now possible to remove pointer events from elements during cypress screenshots, so you should use this if you have problems with failing screenshots because of different hover/active states
-   as always, read the docs regarding our cypress tests to learn more

#### implement new base design + product page ([#3132](https://github.com/shopsys/shopsys/pull/3132))

We want to implement more usable UI design which will be better base for upcoming projects. These changes are about new design for basic stuff like colors and base elements + design for whole product page.

#### rename variable differentDeliveryAddress into isDeliveryAddressDifferentFromBilling ([#3161](https://github.com/shopsys/shopsys/pull/3161))

-   bool variable differentDeliveryAddress was renamed into more suitable isDeliveryAddressDifferentFromBilling across the project
-   see #project-base-diff to update your project

#### simple navigation images are now blacked-out during cypress tests ([#3174](https://github.com/shopsys/shopsys/pull/3174))

-   simple navgation images are now blacked-out during cypress tests
-   make sure you add the blackout everywhere where your snapshots contain simple navigation with images

#### removed invalid cache invalidation when adding to product list ([#3172](https://github.com/shopsys/shopsys/pull/3172))

#### bump versions of SF packages to fix security issues([#3191](https://github.com/shopsys/shopsys/pull/3191))

#### useContext refactoring ([#3176](https://github.com/shopsys/shopsys/pull/3176))

-   move your contexts as high in the component tree as it makes sense (especially with regards to optimizations), in order to maximally avoid unavailable contexts, but to also not kill the performance of you app with unnecessary rerenders
    -   in our case it meant having all context providers in `_app.tsx`, which means less headaches, but might not be feasible for you
    -   we were only able to do this because we use all our contexts globally, so if you have a local context, do not move it to the root, as that will cause unnecessary rerenders
-   we now throw errors if contexts are used outside of providers, which is good if you want to discover bugs as soon as possible, but might not be the right choice for you
    -   if you can and want provide default state instead of throwing errors, you can do that as well
-   we never use `useContext` directly, but provide a wrapper which takes care of extra actions, such as throwing errors if used outside of a provider

#### minor array keys fix ([#3178](https://github.com/shopsys/shopsys/pull/3178))

#### fixed translation on customer's edit profile page ([#3179](https://github.com/shopsys/shopsys/pull/3179))

#### added option to migrate persist store ([#3171](https://github.com/shopsys/shopsys/pull/3171))

-   persist store can now be migrated (read docs in `store-management.md`)
-   all persist store slices should now expose default state as a constant
-   docs regarding store management (`store-management.md`) were improved, so make sure that you implement changes to store based on them
-   remember to update the `DEFAULT_PERSIST_STORE_STATE` constant in your cypress tests to suit the new version of persist store

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

#### fix slider ([#3130](https://github.com/shopsys/shopsys/pull/3130))

On Chrome there was issue when clicking on next/previous button while slider is only partially visible, in this case scroll freeze after scroll onto slider element and is stuck. Fixed by scrolling first onto the slider element and with a little delay continue sliding onto an item in the slider.

#### fix usage of INTERNAL_ENDPOINT env variable ([#3205](https://github.com/shopsys/shopsys/pull/3205))

-   INTERNAL_ENDPOINT env variable is defined with trailing slash, so any usage of it should not include slash at the beginning of the path
-   update your usages in a similar way as in the PR

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

#### Luigi's Box search relations fix ([#3217](https://github.com/shopsys/shopsys/pull/3217))

-   `SearchQuery.graphql` now incldues products search query in order to relate searched categories, brands, and articles to searched products
    -   this is required for Luigi's Box, so if you are not using it for search, then you might skip this change
    -   make sure that `SearchQuery.graphql` includes the same products search query (same fragments, same variables) as `SearchProductsQuery.graphql` in order to utilize URQL cache

#### change free transport limit in demo data ([#3199](https://github.com/shopsys/shopsys/pull/3199))

-   social links in footer were blacked-out for cypress snapshots as they caused issues, so do not forget to add the blackout to snapshots where these links are visible, and also regenerate your screenshots

#### add privacy policy checkbox to contact form ([#3219](https://github.com/shopsys/shopsys/pull/3219))

-   privacy policy checbox is required and needs to be checked in order to submit the contact form
-   the checkbox have replaced the text agreement of the privacy policy by clicking on the submit button

#### restyle orders page ([#3123](https://github.com/shopsys/shopsys/pull/3123))

-   Introduced new CustomerLayout for user section
-   Introduced new user navigation for user section
-   Restyle orders page according to the new design
-   Implement a LinkButton, a link that looks like a button and has props from the Button component, but uses an anchor tag inside

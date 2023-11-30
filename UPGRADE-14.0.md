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
-   start formatting markdown files with Prettier ([#2892](https://github.com/shopsys/shopsys/pull/2892))
    -   see #project-base-diff to update your project
    -   reformat your markdown files by running `php phing standards-fix` in php-fpm container
    -   `standards(-fix)` targets runs newly added `markdown-check/markdown-fix` target, so if you have completely changed the `standards(-fix)` target, remember to add those into your `standards(-fix)` target
-   speed up Product creation in your project ([#2903](https://github.com/shopsys/shopsys/pull/2903))
    -   method `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade::resolveUniquenessOfFriendlyUrlAndFlush()` has been renamed to `resolveUniquenessOfFriendlyUrl` as it no longer flushes
    -   method `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade::refresh()` has changed its visibility to `protected`
    -   method `Shopsys\FrameworkBundle\Model\Product\ProductFacade::refreshProductManualInputPrices()` has been moved to `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade::refreshProductManualInputPrices()` and changed its visibility to `public`
    -   method `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade::__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
            protected readonly ProductManualInputPriceFactoryInterface $productManualInputPriceFactory,
    +       protected readonly PricingGroupRepository $pricingGroupRepository,
        )
    ```
    -   method `Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainFacade::saveGoogleProductDomain()` changed its visibility to `protected`
    -   method `Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainFacade::saveHeurekaProductDomain()` changed its visibility to `protected`
    -   method `Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainFacade::saveHeurekaProductDomain()` changed its visibility to `protected`
    -   method `Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade::saveZboziProductDomain()` changed its visibility to `protected`
    -   see #project-base-diff to update your project
-   add consumers and RabbitMQ to deployed application ([#2904](https://github.com/shopsys/shopsys/pull/2904))
    -   set new environment variables `RABBITMQ_DEFAULT_USER`, `RABBITMQ_DEFAULT_PASS`, `RABBITMQ_IP_WHITELIST` in your deployment tool (with use of the default config it will be Gitlab CI)
    -   see #project-base-diff to update your project
-   re-enable phing target cron ([#2875](https://github.com/shopsys/shopsys/pull/2875)) - see #project-base-diff to update your project
-   prepare core for dispatch/consume system ([#2907](https://github.com/shopsys/shopsys/pull/2907))
    -   your custom classes that utilize internal array caching or need to be reset between message consumption should now implement the `\Symfony\Contracts\Service\ResetInterface` interface
    -   method `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler::cleanScheduleForImmediateRecalculation()` has been renamed to `reset()`
    -   see #project-base-diff to update your project
-   # replace custom application bootstrapping with symfony/runtime ([#2914](https://github.com/shopsys/shopsys/pull/2914))
-   improve repeat order related mutations ([#2876](https://github.com/shopsys/shopsys/pull/2876))
    -   constant `Shopsys\FrameworkBundle\Component\Setting\Setting::ORDER_SENT_PAGE_CONTENT` was removed, use `Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageSettingFacade::ORDER_SENT_PAGE_CONTENT` instead
    -   `Shopsys\FrameworkBundle\Controller\Admin\CustomerCommunicationController`
        -   method `__construct()` changed its interface:
        ```diff
            public function __construct(
        -       protected readonly Setting $setting,
        +       protected readonly OrderContentPageSettingFacade $orderContentPageSettingFacade,
                protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
            )
        ```
        -   method `orderSubmittedAction()` now returns `Symfony\Component\HttpFoundation\Response`
    -   `Shopsys\FrameworkBundle\Model\Order\OrderFacade`
        -   constant `VARIABLE_NUMBER` was removed, use `Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade::VARIABLE_NUMBER` instead
        -   constant `VARIABLE_ORDER_DETAIL_URL` was removed, use `Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade::VARIABLE_ORDER_DETAIL_URL` instead
        -   constant `VARIABLE_PAYMENT_INSTRUCTIONS` was removed, use `Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade::VARIABLE_PAYMENT_INSTRUCTIONS` instead
        -   constant `VARIABLE_TRANSPORT_INSTRUCTIONS` was removed, use `Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade::VARIABLE_TRANSPORT_INSTRUCTIONS` instead
        -   method `getOrderSentPageContent()` was removed, use `Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade::getOrderSentPageContent()` instead
    -   class `Shopsys\FrontendApiBundle\Model\Order\OrderFacade`
        -   was renamed to `Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade`, change your usage accordingly
        -   method `__construct()` changed its interface:
        ```diff
            public function __construct(
                protected readonly OrderRepository $orderRepository,
        +       protected readonly OrderFacade $orderFacade,
            )
        ```
    -   see #project-base-diff to update your project
-   prevent duplicate color parameters in data fixtures ([#2911](https://github.com/shopsys/shopsys/pull/2911))
    -   see #project-base-diff to update your project
-   enable crons to be run at specified times the same as crons ([#2922](https://github.com/shopsys/shopsys/pull/2922))
    -   replace `HourlyFeedCronModule` and `DailyFeedCronModule` with `FeedCronModule` in `config/services/cron.yaml` and set it to be run every time crons are run to ensure that all feeds are generated
    -   `FeedExportCreationDataQueue` has changed, first parameter is now an array of `Shopsys\FrameworkBundle\Model\Feed\FeedModule` instances instead of module names
    -   method `Shopsys\FrameworkBundle\Model\Feed\FeedFacade::__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly FeedRegistry $feedRegistry,
            protected readonly ProductVisibilityFacade $productVisibilityFacade,
            protected readonly FeedExportFactory $feedExportFactory,
            protected readonly FeedPathProvider $feedPathProvider,
            protected readonly FilesystemOperator $filesystem,
    +       protected readonly FeedModuleRepository $feedModuleRepository,
    +       protected readonly EntityManagerInterface $em,
        )
    ```
    -   method `Shopsys\FrameworkBundle\Model\Feed\FeedFacade::getFeedsInfo()` changed its interface:
    ```diff
    -    public function getFeedsInfo(?string $feedType = null): array
    +    public function getFeedsInfo(bool $onlyForCurrentTime = false): array
    ```
    -   method `Shopsys\FrameworkBundle\Model\Feed\FeedFacade::getFeedsNames()` changed its interface:
    ```diff
    -    public function getFeedNames(?string $feedType = null): array
    +    public function getFeedNames(bool $onlyForCurrentTime = false): array
    ```
    -   method `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::__construct()` changed its interface:
    ```diff
        public function __construct(
    -       protected readonly array $knownTypes,
    -       protected readonly string $defaultType,
    +       protected readonly FeedRegistry $feedRegistry,
    +       protected readonly ProductVisibilityFacade $productVisibilityFacade,
    +       protected readonly FeedExportFactory $feedExportFactory,
    +       protected readonly FeedPathProvider $feedPathProvider,
    +       protected readonly FilesystemOperator $filesystem,
    +       protected readonly FeedModuleRepository $feedModuleRepository,
    +       protected readonly EntityManagerInterface $em,
        )
    ```
    -   method `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::registerFeed()` changed its interface:
    ```diff
    -    public function registerFeed(FeedInterface $feed, ?string $type = null): void
    +    public function registerFeed(FeedInterface $feed, string $timeHours, string $timeMinutes, array $domainIds): void
    ```
    -   property `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::$feedsByType` has been replaced with new `$feedConfigsByName` property instead
    -   method `Shopsys\FrameworkBundle\Controller\Admin\FeedController::__construct` changed its interface:
    ```diff
        public function __construct(
            protected readonly FeedFacade $feedFacade,
            protected readonly GridFactory $gridFactory,
            protected readonly Domain $domain,
    +       protected readonly FeedRegistry $feedRegistry,
    +       protected readonly FeedModuleRepository $feedModuleRepository,
        )
    ```
    -   method `Shopsys\FrameworkBundle\Model\Feed\Exception\FeedNotFoundException__construct` changed its interface:
    ```diff
        public function __construct(
            string $name,
    +       ?int $domainId = null,
            ?Exception $previous = null
        )
    ```
    -   method `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::getFeeds()` has been replaced with method `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::getFeedsForCurrentTime()`
    -   method `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::getAllFeeds()` has been replaced with method `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::getAllFeedConfigs()`
    -   method `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::getFeedByName()` has been replaced with method `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::getFeedConfigByName()`
    -   method `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::assertTypeIsKnown()` has been removed without a replacement
-   upgrade Storefront docker image ([#2931](https://github.com/shopsys/shopsys/pull/2931))
    -   now we use Node.js version 20-alpine3.17 and PNPM version 8.10.5
-   fix GrapesJS ([#2911](https://github.com/shopsys/shopsys/pull/2911))
    -   fix styling on FE, fix layout for Text with Image (sides not working), fix iframe sizes, fix video wrapper causing multiple layers in admin, removed Countdown from Blocks
    -   see #project-base-diff to update your project
-   remove custom stores and stocks implementation as it's now a part of shopsys/framework ([#2918](https://github.com/shopsys/shopsys/pull/2918))
    -   if necessary, extend classes and implement your custom logic
    -   see #project-base-diff to update your project
-   add Persoo Category feed and check your feeds ([#2926](https://github.com/shopsys/shopsys/pull/2926))
    -   we have renamed `shopsys.product_feed` tag to `shopsys.feed` to make it more generic so update it in your `services.yaml` if you have extended any of current feeds or implemented your own
    -   see #project-base-diff to update your project
-   move part of data loading logic from project-base into packages ([#2901](https://github.com/shopsys/shopsys/pull/2901))
    -   `Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository::extractHits` visibility has changed from `protected` to `public`
    -   `Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository::extractTotalCount` visibility has changed from `protected` to `public`
    -   see #project-base-diff to update your project
-   implemented generic product lists ([#2901](https://github.com/shopsys/shopsys/pull/2901))
    -   the functionality replaces the original implementations of wishlists a product comparisons
    -   check `Shopsys\FrameworkBundle\Migrations\Version20231102161313`
        -   the migration handles data transfer from `wishlists`, `wishlist_items`, `comparisons`, and `compared_items` tables to the new `product_lists` and `product_list_items` tables
        -   if you had any custom changes in your wishlist/comparison implementations, you might want to skip the migration in `migrations_lock.yaml` file and handle the data transfer yourself
    -   `Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation::__construct()` changed its interface:
    ```diff
        public function __construct(
            // ...
            protected readonly TokenFacade $tokenFacade,
    +       protected readonly ProductListFacade $productListFacade,
    ```
    -   `Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation::__construct()` changed its interface:
    ```diff
        public function __construct(
            // ...
            protected readonly RequestStack $requestStack,
    +       protected readonly ProductListFacade $productListFacade,
    ```
    -   `Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError::CODE` changed its visibility from `protected` to `public`
    -   `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsQuery::__construct()` changed its interface:
    ```diff
        public function __construct(
            // ...
            protected readonly ProductConnectionFactory $productConnectionFactory,
    +       protected readonly DataLoaderInterface $productsVisibleAndSortedByIdsBatchLoader,
    +       protected readonly ProductListFacade $productListFacade,
    ```
    -   see #project-base-diff to update your project
-   remove backend API ([#2937](https://github.com/shopsys/shopsys/pull/2937))
    -   if you used the backend API, you need to implement it by yourself
    -   `Shopsys\FrameworkBundle\Model\Product\ProductFacade::findByProductQueryParams()` method has been removed
    -   `Shopsys\FrameworkBundle\Model\Product\ProductRepository::findByProductQueryParams()` method has been removed
-   annotation fixer: get property type from typehint when the annotation is missing ([#2934](https://github.com/shopsys/shopsys/pull/2934))
    -   run `php phing annotations-fix` in `php-fpm` container to fix the annotations
-   handle image resizing by image proxy ([#2924](https://github.com/shopsys/shopsys/pull/2924))
    -   if you are using VSH CDN, the image proxy is already set up for you in `imageResizer.php` file.
    -   for local development without CDN, the `imageResizer.php` file has a setup for local imgProxy instance
    -   for any other CDN, you can use the `imageResizer.php` file as well, but you have to set up the proxy yourself
    -   instead of defining various image sizes and additional sizes in `images.yaml`, you need to define the image sizes directly in storefront code (use `components/Basic/Image/Image.tsx` component for that purpose)
    -   during the deployment of the new version, the images structure will be migrated automatically
        -   **we highly recommend to back up your images (i.e. `web/content/images/` in your S3 bucket) before the deployment and test the migration on devel/beta stage thoroughly to be sure everything works as expected for your data structure**
        -   all the entity images in all other than `original` folders will be removed, and images from `original` folders will be moved one folder up
        -   check the `docker/nginx/nginx.conf` file - there is a setup for redirecting the legacy image URLs to the new ones. The setup uses a regex that contains all the legacy image sizes. If you use a different image sizes configuration, you need to modify the sizes in the regex accordingly.
            -   the same must be done in `app/orchestration/kubernetes/configmap/nginx-storefront.yaml` file (if you do not have the file in your project yet, you have to create it, see https://github.com/shopsys/deployment#customize-deployment for more information)
        -   see `Shopsys\FrameworkBundle\Command\MigrateImagesCommand` for more details
    -   `Shopsys\FrameworkBundle\Component\Image\AdditionalImageData` class has been removed
    -   the following exceptions were removed from `Shopsys\FrameworkBundle\Component\Image\Config\Exception` namespace:
        -   `DuplicateMediaException`
        -   `DuplicateSizeNameException`
        -   `ImageAdditionalSizeNotFoundException`
        -   `ImageSizeNotFoundException`
        -   `WidthAndHeightMissingException`
        -   `ImageAdditionalSizeConfig`
    -   `Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig` class has been changed:
        -   constants `ORIGINAL_SIZE_NAME` and `DEFAULT_SIZE_NAME` were removed
        -   method `getEntityName()` is now strictly typed
        -   method `getImageSizeConfigByEntity()` was removed
        -   method `getImageSizeConfigByEntityName()` was removed
        -   method `assertImageSizeConfigByEntityNameExists()` was replaced by `assertImageConfigByEntityNameExists()`
        -   method `getImageSizeConfigByImage()` was removed
        -   method `getImageEntityConfig()` is now strictly typed
        -   method `hasImageConfig()` is now strictly typed
        -   method `getEntityConfigByEntityName()` is now strictly typed and its visibility changed to `protected`
        -   method `getAllImageEntityConfigsByClass()` is now strictly typed
        -   method `getImageEntityConfigByClass()` is now strictly typed
    -   `Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigDefinition` class has been changed:
        -   constants `CONFIG_SIZES`, `CONFIG_SIZE_NAME`, `CONFIG_SIZE_WIDTH`, `CONFIG_SIZE_HEIGHT`, `CONFIG_SIZE_CROP`, `CONFIG_SIZE_OCCURRENCE`, `CONFIG_SIZE_ADDITIONAL_SIZES`, and `CONFIG_SIZE_ADDITIONAL_SIZE_MEDIA` were removed
        -   method `createSizesNode()` was removed
    -   `Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader` class has been changed:
        -   method `loadFromYaml()` is now strictly typed
        -   method `loadFromArray()` is now strictly typed
        -   method `processEntityConfig()` is now strictly typed
        -   method `prepareTypes()` is now strictly typed
        -   method `getMultipleByType()` is now strictly typed
        -   method `prepareSizes()` was removed
        -   method `prepareAdditionalSizes()` was removed
    -   `Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig` class has been changed:
        -   `$sizeConfigsByType` and `$sizeConfigs` properties were removed
        -   the constructor was changed:
            ```diff
                public function __construct(
            -       $entityName,
            -       $entityClass,
            -       protected readonly array $sizeConfigsByType,
            -       protected readonly array $sizeConfigs,
            +       protected readonly string $entityName,
            +       protected readonly string $entityClass,
            +       protected readonly array $types,
                    protected readonly array $multipleByType,
            ```
        -   method `getEntityName()` is now strictly typed
        -   method `getEntityClass()` is now strictly typed
        -   method `getTypes()` is now strictly typed
        -   method `isMultiple()` is now strictly typed
        -   method `getSizeConfigs()` was removed
        -   method `getSizeConfigsByTypes()` was removed
        -   method `getSizeConfigsByType()` was removed
        -   method `getSizeConfig()` was removed
        -   method `getSizeConfigByType()` was removed
        -   method `getSizeConfigFromSizeConfigs()` was removed
    -   `Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig` was removed
    -   `Shopsys\FrameworkBundle\Component\Image\DirectoryStructureCreator` class has been changed:
        -   method `makeImageDirectories()` is now strictly typed
        -   method `getTargetDirectoriesFromSizeConfigs()` was removed, replaced by `getTargetDirectoryByType()`
    -   `Shopsys\FrameworkBundle\Component\Image\ImageFacade` class has been changed:
        -   method `getImageUrl()` interface has changed:
            ```diff
                public function getImageUrl(
                    DomainConfig $domainConfig,
                    object $imageOrEntity,
            -       ?string $sizeName = null,
                    ?string $type = null,
            ```
        -   method `getImageUrlFromAttributes()` interface has changed:
            ```diff
                public function getImageUrlFromAttributes(
                    DomainConfig $domainConfig,
                    int $id,
                    string $extension,
                    string $entityName,
                    ?string $type,
            -       ?string $sizeName = null,
            ```
        -   method `getAllImageEntityConfigsByClass()` has been removed
        -   method `getAdditionalImagesData()` has been removed
        -   method `getAdditionalImagesDataFromAttributes()` has been removed
        -   method `getAdditionalImageUrl()` has been removed
    -   ` Shopsys\FrameworkBundle\Component\Image\ImageLocator` class has been changed:
        -   `ADDITIONAL_IMAGE_MASK` constant was removed
        -   `getRelativeImageFilepath()` method has changed its interface:
            ```diff
            -   public function getRelativeImageFilepath(Image $image, $sizeName)
            +   public function getRelativeImageFilepath(Image $image): string
            ```
        -   `getRelativeAdditionalImageFilepath()` method has been removed
        -   `getRelativeImageFilepathFromAttributes()` method has changed its interface:
            ```diff
               public function getRelativeImageFilepathFromAttributes(
                   int $id,
                   string $extension,
                   string $entityName,
                   ?string $type,
            -      ?string $sizeName = null,
            -      ?int $additionalIndex = null,
            ```
        -   `getAbsoluteAdditionalImageFilepath()` method has been removed
        -   `getAbsoluteImageFilepath()` method has changed its interface:
            ```diff
            -   public function getAbsoluteImageFilepath(Image $image, $sizeName)
            +   public function getAbsoluteImageFilepath(Image $image): string
            ```
        -   `getRelativeImagePath()` method has changed its interface:
            ```diff
            -   public function getRelativeImagePath($entityName, $type, $sizeName)
            +   public function getRelativeImagePath(string $entityName, ?string $type): string
            ```
        -   `getAdditionalImageFilename()` method has been removed
    -   `Shopsys\FrameworkBundle\Component\Image\Processing\ImageGenerator` class has been removed
    -   `Shopsys\FrameworkBundle\Component\Image\Processing\ImageGeneratorFacade` class has been removed
    -   `Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor::resizeBySizeConfig()` method has been removed
    -   `Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor::resizeByAdditionalSizeConfig()` method has been removed
    -   `Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor::getSupportedImageExtensions()` method has been removed
    -   `Shopsys\FrameworkBundle\Controller\Admin\ImageController` class has been removed
    -   `admin_image_overview` route has been removed
    -   `images` and `images -> sizes` menu items has been removed from the administration menu
    -   `Cropping`, `Height [px]`, `Image size`, `Occurrence`, `Size name`, `Width [px]`, and `default` translation message IDs has been removed
    -   `framework/src/Resources/views/Admin/Content/Image/overview.html.twig` Twig template has been removed
    -   `imageExists`, `imageUrl`, `noimage`, and `getImages` Twig functions had been removed
    -   `Shopsys\FrameworkBundle\Twig\ImageExtension::imageExists()` method is now strictly typed
    -   `Shopsys\FrameworkBundle\Twig\ImageExtension::getImageUrl()` method changed its visibility to `protected` and changed its interface:
        ```diff
        -   public function getImageUrl($imageOrEntity, $sizeName = null, $type = null)
        +   protected function getImageUrl($imageOrEntity, ?string $type = null): string
        ```
    -   `Shopsys\FrameworkBundle\Twig\ImageExtension::getImages()` method has been removed
    -   `Shopsys\FrameworkBundle\Twig\ImageExtension::getNoimageHtml()` method changed its visibility to `protected` and is now strictly typed
    -   `Shopsys\FrameworkBundle\Twig\ImageExtension::getImageHtmlByEntityName()` has changed its interface:
        ```diff
        -   protected function getImageHtmlByEntityName(array $attributes, $entityName, $additionalImagesData = []): string
        +   protected function getImageHtmlByEntityName(array $attributes, string $entityName): string
        ```
    -   `Shopsys\FrameworkBundle\Twig\ImageExtension::getImageCssClass()` has changed its interface:
        ```diff
        -   protected function getImageCssClass($entityName, $type, $sizeName)
        +   protected function getImageCssClass(string $entityName, ?string $type): string
        ```
    -   `Shopsys\FrontendApiBundle\Model\Resolver\Image\Exception\ImageSizeInvalidUserError` class has been removed
    -   `Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesQuery` class has been changed:
        -   `IMAGE_ENTITY_ADVERT` constant was removed
        -   method `imagesByEntityQuery()` has changed its interface:
        ```diff
        -   public function imagesByEntityQuery(object $entity, ?string $type, ?string $size): array
        +   public function imagesByEntityQuery(object $entity, ?string $type): array
        ```
        -   method `imagesByProductQuery()` has changed its interface:
        ```diff
        -   public function imagesByProductQuery($data, ?string $type, ?string $size): array
        +   public function imagesByProductQuery($data, ?string $type): array
        ```
        -   method `imagesByAdvertQuery()` has changed its interface:
        ```diff
        -   public function imagesByAdvertQuery(Advert $advert, ?string $type, ?string $size): array
        +   public function imagesByAdvertQuery(Advert $advert, ?string $type): array
        ```
        -   method `resolveByEntityId()` has changed its interface:
        ```diff
        -   protected function resolveByEntityId(int $entityId, string $entityName, ?string $type, ?string $size): array
        +   protected function resolveByEntityId(int $entityId, string $entityName, ?string $type): array
        ```
        -   method `getResolvedImages()` has changed its interface:
        ```diff
        -   protected function getResolvedImages(array $images, array $sizeConfigs): array
        +   protected function getResolvedImages(array $images): array
        ```
        -   method `getResolvedImage()` has changed its interface:
        ```diff
        -   protected function getResolvedImage(Image $image, ImageSizeConfig $sizeConfig): array
        +   protected function getResolvedImage(Image $image): array
        ```
        -   method `getSizeConfigs()` has been removed
        -   method `getSizeConfigsForAdvert()` has been removed
    -   `Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade::getImagesUrlsIndexedByProductId()` method has changed its interface:
        ```diff
        -    public function getImagesUrlsIndexedByProductId(array $products, DomainConfig $domainConfig, $sizeName = null)
        +    public function getImagesUrlsIndexedByProductId(array $products, DomainConfig $domainConfig): array
        ```
    -   FE API image queries no longer accept `size` argument
        -   i.e. `AdvertImageDecorator.images`, `BrandDecorator.images`, `CategoryDecorator.images`, `PaymentDecorator.images`, `ProductDecorator.images`, and `TransportDecorator.images`
    -   the following fields has been removed from `ImageDecorator.types`:
        -   `type`, `position`, `size`, `width`, and `height`
    -   image lazy loading via `image` Twig function is not supported anymore:
        -   `Shopsys\FrameworkBundle\Twig\ImageExtension` class has been changed:
            -   `PLACEHOLDER_FILENAME` and `BROWSERS_WITHOUT_NATIVE_LAZY_LOAD` constants were removed
            -   `$browser` property was removed
            -   `$isLazyLoadEnabled` constructor parameter was removed
            -   `getImagePlaceholder()`, `isLazyLoadEnabled()`, `makeHtmlAttributesLazyLoaded()`, and `isNativeLazyLoadSupported()` methods were removed
        -   `shopsys.image.enable_lazy_load` container parameter was removed
    -   see #project-base-diff to update your project
-   remove usage of shopsys/read-model package ([#2935](https://github.com/shopsys/shopsys/pull/2935))
    -   `Shopsys\FrameworkBundle\Component\Image\ImageFacade` class has been changed:
        -   method `getImagesByEntityIdAndNameIndexedById()` was removed
        -   method `getImageUrlFromAttributes()` was removed
        -   method `getImagesByEntitiesIndexedByEntityId()` was removed
        -   method `getImagesByEntityId()` was removed
    -   `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface`, `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade` classes have been changed:
        -   method `getAccessoriesForProduct()` was removed
        -   method `getVariantsForProduct()` was removed
        -   method `getPaginatedProductsInCategory()` was removed
        -   method `getPaginatedProductsForBrand()` was removed
        -   method `getPaginatedProductsForSearch()` was removed
        -   method `getSearchAutocompleteProducts()` was removed
        -   method `getProductFilterCountDataInCategory()` changed its interface:
        ```diff
            public function getProductFilterCountDataInCategory(
                int $categoryId,
                ProductFilterConfig $productFilterConfig,
                ProductFilterData $productFilterData,
        +       string $searchText = '',
            ): ProductFilterCountData
        ```
    -   class `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade` was removed, use `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade` instead
    -   `Shopsys\FrameworkBundle\Model\Product\ProductRepository`
        -   method `getPaginationResultForListableInCategory()` was removed
        -   method `getAllListableTranslatedAndOrderedQueryBuilder()` was removed
        -   method `getAllListableTranslatedAndOrderedQueryBuilderByCategory()` was removed
        -   method `getPaginationResultForListableForBrand()` was removed
        -   method `getFilteredListableInCategoryQueryBuilder()` was removed
        -   method `getPaginationResultForSearchListable()` was removed
        -   method `getFilteredListableForSearchQueryBuilder()` was removed
        -   method `applyOrdering()` was removed
        -   method `__construct` changed its interface:
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
        -       protected readonly ProductFilterRepository $productFilterRepository,
        -       protected readonly QueryBuilderExtender $queryBuilderExtender,
        -       protected readonly Localization $localization,
                protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
            )
        ```
    -   see #project-base-diff to update your project
-   remove preorder and vendor delivery date features ([#2942](https://github.com/shopsys/shopsys/pull/2942))
    -   see #project-base-diff to update your project
-   add Persoo Product feed and remove unused code ([#2939](https://github.com/shopsys/shopsys/pull/2939))
    -   product plan and assembly instructions have been removed from project-base, see diff to update your project
    -   see #project-base-diff to update your project

### Storefront

-   add rounded price value to order process ([#2835](https://github.com/shopsys/shopsys/pull/2835))
-   remove unnecessary default value for domain config in zustand ([#2888](https://github.com/shopsys/shopsys/pull/2888))
    -   you probably do not need the default value as well, as we set it right at the beginning of page load
-   fixed undefined window error ([#2882](https://github.com/shopsys/shopsys/pull/2882))
    -   in one of the previous PR's, the `canUseDom` function was removed, but it caused the application to fail in some cases
    -   because of that, the function was brought back as a constant (as `isClient`) and some checks were reinstantiated
    -   in your code, you should pay attention to where you are using the window object and make sure it is available (either by checking the logic or by explicitly wrapping it in a check)
    -   also keep in mind that `canUseDom` is now `isClient` and `isServer` is now used as `!isClient`
-   add Prettier plugin and ESlint plugins and rules ([#2874](https://github.com/shopsys/shopsys/pull/2874))
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
-   improve product lists in GrapesJS ([#2879](https://github.com/shopsys/shopsys/pull/2879))
-   add instant page skeleton after link click ([#2863](https://github.com/shopsys/shopsys/pull/2863))
    -   before page must first load and then skeleton was shown, now we pass page type to `ExtendedLink` component which allow us to display immediately after user click on the link proper skeleton for the required page
    -   some reorganization and renaming was done to Skeletons, we basically have only two types of skeletons, for pages and modules, since it is sometimes difficult to recognise which one is which, we have added Page word, but this was not perfect in folder organization, that's why it's been added word Module as well, to organize skeletons better way
    -   added missing skeletons for Homepage and Stores
    -   adjustments to current skeletons to match the design of a page better
-   refactoring of various error-related matters on SF ([#2871](https://github.com/shopsys/shopsys/pull/2871))
    -   the goal was to shine light on some of the not-well-understood places in regard of error handling on SF
    -   for you to get the most out of this PR, you should check `error-handling.md` in SF docs, which is a direct result of this PR
    -   it contains explanations and tips on how to improve error handling in your SF code as well
-   refactor `ProductVariantsTable` ([#2899](https://github.com/shopsys/shopsys/pull/2899))
    -   `ProductVariantsTable` component was made with table element but on smaller screens it was styled more like list. This was causing styling difficulties. That's why it has been replaced with grid with combination of flexbox.
    -   components `ProductVariantsTableRow` and `Variant` were removed
    -   component `ProductVariantsTable` was renamed to `ProductDetailVariantsTable` so it matches parent folder where it's placed
-   add equal spacing to the Category page ([#2900](https://github.com/shopsys/shopsys/pull/2900))
-   auth (loading) improvements ([#2897](https://github.com/shopsys/shopsys/pull/2897))

    -   `window.location.href` assignments were replaced with `router` operations because of unexpected behavior, where the current URL (thus also properties of the router) changed even before the transition, which caused useEffects and some other conditions to re-run and potentially fails
    -   if you manipulate `window.location` in your code, you should remove it and use `router` instead
    -   loading states for all auth operations (login, logout, registration) are now implemented, so if you have some custom states, you should also handle these
    -   optimistic display of skeletons was implemented for `logout-loading` and both `registration-loading...`states

        -   this was possible as we know that the user is going to be redirected to homepage
        -   however, since we do not know the page type of the redirect destination after login, we do not display any skeleton there
        -   if you always know the destination of redirect after login, chances are you can improve the UX by implementing the following changes:

        ```tsx
        // inside useAuth.tsx login
        updateAuthLoadingState(
            loginResult.data.Login.showCartMergeInfo ? 'login-loading-with-cart-modifications' : 'login-loading',
        );

        // add this line to start showing the skeleton before the redirect
        updatePageLoadingState({
            isPageLoading: true,
            redirectPageType: 'my-page-type',
        });

        if (rewriteUrl) {
            router.replace(rewriteUrl).then(() => router.reload());
        } else {
            router.reload();
        }
        ```

-   added USPs on product detail page ([#2887](https://github.com/shopsys/shopsys/pull/2887))
-   fix sizes of product actions buttons ([#2896](https://github.com/shopsys/shopsys/pull/2896))
    -   now we have unified sizes of add to cart buttons
-   fix Comparison for not logged in users ([#2905](https://github.com/shopsys/shopsys/pull/2905))
    -   unified code for Comparison and Wishlist
    -   refactored Zustand store to use only one store (User Store) for all cartUuid, wishlistUuid and comparisonUuid
-   search on search page is now not called if search query is empty ([#2895](https://github.com/shopsys/shopsys/pull/2895))
-   fix set default delivery address country ([#2902](https://github.com/shopsys/shopsys/pull/2902))
-   fix router server access error on PageGuard ([#2909](https://github.com/shopsys/shopsys/pull/2909))
-   fix Cart list unit text ([#2910](https://github.com/shopsys/shopsys/pull/2910))
-   remove `Heading` component ([#2894](https://github.com/shopsys/shopsys/pull/2894))
    -   it was decided by FE team that this component is not beneficial
    -   it was replaced with general H tags and styles were put into globals.css, styles were also included with new classes (`h1`, `h2`, `h3`, `h4`) which we can use to style text which suppose to look like heading but it is not important enough for mark with H tag
    -   also from those headings and heading classes was remove margin bottom since spacing should be set in exact place where this component is used, not everywhere
-   remove error logging for when gtmSafePush is called outside of the client ([#2920](https://github.com/shopsys/shopsys/pull/2920))
    -   logging this type of error brought no business value, thus it was removed
    -   if you want to treat this ignorable event in a more strict way, you might want to keep the logging, but then you have to improve its behavior yourself
-   added logic for ordering GTM events ([#2921](https://github.com/shopsys/shopsys/pull/2921))
    -   added a GTM context provider for synchronizing events
    -   if you have any logic for syncing your events, you can move it to this provider
    -   docs were added, so you can base your changes on those
-   improve SEO categories logic in regards to non-SEO-sensitive filters ([#2891](https://github.com/shopsys/shopsys/pull/2891))
    -   we moved some config from various files to `config/constants` and warmly suggest you do the same, as it improves the app and testing
    -   we also moved some hooks from `helpers` to `hooks` and suggest you do the same with all hooks, as it again improves the app and testing
    -   the implemented functionality for dynamic switching between SEO-sensitivity for various filters is only implemented on SF, so applying these changes won't make it work on BE
    -   if you do not need SEO categories, these changes might be irrelevant altogether
    -   flag and brand values are now merged after change of default parameters (leaving SEO category) instead of overwritting. This is a bug fix and you should apply it to your changes as well.
-   category data fetching logic improvements ([#2893](https://github.com/shopsys/shopsys/pull/2893))
    -   these changes primarily focus on fixing loading and fetching logic on the category page
    -   there were certain bugs with double loads, skeleton glitches, etc. but they were mostly caused by the added complexity of SEO categories, so if you do not have those and your fetching logic is thus much simpler, you probably do not need most of these changes
    -   the category detail fetching was also rewritten to the generated URQL hook, which can be beneficial for you if your logic allows you to do so
    -   one thing that you should definitely consider is removal of `onRouteChangeError` from the page loading logic, as the previous implementation was rather invalid. See commit message for more details
-   added additional skeletons, sync store across browser tabs, use BroadcastChannel to refresh cart after Add/Remove to/from cart, ExtendedNextLink was refactored, remove EmptyCartWrapper ([#2906](https://github.com/shopsys/shopsys/pull/2906))

    -   added skeletons for Wishlist, Comparison and My orders, Order detail and Product Main Variant page
    -   fix Add to cart/wishlist/comparison feature when using multiple tabs

        -   Before this change if I open multiple tabs as fresh unlogged user without `cartUuid`, then in each tab I add product to cart, I am experiencing that each tab contains different products in cart, even after refresh. Same applies to `Wishlist` and `Comparison`. This is solved now by adding `BroadcastChannel` middleware to Zustand store, so now it uses always only first returned `uuid`.
        -   removed redirect to homepage after logout, this was not good approach
        -   `removeItemFromCartAction ` in `useRemoveProductFromCart` is now `void` type (doesn't return `CartFragmentApi`) and in case of error is returned from API it refect cart so user gets latest cart updates
        -   Zustand package is upgraded to latest version
        -   We are now manually hydrating the Zustand store during the initial page load. This means that we no longer need to resolve mismatches between the UI rendered by the server and the UI managed on the client side. Code from this example may look familiar to you, this is no longer needed:

        ```tsx
        const [isFetchingPaused, setIsFetchingPaused] = useState(true);

        const [{ data: comparisonData, fetching }] = useComparisonQueryApi({
            variables: { comparisonUuid },
            pause: isFetchingPaused,
            pause: !comparisonUuid && !isUserLoggedIn,
        });

        useEffect(() => {
            setIsFetchingPaused(!comparisonUuid && !isUserLoggedIn);
        }, [comparisonUuid]);
        ```

    -   fix Add to cart/wishlist/comparison feature when using multiple tabs
        -   As fresh unlogged user without `cartUuid` I open multiple tabs, then in each tab I add product to cart, I am experiencing that each tab contains different products in cart, even after refresh. Same applies to `Wishlist` and `Comparison`. This was solved by adding `BroadcastChannel` middleware to Zustand store, so now it uses always only first returned `uuid`.
        -   Added useBroadcastChannel hook, this allow us to use Broadcast Channel for better handling multitab behavior. Together with the first type Auth for handling Authentication. Now when user log in/out, other tabs are reloaded.
    -   ExtendedNextLink was refactored
        -   remove static type from your links
        -   if you have custom types, we moved it from STATIC_PAGES to CUSTOM_PAGES
    -   use BroadcastChannel to refresh cart
        -   whereever you use modifications for cart there should be also implemented the same behavior
        -   Removed isCartEmpty value from useCurrentCart. This was causing unnecessary checks for cart value after check if isCartEmpty, because Typescript was not able to recognise that cart value is already checked. For the same reason some handling cases were more difficult to write and understand. Also check for cart exist (!cartUuid && !isUserLoggedIn) was needed basically everywhere where cart values were using, this was added to this hook and now cart value is consistent
    -   remove EmptyCartWrapper
        -   this component was making whole order process very difficult to predict behavior, now we have the logic from this component splitted into each page of the order process which makes it much more predictable
        -   also this fixes bug with infinite cart page loading

-   improve repeat order related mutations ([#2876](https://github.com/shopsys/shopsys/pull/2876))
    -   the maximum number of payment transaction is set to 2 (using `MAX_ALLOWED_PAYMENT_TRANSACTIONS`), this means that the user can repeat payment only once, so if this does not match your requirements, you need to change it
    -   GoPay SWIFT selection was moved directly inside payment selection, which might not be visible right away if you use the same approach as we do (hide unselected transports and payments), however if you do not do that, this new layout might be suitable for you
    -   RegistrationAfterOrder now takes care of its own conditional rendering, so if you want to add a condition, move it inside the component
    -   PaymentConfirmationElements and PaymentConfirmationContent were removed and instead a new ConfirmationPageContent is used, which is common for all confirmation pages. If this does not suit your needs (e.g. the page needs to look completely different in each case), you probably want to either modify the new component or use multiple different ones.
    -   logic for PaymentFail and PaymentSuccess was reduced and is now either inside the usePaymentConfirmationContent hook, which is responsible for fetching the content from the API, or inside ConfirmationPageContent, which is responsible for structuring the content. If you do not want to load the page content from API, these changes might not be necessary for you, and you can simplify the logic by having static components, instead of dynamic ones.
    -   two new queries for payment confirmation were added on SF. If you have previously implemented the logic for order confirmation page dynamic content, you can follow that example and implement it similarly.
    -   query for order confirmation was renamed (the word 'Query' was added)
    -   expiration of confirmation content is now taken into account on SF by ignoring the API error and not rendering the content. This was a bug (missing feature) in previous versions. If you have not fixed it yourself before, you definitely want to implement these changes, because otherwise, the user can see errors when displaying the order confirmation page after a certain timeout.
    -   query parameters parsed using getStringFromUrlQuery are now also trimmed, which makes sure the API can understand the string. For example, the API does not understand " 440ecde4-b992-4290-8636-4f454c9cf475 " as a valid UUID.
-   display Transport and Payment description on desktop ([#2930](https://github.com/shopsys/shopsys/pull/2930))
-   implemented generic product lists ([#2901](https://github.com/shopsys/shopsys/pull/2901))

    -   previous implementation for wishlist and comparison was removed, so if you want to keep them, you have to re-implement it according to the new requirements (use of generic API queries and mutations)
    -   this opens door for multiple lists of any kind, so if you want multiple wishlists or other types of lists, you can do that, just don't forget to

        -   use the generic queries, mutations, and fragments (`AddProductToListMutation`, `RemoveProductFromListMutation`, `RemoveProductListMutation`, `ProductListQuery`, `ProductListFragment`, and `ProductInProductListFragment`)

        -   create a wrapper hook (such as `useWishlist`) which uses `useProductList` and provides the product list type and necessary callbacks

    -   keep in mind that the implementation heavily depends on the graphcache in `cacheExchange.ts`. This cache is responsible for manually updating queries, which decreases the number of requests, but also makes the entire functionality to behave as expected. If you use the generic mutations, queries, and fragments, you do not need to touch it, as it should work out of the box. However, there is a chance that your implementation will be more customized and not directly follow the provided one, for example by having a custom query for your list. In such case, if implementing any other list in a more custom manner, follow the guidelines in the docs (`graphcache.md`).
    -   we also renamed `productsCompare` to `comparedProducts` across the files as it is a more suitable and better name.

-   package.json fix to minors ([#2923](https://github.com/shopsys/shopsys/pull/2923))

    -   all SF packages were updated to the highest possible patch within the current minor version

-   implement NextImage component ([#2924](https://github.com/shopsys/shopsys/pull/2924))
    -   Now we don't use `srcset` solution anymore for which was our `Image` component prepared. It was replaced with the solution provided by Next.js which is `NextImage` component. This component allows us many more possibilities. But it comes with a price. Since `NextImage` is capable of different image rendering methods we need to adjust each image to the chosen method. For this there is no specific advice, but we have 2 methods:
        -   Each `Image` has specific dimensions `width` and `height`, here we need to usually adjust `w-auto` or `max-h-full` so it is displayed properly. All depends on project and specific place.
        -   `Image` component has `fill` prop, no need to specify `width` or `height` props. But it needs some wrapper which has specified dimensions to limit the image. Then you can use different `object-fit` CSS properties.
        -   check all places where you use the `Image` component and modify them accordingly as the component interface has changed
        -   in your `next.config.js`, add all your domains names into `images -> remotePatterns` setting

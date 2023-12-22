# Upgrade Product Recalculations

## General changes that have to be known

This article covers the product recalculations, which means recalculation of the product visibility, selling denial, and export to Elasticsearch.

The previous implementation was not unified and, therefore, may sometimes be tricky. For example, visibility had to be calculated before exporting to Elasticsearch; otherwise, the product may be exported with the wrong visibility.

The whole functionality of the product recalculations was changed and unified.
The main goal was to make the product recalculations as simple as possible and to make the code more readable and maintainable.
For that reason, several schedulers, listeners, and custom implementations were removed, and the recalculations are now done only by dispatching a message for the message broker and async, handler then immediately recalculates the product and exports it to Elasticsearch.

It's no longer necessary to think about the context of the executed code – calculations were handled differently in the console, cron, and request.
At any place where a product is somehow changed, only, and the ONLY one of the `ProductRecalculationDispatcher::dispatch\*() method has to be called.
The dispatcher and the asynchronous queue take care of the rest.

This allows, among other benefits, also to horizontally scale the recalculations.
For a larger catalog, several consumers may be run to handle the recalculations and drastically reduce the time necessary to present the changes of products on the Storefront.

Because variants are tightly coupled with the main variant, the recalculations have to be always done for the whole group (variants + main variant).
But it's no longer necessary to take this into account in a custom code.
When, for example, only a single variant is changed, it's enough to dispatch a message for this variant – recalculation will be done automatically for the whole group to be sure all products are in a proper state.

Codebase and some concepts were simplified, most importantly, by removing the calculated fields from the product entity.
The following fields were removed:
`Product::$calculatedHidden`
`Product::$calculatedAvailability`
`Product::$recalculateAvailability`
`Product::$calculatedVisibility`
`Product::$recalculateVisibility`
`Product::$recalculatePrice`
`Product::$exportProduct`
The entity `ProductCalculatedPrice` was removed completely.

This lowers the size of the changes flushed to the database and helps avoid deadlock situations when recalculating the product.

In certain situations (for example, when a pricing group is created), it's necessary to recalculate all products.
This is done by dispatching a message "dispatch all" to the message broker.
The async handler then takes care of dispatching all product IDs to recalculation.
That way, we may dispatch all products to recalculation during request without worrying about the size of the catalog.

Cron modules responsible for recalculations or export to Elasticsearch were removed and replaced by a single cron module dispatching all products to recalculation each day at midnight.
This is a safety net to make sure all products are recalculated at least once a day.

The new command `shopsys:dispatch:recalculations` allows manual dispatch message(s) for recalculation.

In tests, the real queue is not used – instead, where necessary (when testing product changes), the `Tests\App\Test\WebTestCase::handleDispatchedRecalculationMessages()` method should be called to run scheduled recalculations.
That way we are sure the code works in real life – the message is truly dispatched, thus the product is recalculated – everything with the same code used in real life.

Calling this method also creates a snapshot in Elasticsearch before any changes are exported and restores it afterward.
Tests no longer depend on the changes made or the order of run tests.

## BC Breaking changes

-   class `Shopsys\FrameworkBundle\Form\Admin\Stock\StockProductFormType` has been deleted
-   class `Shopsys\FrameworkBundle\Component\Messenger\ExampleMessage` has been deleted
-   class `Shopsys\FrameworkBundle\Component\Messenger\ExampleDispatcher` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\ProductVisibilityMidnightCronModule` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculator` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCronModule` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountRepository` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterRepository` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterRepository` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\ProductVisibilityImmediateCronModule` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPriceFactoryInterface` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPriceRepository` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCronModule` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPriceFactory` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\MarkProductForExportSubscriber` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportCronModule` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportChangedCronModule` has been deleted
-   class `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportSubscriber` has been deleted
-   class `Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductAvailabilityFilter` has been deleted
-   `Shopsys\FrameworkBundle\Command\RecalculationsCommand`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            private readonly CategoryVisibilityRepository $categoryVisibilityRepository,
    -       private readonly ProductHiddenRecalculator $productHiddenRecalculator,
    -       private readonly ProductPriceRecalculator $productPriceRecalculator,
            private readonly ProductVisibilityFacade $productVisibilityFacade,
    -       private readonly ProductAvailabilityRecalculator $productAvailabilityRecalculator,
            private readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
        )
    ```
-   `Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher`:
    -   method `__construct()` has been deleted
-   `Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            ProductCatnumFilter $productCatnumFilter,
            ProductNameFilter $productNameFilter,
            ProductPartnoFilter $productPartnoFilter,
            ProductStockFilter $productStockFilter,
            ProductFlagFilter $productFlagFilter,
            ProductCalculatedSellingDeniedFilter $productCalculatedSellingDeniedFilter,
    -       ProductAvailabilityFilter $productAvailabilityFilter,
            ProductBrandFilter $productBrandFilter,
            ProductCategoryFilter $productCategoryFilter,
        )
    ```
-   `Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
    -       protected readonly ProductVisibilityRepository $productVisibilityRepository,
    +       protected readonly ProductVisibilityFacade $productVisibilityFacade,
            protected readonly Domain $domain,
        )
    ```
    -   property `$productVisibilityRepository` has been deleted
-   `Shopsys\FrameworkBundle\Model\Category\CategoryFacade`:
    -   method `__construct()` changed its interface:
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
    +       protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    +       protected readonly EventDispatcherInterface $eventDispatcher,
        )
    ```
-   `Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler`:
    -   method `scheduleRecalculationWithoutDependencies()` has been deleted
-   `Shopsys\FrameworkBundle\Model\Feed\FeedFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly FeedRegistry $feedRegistry,
    -       protected readonly ProductVisibilityFacade $productVisibilityFacade,
            protected readonly FeedExportFactory $feedExportFactory,
            protected readonly FeedPathProvider $feedPathProvider,
            protected readonly FilesystemOperator $filesystem,
            protected readonly FeedModuleRepository $feedModuleRepository,
            protected readonly EntityManagerInterface $em,
        )
    ```
    -   property `$productVisibilityFacade` has been deleted
-   `Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
    -       protected readonly ProductHiddenRecalculator $productHiddenRecalculator,
    -       protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
    -       protected readonly ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
    -       protected readonly ProductVisibilityFacade $productVisibilityFacade,
            protected readonly ModuleFacade $moduleFacade,
            protected readonly ProductRepository $productRepository,
    +       protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        )
    ```
    -   property `$productHiddenRecalculator` has been deleted
    -   property `$productSellingDeniedRecalculator` has been deleted
    -   property `$productAvailabilityRecalculationScheduler` has been deleted
    -   property `$productVisibilityFacade` has been deleted
-   `Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly CurrencyRepository $currencyRepository,
            protected readonly PricingSetting $pricingSetting,
            protected readonly OrderRepository $orderRepository,
            protected readonly Domain $domain,
    -       protected readonly ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
            protected readonly CurrencyFactoryInterface $currencyFactory,
    +       protected readonly EventDispatcherInterface $eventDispatcher,
        )
    ```
    -   property `$productPriceRecalculationScheduler` has been deleted
-   `Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly PricingGroupRepository $pricingGroupRepository,
            protected readonly Domain $domain,
    -       protected readonly ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
            protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    -       protected readonly ProductVisibilityRepository $productVisibilityRepository,
    -       protected readonly ProductCalculatedPriceRepository $productCalculatedPriceRepository,
    +       protected readonly ProductVisibilityFacade $productVisibilityFacade,
            protected readonly CustomerUserRepository $customerUserRepository,
            protected readonly PricingGroupFactoryInterface $pricingGroupFactory,
            protected readonly EventDispatcherInterface $eventDispatcher,
        )
    ```
    -   property `$productPriceRecalculationScheduler` has been deleted
    -   property `$productVisibilityRepository` has been deleted
    -   property `$productCalculatedPriceRepository` has been deleted
-   `Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly VatRepository $vatRepository,
            protected readonly Setting $setting,
    -       protected readonly ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
            protected readonly VatFactoryInterface $vatFactory,
            protected readonly Domain $domain,
    +       protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        )
    ```
    -   property `$productPriceRecalculationScheduler` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly AvailabilityRepository $availabilityRepository,
            protected readonly Setting $setting,
    -       protected readonly ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
            protected readonly AvailabilityFactoryInterface $availabilityFactory,
            protected readonly EventDispatcherInterface $eventDispatcher,
        )
    ```
    -   property `$productAvailabilityRecalculationScheduler` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation`:
    -   property `$productSellingDeniedRecalculator` has been deleted
    -   property `$productVisibilityFacade` has been deleted
    -   property `$em` has been deleted
    -   property `$productRepository` has been deleted
    -   method `calculateAvailabilityForUsingStockProduct()` has been deleted
    -   method `calculateMainVariantAvailability()` has been deleted
    -   method `getAtLeastSomewhereSellableVariantsByMainVariant()` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
    -       EntityManagerInterface $entityManager
    +       protected readonly EntityManagerInterface $em,
    +       protected readonly Domain $domain,
        )
    ```
-   `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly ParameterRepository $parameterRepository,
            protected readonly ProductFacade $productFacade,
            protected readonly FriendlyUrlRepository $friendlyUrlRepository,
    -       protected readonly ProductVisibilityRepository $productVisibilityRepository,
    +       protected readonly ProductVisibilityFacade $productVisibilityFacade,
            protected readonly FriendlyUrlFacade $friendlyUrlFacade,
            protected readonly CategoryFacade $categoryFacade,
            protected readonly ProductAccessoryFacade $productAccessoryFacade,
            protected readonly BrandCachedFacade $brandCachedFacade,
    +       protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        )
    ```
    -   property `$productVisibilityRepository` has been deleted
    -   method `getProductIdsForChanged()` has been deleted
    -   method `getProductChangedCount()` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex`:
    -   method `getChangedCount()` has been deleted
    -   method `getChangedIdsForBatch()` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly ParameterFilterChoiceRepository $parameterFilterChoiceRepository,
            protected readonly FlagFilterChoiceRepository $flagFilterChoiceRepository,
            protected readonly CurrentCustomerUser $currentCustomerUser,
            protected readonly BrandFilterChoiceRepository $brandFilterChoiceRepository,
            protected readonly PriceRangeRepository $priceRangeRepository,
    +       protected readonly ProductFilterElasticFacade $productFilterElasticFacade,
    +       protected readonly ParameterFacade $parameterFacade,
    +       protected readonly FlagFacade $flagFacade,
    +       protected readonly BrandFacade $brandFacade,
        )
    ```
    -   method `createForCategory` changed its interface:
    ```diff
        public function createForCategory(
    -       $domainId,
    -       $locale,
    +       string $locale
            Category $category
    +       string $searchText,
        )
    ```
-   `Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly ProductMassActionRepository $productMassActionRepository,
    -       protected readonly ProductVisibilityFacade $productVisibilityFacade,
    -       protected readonly ProductHiddenRecalculator $productHiddenRecalculator,
    +       protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        )
    ```
    -   property `$productVisibilityFacade` has been deleted
    -   property `$productHiddenRecalculator` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly ParameterRepository $parameterRepository,
            protected readonly ParameterFactoryInterface $parameterFactory,
            protected readonly EventDispatcherInterface $eventDispatcher,
    +       protected readonly CategoryParameterRepository $categoryParameterRepository,
        )
    ```
-   `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly PricingSetting $pricingSetting,
            protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
            protected readonly ProductRepository $productRepository,
            protected readonly ProductInputPriceRecalculator $productInputPriceRecalculator,
    +       protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        )
    ```
-   `Shopsys\FrameworkBundle\Model\Product\Product`:
    -   property `$calculatedHidden` has been deleted
    -   property `$recalculateAvailability` has been deleted
    -   property `$calculatedVisibility` has been deleted
    -   property `$recalculatePrice` has been deleted
    -   property `$recalculateVisibility` has been deleted
    -   property `$exportProduct` has been deleted
    -   property `$calculatedAvailability` has been deleted
    -   method `getCalculatedHidden()` has been deleted
    -   method `setCalculatedAvailability()` has been deleted
    -   method `getCalculatedVisibility()` has been deleted
    -   method `isVisible()` has been deleted
    -   method `markPriceAsRecalculated()` has been deleted
    -   method `setRecalculateVisibility()` has been deleted
    -   method `markForVisibilityRecalculation()` has been deleted
    -   method `markForAvailabilityRecalculation()` has been deleted
    -   method `markForExport()` has been deleted
    -   method `isMarkedForVisibilityRecalculation()` has been deleted
    -   method `getCalculatedAvailability()` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\ProductDataFactory`:
    -   method `__construct()` changed its interface:
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
            protected readonly AvailabilityFacade $availabilityFacade,
            protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    +       protected readonly ProductStockFacade $productStockFacade,
    +       protected readonly StockFacade $stockFacade,
    +       protected readonly ProductStockDataFactory $productStockDataFactory,
        )
    ```
-   `Shopsys\FrameworkBundle\Model\Product\ProductFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly ProductRepository $productRepository,
            protected readonly ProductVisibilityFacade $productVisibilityFacade,
            protected readonly ParameterRepository $parameterRepository,
            protected readonly Domain $domain,
            protected readonly ImageFacade $imageFacade,
    -       protected readonly ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
            protected readonly PricingGroupRepository $pricingGroupRepository,
            protected readonly ProductManualInputPriceFacade $productManualInputPriceFacade,
    -       protected readonly ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
            protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    -       protected readonly ProductHiddenRecalculator $productHiddenRecalculator,
    -       protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
            protected readonly ProductAccessoryRepository $productAccessoryRepository,
            protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
            protected readonly ProductFactoryInterface $productFactory,
            protected readonly ProductAccessoryFactoryInterface $productAccessoryFactory,
            protected readonly ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
            protected readonly ProductParameterValueFactoryInterface $productParameterValueFactory,
            protected readonly ProductVisibilityFactoryInterface $productVisibilityFactory,
            protected readonly ProductPriceCalculation $productPriceCalculation,
    -       protected readonly ProductExportScheduler $productExportScheduler,
    +       protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    +       protected readonly ProductStockFacade $productStockFacade,
    +       protected readonly StockFacade $stockFacade,
        )
    ```
    -   property `$productPriceRecalculationScheduler` has been deleted
    -   property `$productAvailabilityRecalculationScheduler` has been deleted
    -   property `$productHiddenRecalculator` has been deleted
    -   property `$productSellingDeniedRecalculator` has been deleted
    -   property `$productExportScheduler` has been deleted
    -   method `markProductsForExport()` has been deleted
    -   method `markAllProductsForExport()` has been deleted
    -   method `markAllProductsAsExported()` has been deleted
    -   method `getProductsWithAvailability()` has been deleted
    -   method `getProductsWithParameter()` has been deleted
    -   method `getProductsWithBrand()` has been deleted
    -   method `getProductsWithFlag()` has been deleted
    -   method `getProductsWithUnit()` has been deleted
    -   method `createFriendlyUrlsWhenRenamed()` has been deleted
    -   method `getChangedNamesByLocale()` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\ProductFactory`:
    -   method `setCalculatedAvailabilityIfMissing()` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\ProductRepository`:
    -   method `markAllProductsForAvailabilityRecalculation()` has been deleted
    -   method `markAllProductsForPriceRecalculation()` has been deleted
    -   method `getProductsForPriceRecalculationIterator()` has been deleted
    -   method `getProductsForAvailabilityRecalculationIterator()` has been deleted
    -   method `getAtLeastSomewhereSellableVariantsByMainVariant()` has been deleted
    -   method `markProductsForExport()` has been deleted
    -   method `markAllProductsForExport()` has been deleted
    -   method `markAllProductsAsExported()` has been deleted
    -   method `getProductsWithParameter()` has been deleted
    -   method `getProductsWithBrand()` has been deleted
    -   method `getProductsWithFlag()` has been deleted
    -   method `getProductsWithUnit()` has been deleted
    -   method `getProductsWithAvailability()` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator`:
    -   method `calculateSellingDeniedForProduct()` has been deleted
    -   method `getProductsForCalculations()` has been deleted
    -   method `propagateVariantsSellingDeniedToMainVariant` changed its interface:
    ```diff
        /**
    -    * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
    +    * @param int[] $productIds
         */
        protected function propagateVariantsSellingDeniedToMainVariant(
    -       array $products,
    +       array $productIds,
    -   )
    +   ): void
    ```
-   `Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly ProductFacade $productFacade,
            protected readonly ProductDataFactoryInterface $productDataFactory,
            protected readonly ImageFacade $imageFacade,
            protected readonly ProductFactoryInterface $productFactory,
    -       protected readonly ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
    -       protected readonly ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
    -       protected readonly ProductExportScheduler $productExportScheduler,
    +       protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        )
    ```
    -   property `$productPriceRecalculationScheduler` has been deleted
    -   property `$productAvailabilityRecalculationScheduler` has been deleted
    -   property `$productExportScheduler` has been deleted
    -   method `createVariant` changed its interface:
    ```diff
        public function createVariant(
            Product $mainProduct,
            array $variants,
    -   )
    +   ): Product
    ```
-   `Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade`:
    -   property `$recalcVisibilityForMarked` has been deleted
    -   method `refreshProductsVisibilityForMarkedDelayed()` has been deleted
    -   method `refreshProductsVisibility()` has been deleted
    -   method `refreshProductsVisibilityForMarked()` has been deleted
    -   method `markProductsForRecalculationAffectedByCategory()` has been deleted
    -   method `onKernelResponse()` has been deleted
-   `Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
    -       EntityManagerInterface $em,
    +       protected readonly EntityManagerDecorator $em,
            protected readonly Domain $domain,
            protected readonly PricingGroupRepository $pricingGroupRepository,
        )
    ```
    -   method `refreshProductsVisibility()` changed its interface:
    ```diff
        /**
    -    * @param bool $onlyMarkedProducts
    +    * @param int[]|null $productIds
         */
        public function refreshProductsVisibility(
    -       $onlyMarkedProducts = false,
    +       ?array $productIds = null,
    -   )
    +   ): void
    ```
    -   method `markProductsForRecalculationAffectedByCategory()` has been deleted
    -   method `refreshGlobalProductVisibility()` has been deleted
    -   method `markAllProductsVisibilityAsRecalculated()` has been deleted
    -   method `hideMainVariantsWithoutVisibleVariants` changed its interface:
    ```diff
        /**
    -    * @param bool $onlyMarkedProducts
    +    * @param int[]|null $productIds
         */
        protected function hideMainVariantsWithoutVisibleVariants(
    -       $onlyMarkedProducts,
    +       ?array $productIds,
        )
    ```
-   `Shopsys\FrameworkBundle\Model\Store\StoreFacade`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly StoreRepository $storeRepository,
            protected readonly StoreFactory $storeFactory,
            protected readonly FriendlyUrlFacade $friendlyUrlFacade,
            protected readonly ImageFacade $imageFacade,
            protected readonly EntityManagerInterface $em,
    -       protected readonly ProductRepository $productRepository,
    +       protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        )
    ```
    -   property `$productRepository` has been deleted
-   `Shopsys\FrameworkBundle\Twig\ProductVisibilityExtension`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
    -       protected readonly ProductVisibilityRepository $productVisibilityRepository,
    +       protected readonly ProductVisibilityFacade $productVisibilityFacade,
            protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
            protected readonly Domain $domain,
        )
    ```
    -   property `$productVisibilityRepository` has been deleted
-   `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly Domain $domain,
            protected readonly ProductCollectionFacade $productCollectionFacade,
            protected readonly ProductAccessoryFacade $productAccessoryFacade,
            protected readonly CurrentCustomerUser $currentCustomerUser,
            protected readonly ParameterWithValuesFactory $parameterWithValuesFactory,
    +       protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        )
    ```
    -   method `getAvailability` changed its interface:
    ```diff
        public function getAvailability(
            Product $product,
    -   ): Availability
    +   ): array
    ```
-   `Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItemFactory`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
    -       HeurekaProductDataBatchLoader $heurekaProductDataBatchLoader,
    +       protected readonly HeurekaProductDataBatchLoader $productDataBatchLoader,
            protected readonly HeurekaCategoryFacade $heurekaCategoryFacade,
            protected readonly CategoryFacade $categoryFacade,
    +       protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        )
    ```
-   `Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFactory`:
    -   method `__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
            protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
            protected readonly ProductParametersBatchLoader $productParametersBatchLoader,
            protected readonly CategoryFacade $categoryFacade,
    +       protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        )
    ```

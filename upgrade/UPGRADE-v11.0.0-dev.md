# [Upgrade from v10.0.0 to v11.0.0-dev](https://github.com/shopsys/shopsys/compare/v10.0.0...master)

This guide contains instructions to upgrade from version v10.0.0 to v11.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Removed deprecations

- check that your code don't use any removed code ([#2455](https://github.com/shopsys/shopsys/pull/2455))
    - Phing property `path.env.test` was removed
    - function `getCategoriesOrderingData()` from `@shopsys/framework/assets/js/admin/components/CategoryTreeSorting.js` was removed, use `getNestedSetData()` instead
    - method `Shopsys\FrameworkBundle\Controller\Admin\CategoryController::saveOrderAction()` was removed, use `applySortingAction()` instead
    - method `Shopsys\FrameworkBundle\Model\Category\CategoryFacade::editOrdering()` was removed, use `reorderByNestedSetValues()` instead
    - data transformer `Shopsys\FrameworkBundle\Form\Transformers\ScriptPlacementToBooleanTransformer` was removed as it's not necessary anymore
    - command `Shopsys\FrameworkBundle\Command\ElFinderPostInstallCommand` was removed, set proper public dir with `--docroot` option in `elfinder:install` command
    - class `Shopsys\FrameworkBundle\Component\Csv\CsvReader` was removed, use `SplFileObject::fgetcsv()` instead
    - command `Shopsys\FrameworkBundle\Command\Elasticsearch\ElasticsearchIndexesCreateCommand` was removed, use `ElasticsearchIndexesMigrateCommand` instead
    - method `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition::getLegacyIndexAlias()` was removed
    - visibility of method `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade::create()` was changed to `protected`
    - method `Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexException::indexAlreadyExists()` was removed, use `ElasticsearchIndexAlreadyExistsException` directly
    - class `Shopsys\FrameworkBundle\Component\DataFixture\AbstractNativeFixture` was removed
    - removed constant `Shopsys\FrameworkBundle\Component\Environment\EnvironmentFileSetting\ENVIRONMENTS_CONSOLE`
    - `Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory`
        - property `$configLoader` was removed
        - property `$container` is no longer nullable
        - property `$cacheDir`  is no longer nullable
        - method `__construct`  changed its interface:
            ```diff
                public function __construct(
                    $routerConfiguration,
            -       ?LoaderInterface $configLoader,
                    LocalizedRouterFactory $localizedRouterFactory,
                    FriendlyUrlRouterFactory $friendlyUrlRouterFactory,
                    Domain $domain,
                    RequestStack $requestStack,
            -       ?ContainerInterface $container = null,
            +       ContainerInterface $container,
            -       ?string $cacheDir = null
            +       string $cacheDir
                )
            ```
    - `Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory`
        - property `$configLoader` was removed
        - property `$container` is no longer nullable
        - property `$cacheDir`  is no longer nullable
        - method `__construct`  changed its interface:
            ```diff
                public function __construct(
                    $localeRoutersResourcesFilepathMask,
            -       ?LoaderInterface $configLoader = null,
            -       ?ContainerInterface $container = null,
            +       ContainerInterface $container,
            -       ?string $cacheDir = null
            +       string $cacheDir
                )
            ```
    - `Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCategoryFilter`
        - property `$domain` was removed
        - method `__construct`  changed its interface:
            ```diff
                public function __construct(
                    CategoryFacade $categoryFacade,
            -       ?Domain $domain = null,
            -       ?LocalizationAlias $localization = null
            +       Localization $localization
                )
            ```
    - class `Shopsys\FrameworkBundle\Model\Breadcrumb\ErrorPageBreadcrumbGenerator` was changed to abstract, don't forget to extend this class and implement following methods:
        - `getTranslatedBreadcrumbForNotFoundPage()`
        - `getTranslatedBreadcrumbForErrorPage()`
    - class `Shopsys\FrameworkBundle\Model\Breadcrumb\SimpleBreadcrumbGenerator` was changed to abstract, don't forget to extend this class and implement following methods:
        - `getTranslatedBreadcrumbsByRouteNames()`
    - class `Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade` was changed to abstract, don't forget to extend this class and implement following methods:
        - `getMessageForNoLongerAvailableExistingProduct()`
        - `getMessageForNoLongerAvailableProduct()`
        - `getMessageForChangedProduct()`
    - class `Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade` was changed to abstract, don't forget to extend this class and implement following methods:
        - `getTermsAndConditionsDownloadFilename()`
    - class `Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForBrandFacade` was changed to abstract, don't forget to extend this class and implement following methods:
        - `getSupportedOrderingModesNamesById()`
    - class `Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade` was changed to abstract, don't forget to extend this class and implement following methods:
        - `getSupportedOrderingModesNamesById()`
    - class `Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForSearchFacade` was changed to abstract, don't forget to extend this class and implement following methods:
        - `getSupportedOrderingModesNamesById()`
    - method `Shopsys\FrameworkBundle\Model\Category\CategoryFacade::getTranslatedAll()` was removed, use `getAllTranslated()` instead
    - method `Shopsys\FrameworkBundle\Model\Category\CategoryFacade::getTranslatedAllWithoutBranch` was removed, use `getAllTranslatedWithoutBranch()` instead
    - method `Shopsys\FrameworkBundle\Model\Category\CategoryRepository::getTranslatedAll()` was removed, use `getAllTranslated()` instead
    - method `Shopsys\FrameworkBundle\Model\Category\CategoryRepository::getTranslatedAllWithoutBranch()` was removed, use `getAllTranslatedWithoutBranch()` instead
    - method `Shopsys\FrameworkBundle\Model\Category\CategoryRepository::addTranslationPublic()` was removed
        - use `addTranslation()`, which visibility was changed to public
    - method `Shopsys\FrameworkBundle\Model\Category\CategoryRepository::filterBySearchTextPublic()` was removed
        - use `filterBySearchText()`, which visibility was changed to public
    - method `Shopsys\FrameworkBundle\Model\Pricing\PriceConverter::convertPriceWithoutVatToPriceInDomainDefaultCurrency()` was removed, use `convertPriceWithoutVatToDomainDefaultCurrencyPrice()` instead
    - method `Shopsys\FrameworkBundle\Model\Pricing\PriceConverter::convertPriceWithVatToPriceInDomainDefaultCurrency()` was removed, use `convertPriceWithVatToDomainDefaultCurrencyPrice()` instead
    - method `Shopsys\FrameworkBundle\Model\Pricing\PriceConverter::__construct()` changed its interface:
        ```diff
            public function __construct(
                CurrencyFacade $currencyFacade,
                Rounding $rounding,
        -       ?Setting $setting = null
        +       Setting $setting
            )
        ```
    - method `Shopsys\FrameworkBundle\Model\Pricing\PriceConverter::convertPriceWithoutVatToPriceInDomainDefaultCurrency()` was removed, use `convertPriceWithoutVatToDomainDefaultCurrencyPrice'()` instead
    - method `Shopsys\FrameworkBundle\Model\Pricing\PriceConverter::convertPriceWithVatToPriceInDomainDefaultCurrency()` was removed, use `convertPriceWithVatToDomainDefaultCurrencyPrice'()` instead
    - method `Shopsys\FrameworkBundle\Model\Product\ProductFacade::getSellableByUuid` was removed, use method with the same name from the `Shopsys\FrontendApiBundle\Model\Product` namespace (you have to have shopsys/frontend-api package installed)
    - method `Shopsys\FrameworkBundle\Model\Product\ProductRepository::getSellableByUuid` was removed, use method with the same name from the `Shopsys\FrontendApiBundle\Model\Product` namespace (you have to have shopsys/frontend-api package installed)
    - method `Shopsys\FrameworkBundle\Model\Product\ProductRepository::getListableForBrandQueryBuilderPublic()` was removed
        - use `getListableForBrandQueryBuilder()`, which visibility was changed to public
    - `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade`:
        - method `createListableProductsInCategoryFilterQuery()` was removed, use `Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory::createListableProductsByCategoryId()` instead
        - method `createListableProductsForBrandFilterQuery()` was removed, use `Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory::createListableProductsByBrandId` instead
        - method `createListableProductsForSearchTextFilterQuery()` was removed, use `Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory::createListableProductsBySearchText()` instead
        - method `createFilterQueryWithProductFilterData()` was removed, use `Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory::createWithProductFilterData()` instead
        - method `getIndexName()` was removed, use `Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory::getIndexName()` instead
        - method `getProductsCountOnCurrentDomain()` was removed, use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getFilteredProductsCountOnCurrentDomain()` (you have to have shopsys/frontend-api package installed)
        - method `getProductsOnCurrentDomain()` was removed, use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getFilteredProductsOnCurrentDomain()` (you have to have shopsys/frontend-api package installed)
        - method `getProductsByCategory()` was removed, use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getFilteredProductsByCategory()` (you have to have shopsys/frontend-api package installed)
    - `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade`
        - method `getProductsCountOnCurrentDomain()` was removed, use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getFilteredProductsCountOnCurrentDomain()` (you have to have shopsys/frontend-api package installed)
        - method `getProductsOnCurrentDomain()` was removed, use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getFilteredProductsOnCurrentDomain()` (you have to have shopsys/frontend-api package installed)
        - method `getProductsByCategory()` was removed, use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getFilteredProductsByCategory()` (you have to have shopsys/frontend-api package installed)
    - `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface`:
        - method `getProductsCountOnCurrentDomain()` was removed
        - method `getProductsOnCurrentDomain()` was removed
        - method `getProductsByCategory()` was removed
    - constant `Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade::MAX_RESULTS` was removed
    - method `Shopsys\FrameworkBundle\Model\Script\ScriptFacade::getAllPagesScriptCodes()` was removed, use `getAllPagesBeforeContentScriptCodes()` instead
    - all unnecessary service definitions were removed from `services_test.yaml` file
    - removed `Shopsys\FrameworkBundle\Component\FileUpload\DeleteOldUploadedFilesCronModule` cron definition from shopsys/framework package, add this cron to your project config (it should be already set in cron.yaml if you properly followed all the previous upgrade notes or started your project on at least 9.0.2 version of Shopsys Framework)
    - removed css class `js-order-sent-page-variables` from `shopsys/framework/src/Resources/views/Admin/Content/Script/detail.html.twig`
    - `Shopsys\FrontendApiBundle\Model\Product\ProductFacade`
        - method `getProductsCountOnCurrentDomain()` was removed, use `getFilteredProductsCountOnCurrentDomain()` instead
        - method `getProductsOnCurrentDomain()` was removed, use `getFilteredProductsOnCurrentDomain()` instead
        - method `getProductsByCategory()` was removed, use `getFilteredProductsByCategory()` instead
        - method `getProductsByCategoryCount()` was removed, use `getFilteredProductsByCategoryCount()` instead
        - method `getProductsByBrand()` was removed, use `getFilteredProductsByBrand()` instead
        - method `getProductsByBrandCount()` was removed, use `getFilteredProductsByBrandCount()` instead
    - removed parameter `shopsys.var_dir` from shopsys/framework package, add this parameter to your project config (it should be already set in paths.yaml if you properly followed all the previous upgrade notes or started your project on at least 9.0.2 version of Shopsys Framework)
    - `Shopsys\FrontendApiBundle\Component\Constraints\ProductCanBeOrderedValidator`
        - property `$productFacade` was removed
        - property `$frontendApiProductFacade` is no longer nullable
        - method `__construct()` changed its interface:
            ```diff
                public function __construct(
            -       ProductFacade $productFacade,
                    ProductCachedAttributesFacade $productCachedAttributesFacade,
                    Domain $domain,
                    CurrentCustomerUser $currentCustomerUser,
            -       ?FrontendApiProductFacade $frontendApiProductFacade = null
            +       FrontendApiProductFacade $frontendApiProductFacade
                )
            ```
    - method `Shopsys\FrameworkBundle\Model\Article\ArticleRepository::getVisibleArticlesByDomainIdAndPlacementSortedByPositionQueryBuilder()` changed its visibility to `public`
    - method `Shopsys\FrameworkBundle\Model\Article\ArticleRepository::getAllVisibleQueryBuilder` changed its visibility to `public`
    - method `Shopsys\FrameworkBundle\Model\Article\ArticleRepository::getArticlesByDomainIdQueryBuilder` changed its visibility to `public`
    - `Shopsys\FrontendApiBundle\Model\Article\ArticleRepository`
        - property `$em` was removed
        - method `__construct()` changed its interface
            ```diff
            -   public function __construct(FrameworkArticleRepository $articleRepository, EntityManagerInterface $em)
            +   public function __construct(FrameworkArticleRepository $articleRepository)
            ```
        - method `getVisibleArticlesByDomainIdAndPlacementSortedByPositionQueryBuilder()` was removed, use method with the same name from the `Shopsys\FrameworkBundle\Model\Article\ArticleRepository` class
        - method `getArticlesByDomainIdQueryBuilder()` was removed, use method with the same name from the `Shopsys\FrameworkBundle\Model\Article\ArticleRepository` class
        - method `getAllVisibleQueryBuilder()` was removed, use method with the same name from the `Shopsys\FrameworkBundle\Model\Article\ArticleRepository` class
    - `Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolver`
        - property `$domain` is no longer nullable
        - property `$friendlyUrlFacade` is no longer nullable
        - method `__construct()` changed its interface
            ```diff
                public function __construct(
                    CategoryFacade $categoryFacade,
            -       ?Domain $domain = null,
            +       Domain $domain,
            -       ?FriendlyUrlFacade $friendlyUrlFacade = null
            +       FriendlyUrlFacade $friendlyUrlFacade
                )
            ```
        - method `resolver` used as resolver with an alias `category` was removed, use `resolveByUuidOrUrlSlug` method and resolver alias instead
    - class `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductResolver` used as resolver with alias `product` was removed, use `ProductDetailResolver` with alias `productDetail` instead
    - `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductResolverMap`
        - method `getProductLink()` was removed, use mapping via appropriate class `DataMapper\Product*FieldMapper` instead
        - method `getFlagsForData()` was removed, use mapping via appropriate class `DataMapper\Product*FieldMapper` instead
        - method `getCategoriesForData()`  was removed, use mapping via appropriate class `DataMapper\Product*FieldMapper` instead
        - property `$domain` was removed
        - property `$productCollectionFacade` was removed
        - property `flagFacade` was removed
        - property `$categoryFacade` was removed
        - method `__construct` changed its interface
            ```diff
                public function __construct(
            -       Domain $domain,
            -       ProductCollectionFacade $productCollectionFacade,
            -       FlagFacade $flagFacade,
            -       CategoryFacade $categoryFacade,
            -       ?ProductEntityFieldMapper $productEntityFieldMapper = null,
            +       ProductEntityFieldMapper $productEntityFieldMapper,
            -       ?ProductArrayFieldMapper $productArrayFieldMapper = null
            +       ProductArrayFieldMapper $productArrayFieldMapper
                )
            ```
    - `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsResolver`
        - constant `EDGE_COUNT` was removed
        - property `$productOnCurrentDomainFacade` was removed
        - property `$productFacade` is no longer nullable
        - property `$productFilterFacade` is no longer nullable
        - property `$productConnectionFactory` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
            -       ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
            -       ?ProductFacade $productFacade = null,
            +       ProductFacade $productFacade,
            -       ?ProductFilterFacade $productFilterFacade = null,
            +       ProductFilterFacade $productFilterFacade,
            -       ?ProductConnectionFactory $productConnectionFactory = null
            +       ProductConnectionFactory $productConnectionFactory
                )
            ```
    - method `Shopsys\ReadModelBundle\Image\ImageViewFacade::getForEntityIds()` was removed, use `getMainImagesByEntityIds()` instead
    - method `Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade::getForArray()` was removed, use `ProductActionViewFactory::createFromArray()` instead
    - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewElasticFacade`
        - property `$productActionViewFacade` was removed
        - method `createFromProducts()` was removed, use `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory::createFromProducts()` instead
        - method `getIdsForProducts()` was removed, use `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory::getIdsForProducts()` instead
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    ProductFacade $productFacade,
                    ProductAccessoryFacade $productAccessoryFacade,
                    Domain $domain,
                    CurrentCustomerUser $currentCustomerUser,
                    TopProductFacade $topProductFacade,
                    ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
                    ListedProductViewFactory $listedProductViewFactory,
            -       ProductActionViewFacade $productActionViewFacade,
                    ImageViewFacadeInterface $imageViewFacade,
            -       ?ProductActionViewFactory $productActionViewFactory = null,
            +       ProductActionViewFactory $productActionViewFactory,
            -       ?ProductElasticsearchProvider $productElasticsearchProvider = null
            +       ProductElasticsearchProvider $productElasticsearchProvider
                )
            ```
    - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFacade`
        - property `$imageViewFacade` was removed
        - property `$productActionViewFacade` was removed
        - method `createFromProducts()` was removed, use `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory::createFromProducts()` instead
        - method `getIdsForProducts()` was removed, use `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory::getIdsForProducts()` instead
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    ProductFacade $productFacade,
                    ProductAccessoryFacade $productAccessoryFacade,
                    Domain $domain,
                    CurrentCustomerUser $currentCustomerUser,
                    TopProductFacade $topProductFacade,
                    ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
            -       ListedProductViewFactory $listedProductViewFactory,
            -       ProductActionViewFacade $productActionViewFacade,
            -       ImageViewFacade $imageViewFacade
            +       ListedProductViewFactory $listedProductViewFactory
                )
            ```
    - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory`
        - method `getProductPriceFromArrayByPricingGroup()` was removed, use `Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory::createProductPriceFromArrayByPricingGroup()`
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    Domain $domain,
                    ProductCachedAttributesFacade $productCachedAttributesFacade,
            -       ?ImageViewFacadeInterface $imageViewFacade = null,
            +       ImageViewFacadeInterface $imageViewFacade,
            -       ?ProductActionViewFacadeInterface $productActionViewFacade = null,
            +       ProductActionViewFacadeInterface $productActionViewFacade,
            -       ?ProductActionViewFactory $productActionViewFactory = null,
            +       ProductActionViewFactory $productActionViewFactory,
            -       ?CurrentCustomerUser $currentCustomerUser = null,
            +       CurrentCustomerUser $currentCustomerUser,
            -       ?PriceFactory $priceFactory = null
            +       PriceFactory $priceFactory
                )
            ```
    - method `Shopsys\FrameworkBundle\Component\Environment\EnvironmentFileSetting::getEnvironment()` changed its interface
        ```diff
        -   public function getEnvironment(?bool $console = null): string
        +   public function getEnvironment(): string
        ```
    - `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade`
        - property `$friendlyUrlCacheKeyProvider` is no longer nullable
        - property `$mainFriendlyUrlSlugCache` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    EntityManagerInterface $em,
                    DomainRouterFactory $domainRouterFactory,
                    FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory,
                    FriendlyUrlRepository $friendlyUrlRepository,
                    Domain $domain,
                    FriendlyUrlFactoryInterface $friendlyUrlFactory,
            -       ?FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider = null,
            +       FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
            -       ?CacheInterface $mainFriendlyUrlSlugCache = null
            +       CacheInterface $mainFriendlyUrlSlugCache
                )
            ```
    - `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGenerator`
        - property `$friendlyUrlCacheKeyProvider` is no longer nullable
        - property `$mainFriendlyUrlSlugCache` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    RequestContext $context,
                    FriendlyUrlRepository $friendlyUrlRepository,
            -       ?FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider = null,
            +       FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
            -       ?CacheInterface $mainFriendlyUrlSlugCache = null
            +       CacheInterface $mainFriendlyUrlSlugCache
                )
            ```
    - method `Shopsys\FrameworkBundle\Model\Feed\FeedExportFactory::create()` changed its interface
        ```diff
            public function create(
                FeedInterface $feed,
                DomainConfig $domainConfig,
        -       $lastSeekId = null
        +       ?int $lastSeekId = null
            )
        ```
    - method `Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory::createProductPriceFromArrayByPricingGroup()` changed its return type and now can throw `NoProductPriceForPricingGroupException`
        ```diff
            public function createProductPriceFromArrayByPricingGroup(
                array $pricesArray,
                PricingGroup $pricingGroup
        -   ): ?ProductPrice
        +   ): ProductPrice
        ```
    - method `Shopsys\FrontendApiBundle\Model\Price\PriceFacade::createProductPriceFromArrayForCurrentCustomer()` changed its return type and now can throw `NoProductPriceForPricingGroupException`
        ```diff
            public function createProductPriceFromArrayForCurrentCustomer(
                array $pricesArray
        -   ): ?ProductPrice
        +   ): ProductPrice
        ```
    - `Shopsys\FrameworkBundle\Command\CronCommand`
        - property `$parameterBag` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    CronFacade $cronFacade,
                    MutexFactory $mutexFactory,
            -       ?ParameterBagInterface $parameterBag = null
            +       ParameterBagInterface $parameterBag
                )
            ```
    - `Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportChangedCronModule`
        - property `$eventDispatcher` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    AbstractIndex $index,
                    IndexFacade $indexFacade,
                    IndexDefinitionLoader $indexDefinitionLoader,
                    Domain $domain,
            -       ?EventDispatcherInterface $eventDispatcher = null
            +       EventDispatcherInterface $eventDispatcher
                )
            ```
    - `Shopsys\FrameworkBundle\Component\FileUpload\FileUpload`
        - property `$parameterBag` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    FileNamingConvention $fileNamingConvention,
                    MountManager $mountManager,
                    FilesystemInterface $filesystem,
            -       ?ParameterBagInterface $parameterBag = null
            +       ParameterBagInterface $parameterBag
                )
            ```
    - `Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig`
        - property `$entityNameResolver` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    array $imageEntityConfigsByClass,
            -       ?EntityNameResolver $entityNameResolver = null
            +       EntityNameResolver $entityNameResolver
                )
            ```
    - `Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader`
        - property `$entityNameResolver` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    Filesystem $filesystem,
            -       ?EntityNameResolver $entityNameResolver = null
            +       EntityNameResolver $entityNameResolver
                )
            ```
    - `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory`
        - property `$friendlyUrlCacheKeyProvider` is no longer nullable
        - property `$mainFriendlyUrlSlugCache` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    $friendlyUrlRouterResourceFilepath,
                    LoaderInterface $configLoader,
                    FriendlyUrlRepository $friendlyUrlRepository,
            -       ?FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider = null,
            +       FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
            -       ?CacheInterface $mainFriendlyUrlSlugCache = null
            +       CacheInterface $mainFriendlyUrlSlugCache
                )
            ```
    - `Shopsys\FrameworkBundle\Controller\Admin\CustomerController`
        - property `$domain` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    CustomerUserDataFactoryInterface $customerUserDataFactory,
                    CustomerUserListAdminFacade $customerUserListAdminFacade,
                    CustomerUserFacade $customerUserFacade,
                    BreadcrumbOverrider $breadcrumbOverrider,
                    AdministratorGridFacade $administratorGridFacade,
                    GridFactory $gridFactory,
                    AdminDomainTabsFacade $adminDomainTabsFacade,
                    OrderFacade $orderFacade,
                    LoginAsUserFacade $loginAsUserFacade,
                    DomainRouterFactory $domainRouterFactory,
                    CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
            -       ?Domain $domain = null
            +       Domain $domain
                )
            ```
    - `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade`
        - property `$customerUserRefreshTokenChainFacade` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    EntityManagerInterface $em,
                    CustomerUserRepository $customerUserRepository,
                    EncoderFactoryInterface $encoderFactory,
                    ResetPasswordMailFacade $resetPasswordMailFacade,
                    HashGenerator $hashGenerator,
            -       ?CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade = null
            +       CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
                )
            ```
    - `Shopsys\FrameworkBundle\Model\Product\ProductDataFactory`
        - property `$availabilityFacade` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    VatFacade $vatFacade,
                    ProductInputPriceFacade $productInputPriceFacade,
                    UnitFacade $unitFacade,
                    Domain $domain,
                    ProductRepository $productRepository,
                    ParameterRepository $parameterRepository,
                    FriendlyUrlFacade $friendlyUrlFacade,
                    ProductAccessoryRepository $productAccessoryRepository,
                    ImageFacade $imageFacade,
                    PluginCrudExtensionFacade $pluginDataFormExtensionFacade,
                    ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
                    PricingGroupFacade $pricingGroupFacade,
            -       ?AvailabilityFacade $availabilityFacade = null
            +       AvailabilityFacade $availabilityFacade
                )
            ```
    - `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository`
        - property `$categoryFacade` is no longer nullable
        - property `$productAccessoryFacade` is no longer nullable
        - property `$brandCachedFacade` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    EntityManagerInterface $em,
                    ParameterRepository $parameterRepository,
                    ProductFacade $productFacade,
                    FriendlyUrlRepository $friendlyUrlRepository,
                    Domain $domain,
                    ProductVisibilityRepository $productVisibilityRepository,
                    FriendlyUrlFacade $friendlyUrlFacade,
            -       ?CategoryFacade $categoryFacade = null,
            +       CategoryFacade $categoryFacade,
            -       ?ProductAccessoryFacade $productAccessoryFacade = null,
            +       ProductAccessoryFacade $productAccessoryFacade,
            -       ?BrandCachedFacade $brandCachedFacade = null
            +       BrandCachedFacade $brandCachedFacade
                )
            ```
    - `Shopsys\FrameworkBundle\Twig\UploadedFileExtension`
        - property `$uploadedFileLocator` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    Domain $domain,
                    UploadedFileFacade $uploadedFileFacade,
                    FileThumbnailExtension $fileThumbnailExtension,
            -       ?UploadedFileLocator $uploadedFileLocator = null
            +       UploadedFileLocator $uploadedFileLocator
                )
            ```
    - `Shopsys\FrontendApiBundle\Controller\FrontendApiController`
        - property `$graphqlConfigurator` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    GraphController $graphController,
                    EnabledOnDomainChecker $enabledOnDomainChecker,
            -       ?GraphqlConfigurator $graphqlConfigurator = null
            +       GraphqlConfigurator $graphqlConfigurator
                )
            ```
    - `Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesResolver`
        - property `$frontendApiImageFacade` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    ImageFacade $imageFacade,
                    ImageConfig $imageConfig,
                    Domain $domain,
            -       ?FrontendApiImageFacade $frontendApiImageFacade = null
            +       FrontendApiImageFacade $frontendApiImageFacade
                )
            ```
    - `Shopsys\FrontendApiBundle\Model\Resolver\Price\PriceResolver`
        - property `$priceFacade` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    ProductCachedAttributesFacade $productCachedAttributesFacade,
                    ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
                    PaymentPriceCalculation $paymentPriceCalculation,
                    Domain $domain,
                    CurrencyFacade $currencyFacade,
                    TransportPriceCalculation $transportPriceCalculation,
            -       ?PriceFacade $priceFacade = null
            +       PriceFacade $priceFacade
                )
            ```
    - `Shopsys\FrameworkBundle\Component\Image\ImageFacade`
        - property `$logger` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    $imageUrlPrefix,
                    EntityManagerInterface $em,
                    ImageConfig $imageConfig,
                    ImageRepository $imageRepository,
                    FilesystemInterface $filesystem,
                    FileUpload $fileUpload,
                    ImageLocator $imageLocator,
                    ImageFactoryInterface $imageFactory,
                    MountManager $mountManager,
            -       ?LoggerInterface $logger = null
            +       LoggerInterface $logger
                )
            ```
    - `Shopsys\FrameworkBundle\Model\Mail\Mailer`
        - property `$logger` is no longer nullable
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    Swift_Mailer $swiftMailer,
                    Swift_Transport $realSwiftTransport,
                    MailTemplateFacade $mailTemplateFacade,
            -       ?LoggerInterface $logger = null
            +       LoggerInterface $logger
                )
            ```
          
- Changes in error handling ([#2474](https://github.com/shopsys/shopsys/pull/2474))
    - `symfony/debug` component was replaced by `symfony/error-handler` component
        - reflect this change in your `composer.json`
            ```diff
            - "symfony/debug": "^4.4.0",
            + "symfony/error-handler": "^4.4.0",
            ```
        - if you import any classes from `symfony\debug` component, import them from `symfony/error-handler`
        - rename `config/routes/dev/twig.yaml` to `config/routes/dev/framework.yaml` and change its content
            ```diff
            - # config/routes/dev/twig.yaml
            + # config/routes/dev/framework.yaml
              _errors:
            -     resource: '@TwigBundle/Resources/config/routing/errors.xml'
            +     resource: '@FrameworkBundle/Resources/config/routing/errors.xml'
                  prefix:   /_error
            ```
    - Class `Shopsys\FrameworkBundle\Component\Error\ExceptionController` was removed and it's not needed anymore.
    - Class `App\Controller\Front\ErrorController` was moved to `Shopsys\FrameworkBundle\Component\Error\ErrorController`
        - change your config of `error_controller` in `config/packages/framework.yaml`
            ```diff
            -    error_controller: 'App\Controller\Front\ErrorController::showAction'
            +    error_controller: 'Shopsys\FrameworkBundle\Component\Error::showAction'
            ```
        - remove config of `ErrorController` in `config/services.yaml`. It is configured in framework from now on.
            ```diff
            - App\Controller\Front\ErrorController:
            -     arguments:
            -         $environment: '%kernel.environment%'
            -         $overwriteDomainUrl: '%env(default::OVERWRITE_DOMAIN_URL)%'
            ```
        - remove `front_error_page` and `front_error_page_format` routes from `config/routes/shopsys_front.yaml`. It's handled by framework from now on.
            ```diff
            - front_error_page:
            -     path: /_error/{code}/
            -     defaults:
            -         _controller: App\Controller\Front\ErrorController:errorPageAction
            -     requirements:
            -         code: \d+

            - front_error_page_format:
            -     path: /_error/{code}/{_format}/
            -     defaults:
            -         _controller: App\Controller\Front\ErrorController:errorPageAction
            -     requirements:
            -         code: \d+
            -         _format: css|html|js|json|txt|xml
            ```
        - see #project-base-diff to update your project

## Application

- remove unnecessary extended ImageExtension from your project, if you don't have any custom changes in the extension
    - see #project-base-diff to update your project
- remove unnecessary `services_acc.yaml` config from your project
    - create `config/packages/acc/framework.yaml` with configuration for acceptance testing
    - see #project-base-diff to update your project
- move `paths.yml` config file into packages subfolder to allow easy override them for different environments
    - see #project-base-diff to update your project
- set overwrite domain url to be taken exclusively from environment variable
    - see #project-base-diff to update your project
- set redis host to be taken exclusively from environment variable
    - see #project-base-diff to update your project
- set swift mailer configuration to be taken exclusively from environment variable
    - see #project-base-diff to update your project
- set application secret to be taken exclusively from environment variable
    - see #project-base-diff to update your project
- set doctrine configuration to be taken exclusively from environment variables
    - see #project-base-diff to update your project
- unused translations have been removed
    - the following `msgid` entries are no longer available in framework, they should be already added into your project directly
        - `Contact`
        - `Edit data`
        - `Forgotten password`
        - `Oops! Error occurred`
        - `Page not found`
        - `Product <strong>{{ name }}</strong> you had in cart is no longer available. Please check your order.`
        - `Product you had in cart is no longer in available. Please check your order.`
        - `Registration`
        - `Search [noun]`
        - `TOP`
        - `Terms-and-conditions.html`
        - `The price of the product <strong>{{ name }}</strong> you have in cart has changed. Please, check your order.`
        - `alphabetically A -> Z`
        - `alphabetically Z -> A`
        - `from most expensive`
        - `from the cheapest`
        - `relevance`
- `parameter(_test).yaml.dist` and auto-creation of `parameter(_test).yaml` files was removed
    - see #project-base-diff to update your project
    - you can create `parameters.yaml` manually to locally override some settings (for testing purposes for example)
    - your custom parameters should be in environment variable (if the value is environment-specific), or in different config file (if the value is project-specific)
- fix implementations of FileVisitorInterface::visitTwigFile ([#2465](https://github.com/shopsys/shopsys/pull/2465))
    - in the following classes, an interface of `visitTwigFile` was fixed to comply with `FileVisitorInterface`
        - `ConstraintMessageExtractor`
        - `ConstraintMessagePropertyExtractor`
        - `ConstraintViolationExtractor`
        - `JsFileExtractor`
        - `PhpFileExtractor`
        - `TwigFileExtractor`
- resolve Symfony 4.4 deprecations ([#2468](https://github.com/shopsys/shopsys/pull/2468))
    - remove Twig deprecation - an "if" condition on a "for" tag
        - see the last bullet point in https://twig.symfony.com/doc/2.x/deprecated.html#tags
        - see #project-base-diff
    - remove deprecated "bundle:controller:action" syntax from twig templates
        - see https://github.com/symfony/symfony/blob/4.1/UPGRADE-4.1.md#frameworkbundle
    - `Shopsys\FrameworkBundle\Component\Error\ExceptionListener` class:
        - property `$lastException` is renamed to `$lastThrowable`, its type is changed to `\Throwable|null`, and is strictly typed now
        - method `onKernelException` changed its interface:
        ```diff
        - onKernelException(GetResponseForExceptionEvent $event)
        + onKernelException(ExceptionEvent $event): void
        ```
        - method `getLastException` changed its interface and was renamed to `getLastThrowable`:
        ```diff
        - public function getLastException()
        + public function getLastThrowable(): ?Throwable
        ```
    - `Shopsys\FrontendApiBundle\Model\ErrorErrorHandlerListener` class:
        - method `onKernelException` changed its interface:
        ```diff
        - onKernelException(GetResponseForExceptionEvent $event)
        + onKernelException(ExceptionEvent $event): void
        ```
- replace usage of deprecated kernel events ([#2482](https://github.com/shopsys/shopsys/pull/2482))
    - see #project-base-diff to update your project
    - see https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.0.md#httpkernel for the full list of replacements for your code
    - `Shopsys\FrameworkBundle\Component\Domain\DomainAwareSecurityHeadersSetter` class:
        - method `onKernelResponse` changed its interface:
        ```diff
        - onKernelResponse(FilterResponseEvent $event)
        + onKernelResponse(ResponseEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Component\Domain\DomainSubscriber` class:
        - method `onKernelRequest` changed its interface:
        ```diff
        - onKernelRequest(GetResponseEvent $event)
        + onKernelRequest(RequestEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Component\HttpFoundation\DenyScriptNameInRequestPathListener` class:
        - method `onKernelRequest` changed its interface:
        ```diff
        - onKernelRequest(GetResponseEvent $event)
        + onKernelRequest(RequestEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Component\HttpFoundation\ResponseListener` class:
        - method `onKernelResponse` changed its interface:
        ```diff
        - onKernelResponse(FilterResponseEvent $event)
        + onKernelResponse(ResponseEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Component\HttpFoundation\SubRequestListener` class:
        - method `onKernelController` changed its interface:
        ```diff
        - onKernelController(FilterControllerEvent $event)
        + onKernelController(ControllerEvent $event): void
        ```
        - method `onKernelResponse` changed its interface:
        ```diff
        - onKernelResponse(FilterResponseEvent $event)
        + onKernelResponse(ResponseEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestListener` class:
        - method `onKernelRequest` changed its interface:
        ```diff
        - onKernelRequest(GetResponseEvent $event)
        + onKernelRequest(RequestEvent $event): void
        ```
        - method `onKernelResponse` changed its interface:
        ```diff
        - onKernelResponse(FilterResponseEvent $event)
        + onKernelResponse(ResponseEvent $event): void
        ```
        - method `onKernelException` changed its interface:
        ```diff
        - onKernelException(GetResponseForExceptionEvent $event)
        + onKernelException(ExceptionEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Component\HttpFoundation\VaryResponseByXRequestedWithHeaderListener` class:
        - method `onKernelResponse` changed its interface:
        ```diff
        - onKernelResponse(FilterResponseEvent $event)
        + onKernelResponse(ResponseEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Component\Log\SlowLogSubscriber` class:
        - method `initStartTime` changed its interface:
        ```diff
        - initStartTime(GetResponseEvent $event)
        + initStartTime(RequestEvent $event): void
        ```
        - method `addNotice` changed its interface:
        ```diff
        - addNotice(PostResponseEvent $event)
        + addNotice(TerminateEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector` class:
        - method `onKernelController` changed its interface:
        ```diff
        - onKernelController(FilterControllerEvent $event)
        + onKernelController(ControllerEvent $event): void
        ```
        - method `isProtected` changed its interface:
        ```diff
        - isProtected(FilterControllerEvent $event)
        + isProtected(ControllerEvent $event): bool
        ```
    - `Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedSubscriber` class:
        - method `onKernelRequest` changed its interface:
        ```diff
        - onKernelRequest(GetResponseEvent $event)
        + onKernelRequest(RequestEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Model\Cart\CartMigrationFacade` class:
        - method `onKernelController` changed its interface:
        ```diff
        - onKernelController(FilterControllerEvent $filterControllerEvent)
        + onKernelController(ControllerEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository` class:
        - method `onKernelResponse` changed its interface:
        ```diff
        - onKernelResponse(FilterResponseEvent $event)
        + onKernelResponse(ResponseEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Model\Localization\LocalizationListener` class:
        - method `onKernelRequest` changed its interface:
        ```diff
        - onKernelRequest(GetResponseEvent $event)
        + onKernelRequest(RequestEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Model\Pricing\InputPriceRecalculationScheduler` class:
        - method `onKernelResponse` changed its interface:
        ```diff
        - onKernelResponse(FilterResponseEvent $event)
        + onKernelResponse(ResponseEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade` class:
        - method `onKernelResponse` changed its interface:
        ```diff
        - onKernelResponse(FilterResponseEvent $event)
        + onKernelResponse(ResponseEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculator` class:
        - method `onKernelResponse` changed its interface:
        ```diff
        - onKernelResponse(FilterResponseEvent $event)
        + onKernelResponse(ResponseEvent $event): void
        ```
    - `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator` class:
        - method `onKernelResponse` changed its interface:
        ```diff
        - onKernelResponse(FilterResponseEvent $event)
        + onKernelResponse(ResponseEvent $event): void
        ```
- remove obsolete path parameters ([#2468](https://github.com/shopsys/shopsys/pull/2468))
    - see #project-base-diff to update your project
    - replace parameter `kernel.root_dir` with `%kernel.project_dir%/src` in your codebase
    - parameter `shopsys.framework.javascript_sources_dir` was removed because it's not used anywhere
- debug data collectors are now future compatible with Symfony 5 ([#2484](https://github.com/shopsys/shopsys/pull/2484))
    - `Shopsys\FrameworkBundle\Component\Collector\ShopsysFrameworkDataCollector` class:
        - method `collect` changed its interface:
        ```diff
        - collect(Request $request, Response $response, ?Exception $exception = null): void
        + collect(Request $request, Response $response, ?Throwable $exception = null): void
        ```
    - `Shopsys\FrameworkBundle\Component\Elasticsearch\Debug\ElasticsearchCollector` class:
        - method `collect` changed its interface:
        ```diff
        - collect(Request $request, Response $response, ?Exception $exception = null): void
        + collect(Request $request, Response $response, ?Throwable $exception = null): void
        ```
- remove not used `twig/extensions` package ([#2486](https://github.com/shopsys/shopsys/pull/2486))
    - see #project-base-diff to update your project
    - see the list of filters and update your code if you use any – https://github.com/twigphp/Twig-extensions
        - `trans` filter is already used from symfony/twig-bridge package
- resolve `symfony/translations` deprecations ([#2487](https://github.com/shopsys/shopsys/pull/2487))
    - see #project-base-diff
    - replace usage of `Symfony\Component\Translation\TranslatorInterface` with `Symfony\Contracts\Translation\TranslatorInterface`
    - remove usage of deprecated `Symfony\Component\Translation\MessageSelector`
    - replace `transchoice` filter with `trans` filter and count parameter
        - example:
        ```diff
        -    {% transchoice cart.itemsCount with { '%itemsCount%': cart.itemsCount, '%priceWithVat%': productsPrice.priceWithVat|price } %}
        -        {1} <strong class="cart__state">%itemsCount%</strong> item for <strong class="cart__state">%priceWithVat%</strong>|[2,Inf] <strong class="cart__state">%itemsCount%</strong> items for <strong class="cart__state">%priceWithVat%</strong>
        -    {% endtranschoice %}
        +    {% trans with { '%count%': cart.itemsCount, '%itemsCount%': cart.itemsCount, '%priceWithVat%': productsPrice.priceWithVat|price } %}
        +        {1} <strong class="cart__state">%itemsCount%</strong> item for <strong class="cart__state">%priceWithVat%</strong>|[2,Inf] <strong class="cart__state">%itemsCount%</strong> items for <strong class="cart__state">%priceWithVat%</strong>
        +    {% endtrans %}
        ```
    - `transchoice()` method was removed from `\Shopsys\FrameworkBundle\Component\Translation\Translator`, use `trans()` with `count` parameter instead
    - static `tc()` method was removed from `\Shopsys\FrameworkBundle\Component\Translation\Translator`, use `t()` with `count` parameter instead
    - static `tc()` function was removed from global namespace, use `t()` with `count` parameter instead
- update to latest version heureka/overeno-zakazniky package ([#2526](https://github.com/shopsys/shopsys/pull/2526))
    - see #project-base-diff to update your project
- update twig/twig to v2.15.4 in order to fix CVE-2022-39261 ([#2527](https://github.com/shopsys/shopsys/pull/2527))
    - see #project-base-diff to update your project
- fill missing customer demo data for smooth testing of application ([#2529](https://github.com/shopsys/shopsys/pull/2529))
    - see #project-base-diff to update your project
- remove product variant urls from sitemap ([#2530](https://github.com/shopsys/shopsys/pull/2530))
    - replace usages of `SitemapFacade::getSitemapItemsForVisibleProducts()` with `SitemapFacade::getSitemapItemsForListableProducts()` in your project as old method no longer exists
    - replace usages of `SitemapRepository::getSitemapItemsForVisibleProducts()` with `SitemapRepository::getSitemapItemsForListableProducts()` in your project as old method no longer exists
- session is valid for a year and the cart is now only deleted after 130 days from the user's last activity ([#2537](https://github.com/shopsys/shopsys/pull/2537))
    - see #project-base-diff to update your project
- adding an index to the columns lft,rgt ([#2537](https://github.com/shopsys/shopsys/pull/2537))
    - see #project-base-diff to update your project
- Fix editing attribute created at of article, attribute created at has been moved from project base to framework ([#2546](https://github.com/shopsys/shopsys/pull/2546))
    - see #project-base-diff to update your project
- old phpRedisAdmin has been replaced with new Redis Commander ([#2550](https://github.com/shopsys/shopsys/pull/2550))
    - see #project-base-diff to update your project
- update sitemaps and add product image sitemap ([#2551](https://github.com/shopsys/shopsys/pull/2551))
    - see #project-base-diff to update your project
    - from SitemapLister was removed unused constants for page priorities:
      - PRIORITY_HOMEPAGE
      - PRIORITY_CATEGORIES
      - PRIORITY_PRODUCTS
      - PRIORITY_ARTICLES
- product ordering in all product lists has been changed, now are products ordered primary by availability dispatch time. 
    In stock as first and out of stock as last.  ([#2555](https://github.com/shopsys/shopsys/pull/2555))
  - see #project-base-diff to update your project

## Composer dependencies

- replace swiftmailer with symfony/mailer ([#2470](https://github.com/shopsys/shopsys/pull/2470))
    - see #project-base-diff
    - from now on, the mail transport is configured using `MAILER_DSN` env variable
        - `MAILER_TRANSPORT`, `MAILER_HOST`, `MAILER_USER`, and `MAILER_PASSWORD` env variables had been removed
    - the mail spooling functionality has been removed without replacement
        - if you need the asynchronous mails, you can implement it using [Symfony messenger](https://symfony.com/doc/current/mailer.html#sending-messages-async)
    - `Shopsys\FrameworkBundle\Component\Cron\CronFacade` class:
        - `$mailer` property has been removed
        - constructor changed its interface:
        ```diff
            public function __construct(
                 Logger $logger,
                 CronConfig $cronConfig,
                 CronModuleFacade $cronModuleFacade,
        -        Mailer $mailer,
                 CronModuleExecutor $cronModuleExecutor
        ```
    - `Shopsys\FrameworkBundle\Model\Mail\Exception\EmptyMailException` has been removed
    - `Shopsys\FrameworkBundle\Model\Mail\Exception\SendMailFailedException` has been removed
    - `Shopsys\FrameworkBundle\Model\Mail\Mailer` class:
        - property `$swiftMailer` has been removed
        - property `$realSwiftTransport` has been removed
        - property `$mailTemplateFacade` is now strictly typed
        - constructor changed its interface:
        ```diff
            public function __construct(
        -        Swift_Mailer $swiftMailer,
        -        Swift_Transport $realSwiftTransport,
        +        MailerInterface $symfonyMailer,
                 MailTemplateFacade $mailTemplateFacade,
                 LoggerInterface $logger
        ```
        - method `flushSpoolQueue` has been removed
        - method `send` is now strictly typed
        - method `getMessageWithReplacedVariables` is now strictly typed and returns `\Symfony\Component\Mime\Email` instead of `\Swift_Message`
        - method `replaceVariables` is now strictly typed
    - `Shopsys\FrameworkBundle\Model\Mail\MessageData` class is now strictly typed
    - `Shopsys\FrameworkBundle\Twig\MailerSettingExtension` class:
        - property `$container` has been removed
        - property `$isDeliveryDisabled` has been removed
        - property `$mailerMasterEmailAddress` has been removed
        - property `$twigEnvironment` is now strictly typed
        - constructor changed its interface:
        ```diff
        - public function __construct(ContainerInterface $container, Environment $twigEnvironment)
        + public function __construct(MailerSettingProvider $mailerSettingProvider, Environment $twigEnvironment)
        ```
    - translations - the `Unable to send updating email` msgid is no longer available
- update `overblog/graphql-bundle` to `^0.14.3` ([#2479](https://github.com/shopsys/shopsys/pull/2479))
    - switch implementation of deprecated `Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface` to `Overblog\GraphQLBundle\Definition\Resolver\QueryInterface` in your resolvers
    - change `resolver` expression function to `query` in your types defined in yaml files
        - Old signature (deprecated): `resolver(string $alias, array $args = []): mixed`
        - New signature: `query(string $alias, ...$args): mixed`
        - Example:
        ```diff
        - resolve: "@=resolver('categoriesSearch', [args])"
        + resolve: "@=query('categoriesSearch', arg1, arg2, ...)"
        ```
    - change `service` expression function to `query` in your types defined in yaml files
        - it is no longer supported to use private services in @=service function
        - for more details check: https://github.com/overblog/GraphQLBundle/blob/master/docs/definitions/expression-language.md#private-services
        - Example:
        ```diff
        - resolve: '@=service("Shopsys\\FrontendApiBundle\\Model\\Resolver\\Image\\ImagesResolver").resolveByAdvert(value, args["type"], args["size"])'
        + resolve: '@=query("Shopsys\\FrontendApiBundle\\Model\\Resolver\\Image\\ImagesResolver::resolveByAdvert", value, args["type"], args["size"])'
        ```
    - `mutation` expression function signature was changed
        - Old signature: `mutation(string $alias, array $args = []): mixed`
        - New signature: `mutation(string $alias, ...$args): mixed`
        - Example:
        ```diff
        - resolve: "@=mutation('create_order', [args, validator])"
        + resolve: resolve: "@=mutation('create_order', args, validator)"
        ```
    - check other changes in [GraphQLBundle UPGRADE notes](https://github.com/overblog/GraphQLBundle/blob/master/UPGRADE.md#upgrade-from-013-to-014) and implement them in your codebase
- remove mocked Events from your tests ([#2490](https://github.com/shopsys/shopsys/pull/2490))
    - see #project-base-diff
    - since Symfony 5.0 are all Events from `Symfony\Component\HttpKernel\Event` namespace final and cannot be mocked
- use logger methods as they're specified in PSR-3 ([#2483](https://github.com/shopsys/shopsys/pull/2483))
    - replace any usages of `Logger::add<Emergency|Alert|Critical|Notice|Debug|Error|Warning|Info>` with corresponding call of `emergency|alert|critical|notice|debug|error|warning|info` method
- update to Symfony 5.4 ([#2496](https://github.com/shopsys/shopsys/pull/2496))
    - see Symfony upgrade notes:
        - ([Upgrade from 4.4 to 5.0](https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.0.md))
        - ([Upgrade from 5.0 to 5.1](https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.1.md))
        - ([Upgrade from 5.1 to 5.2](https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.2.md))
        - ([Upgrade from 5.2 to 5.3](https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.3.md))
        - ([Upgrade from 5.3 to 5.4](https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.4.md))
    - here is quick summary you need to know, but we still encourage you to ready Symfony upgrade notes
        - in Request and Request events `masterRequest` has been renamed to `mainRequest` so you have to update your usages accordingly
        - you no longer need to use `Constraint/Date` in your date form types as validation is done by underlying type hinted code ([more described in Symfony Upgrade notes](https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.0.md#validator))
        - use `Symfony\Component\Security\Core\Exception\UsernameNotFoundException` instead of `Symfony\Component\Security\Core\Exception\UserNotFoundException`
        - `Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken` does no longer have second argument credentials so update your usages accordingly
        - support for `bundle:controller:action` syntax has been removed, use `serviceOrFqcn::method` instead ([more info in Symfony Upgrade notes](https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.0.md#frameworkbundle))
        - in functional and application tests requesting of container is changed from public method (`$this->getContainer()`) to static method (`self::getContainer()`) so you have to update your code accordingly
        - we have moved code for requesting application from `FunctionalTestCase` tests to new `ApplicationTestCase` so functional tests and application tests are now separated
    - many typehints were added so run `php phing phpstan` to find occurrences in your code that need to be updated accordingly
    - also see #project-base-diff for more information about changes needed to be done in your project
- fix tests for product searching ([#2524](https://github.com/shopsys/shopsys/pull/2524))
    - see #project-base-diff for more information about changes needed to be done in your project
- little translation tweaks for better developer experience ([2549](https://github.com/shopsys/shopsys/pull/2549))
    - `Shopsys\FrameworkBundle\Component\Translation\Translator` class:
        - constant `DEFAULT_DOMAIN` renamed to `DEFAULT_TRANSLATION_DOMAIN` and its visibility changed to `public`
        - added public constant `DATA_FIXTURES_TRANSLATION_DOMAIN` to use for data fixtures translations
    - global function `t` changed its interface:
    ```diff
    -  function t($id, array $parameters = [], $domain = null, $locale = null)
    +  function t(string $id, array $parameters = [], ?string $translationDomain = null, ?string $locale = null): string
    ```
    - `Shopsys\FrameworkBundle\Twig\TranslationExtension` class:
        - method `transHtml` changed its interface:
        ```diff
        -  transHtml(Environment $twig, $message, array $arguments = [], $domain = null, $locale = null)
        +  transHtml(Environment $twig, string $message, array $arguments = [], ?string $translationDomain = null, ?string $locale = null): string
        ```
    - `Shopsys\FrameworkBundle\Component\Translation\JsFileExtractor` class:
        - constant `DEFAULT_MESSAGE_DOMAIN` has been removed, use `Shopsys\FrameworkBundle\Component\Translation\Translator::DEFAULT_TRANSLATION_DOMAIN` instead
    - `Shopsys\FrameworkBundle\Component\Translation\PhpFileExtractor` class:
        - constant `DEFAULT_MESSAGE_DOMAIN` has been removed, use `Shopsys\FrameworkBundle\Component\Translation\Translator::DEFAULT_TRANSLATION_DOMAIN` instead
    - also see #project-base-diff for more information about changes needed to be done in your project
- added new `demo-data` phing target that does the same as `db-demo` plus exports data to Elasticsearch so we suggest you to use new phing target instead ([#2520](https://github.com/shopsys/shopsys/pull/2520))
    - see #project-base-diff for more information about changes needed to be done in your project
- resolve deprecations after update to Symfony 5.4 ([#2521](https://github.com/shopsys/shopsys/pull/2521))
    - `League\Flysystem\FilesystemOperator` is now used for autoload of abstract filesystem classes instead of `League\Flysystem\FilesystemInterface` update such occurrences in your project
    - some methods have been renamed in flysystem e.g. `getSize` to `fileSize` etc. run `php phing phpstan` to find such places and replace your usages accordingly
    - sessions are no longer handled by snc_redis, but newly by Symfony handler, theirs definition moved from `config/packages/snc_redis.yaml` to `config/services.yaml` review these files if you need specific settings for sessions
    - Redis Docker image has been updated from version 5 to version 7, update your Docker files, CI and production settings accordingly
    - with new authenticator manager enabled paths definitions in `packages/security.yaml` needs roles changed from `IS_AUTHENTICATED_ANONYMOUSLY` to new `PUBLIC_ACCESS` as `IS_AUTHENTICATED_ANONYMOUSLY` is deprecated
    - `Shopsys\FrameworkBundle\Command\RouterDebugCommandForDomain` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(DomainChoiceHandler $domainChoiceHelper, RouterInterface $router, ?FileLinkFormatter $fileLinkFormatter = null)
        + __construct(DomainChoiceHandler $domainChoiceHelper, RouterDebugCommand $routerDebugCommand, KernelInterface $kernel)
        ```
    - `Shopsys\FrameworkBundle\Command\RouterMatchCommandForDomain` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(DomainChoiceHandler $domainChoiceHelper, RouterInterface $router)
        + __construct(DomainChoiceHandler $domainChoiceHelper, RouterMatchCommand $routerMatchCommand, KernelInterface $kernel)
        ```
    - `Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(TokenFacade $tokenFacade)
        + __construct(TokenFacade $tokenFacade, FrontendApiUserProvider $frontendApiUserProvider)
        ```
        - method `__construct` changed its interface:
        ```diff
        - checkCredentials($credentials, UserInterface $user): bool
        + checkCredentials(?string $credentials): bool
        ```
        - method `getUser` has been removed without substitution
        - method `supportsRememberMe` has been removed without substitution
        - method `start` has been removed without substitution
    - `Shopsys\FrameworkBundle\Component\Translation\Translator` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(TranslatorInterface & LocaleAwareInterface $originalTranslator, TranslatorBagInterface $originalTranslatorBag, TranslatorInterface & LocaleAwareInterface $identityTranslator, MessageIdNormalizer $messageIdNormalizer)
        + __construct(TranslatorInterface|LocaleAwareInterface|DataCollectorTranslator $originalTranslator, TranslatorBagInterface|DataCollectorTranslator $originalTranslatorBag, TranslatorInterface|LocaleAwareInterface|IdentityTranslator $identityTranslator, MessageIdNormalizer $messageIdNormalizer)
    - `Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(Domain $domain, SessionInterface $session)
        + __construct(Domain $domain, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Component\Form\FormTimeProvider` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(SessionInterface $session)
        + __construct(Domain $domain, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(SessionInterface $session, AdministratorUserProvider $administratorUserProvider, AccessDecisionManagerInterface $accessDecisionManager, AuthorizationCheckerInterface $authorizationChecker)
        + __construct(RequestStack $requestStack, AdministratorUserProvider $administratorUserProvider, AccessDecisionManagerInterface $accessDecisionManager, AuthorizationCheckerInterface $authorizationChecker)
        ```
    - `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(CurrentCustomerUser $currentCustomerUser, SessionInterface $session)
        + __construct(CurrentCustomerUser $currentCustomerUser, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(PromoCodeFacade $promoCodeFacade, SessionInterface $session)
        + __construct(PromoCodeFacade $promoCodeFacade, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher, SessionInterface $session, CustomerUserRepository $customerUserRepository, AdministratorFrontSecurityFacade $administratorFrontSecurityFacade)
        + __construct(TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher, CustomerUserRepository $customerUserRepository, AdministratorFrontSecurityFacade $administratorFrontSecurityFacade, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Controller\Admin\CategoryController` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(CategoryFacade $categoryFacade, CategoryDataFactoryInterface $categoryDataFactory, SessionInterface $session, Domain $domain, BreadcrumbOverrider $breadcrumbOverrider)
        + __construct(CategoryFacade $categoryFacade, CategoryDataFactoryInterface $categoryDataFactory, Domain $domain, BreadcrumbOverrider $breadcrumbOverrider, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentWatcher` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(SessionInterface $session, PaymentPriceCalculation $paymentPriceCalculation, TransportPriceCalculation $transportPriceCalculation)
        + __construct(RequestStack $requestStack, PaymentPriceCalculation $paymentPriceCalculation, TransportPriceCalculation $transportPriceCalculation)
        ```
    - `Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(Domain $domain, SessionInterface $session)
        + __construct(Domain $domain, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Component\Form\FormTimeProvider` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(SessionInterface $session)
        + __construct(Domain $domain, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(SessionInterface $session, AdministratorUserProvider $administratorUserProvider, AccessDecisionManagerInterface $accessDecisionManager, AuthorizationCheckerInterface $authorizationChecker)
        + __construct(RequestStack $requestStack, AdministratorUserProvider $administratorUserProvider, AccessDecisionManagerInterface $accessDecisionManager, AuthorizationCheckerInterface $authorizationChecker)
        ```
    - `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(CurrentCustomerUser $currentCustomerUser, SessionInterface $session)
        + __construct(CurrentCustomerUser $currentCustomerUser, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(PromoCodeFacade $promoCodeFacade, SessionInterface $session)
        + __construct(PromoCodeFacade $promoCodeFacade, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher, SessionInterface $session, CustomerUserRepository $customerUserRepository, AdministratorFrontSecurityFacade $administratorFrontSecurityFacade)
        + __construct(TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher, CustomerUserRepository $customerUserRepository, AdministratorFrontSecurityFacade $administratorFrontSecurityFacade, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Controller\Admin\CategoryController` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(CategoryFacade $categoryFacade, CategoryDataFactoryInterface $categoryDataFactory, SessionInterface $session, Domain $domain, BreadcrumbOverrider $breadcrumbOverrider)
        + __construct(CategoryFacade $categoryFacade, CategoryDataFactoryInterface $categoryDataFactory, Domain $domain, BreadcrumbOverrider $breadcrumbOverrider, RequestStack $requestStack)
        ```
    - `Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentWatcher` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(SessionInterface $session, PaymentPriceCalculation $paymentPriceCalculation, TransportPriceCalculation $transportPriceCalculation)
        + __construct(RequestStack $requestStack, PaymentPriceCalculation $paymentPriceCalculation, TransportPriceCalculation $transportPriceCalculation)
        ```
    - `Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(FrontendCustomerUserProvider $frontendCustomerUserProvider, UserPasswordEncoderInterface $userPasswordEncoder, CustomerUserPasswordFacade $customerUserPasswordFacade, CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade, TokenStorageInterface $tokenStorage, CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, CustomerUserFacade $customerUserFacade, CustomerUserDataFactory $customerUserDataFactory, TokenFacade $tokenFacade)
        + __construct(TokenStorageInterface $tokenStorage, FrontendCustomerUserProvider $frontendCustomerUserProvider, UserPasswordHasherInterface $userPasswordHasher, CustomerUserPasswordFacade $customerUserPasswordFacade, CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade, CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, CustomerUserFacade $customerUserFacade, CustomerUserDataFactory $customerUserDataFactory, TokenFacade $tokenFacade)
        ```
    - `Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(FrontendCustomerUserProvider $frontendCustomerUserProvider, UserPasswordEncoderInterface $userPasswordEncoder, TokenFacade $tokenFacade)
        + __construct(FrontendCustomerUserProvider $frontendCustomerUserProvider, UserPasswordHasherInterface $userPasswordHasher, TokenFacade $tokenFacade)
        ```
    - `Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(EntityManagerInterface $em, AdministratorRepository $administratorRepository, AdministratorFactoryInterface $administratorFactory, AdministratorRoleFacade $administratorRoleFacade, EncoderFactoryInterface $encoderFactory, TokenStorageInterface $tokenStorage)
        + __construct(EntityManagerInterface $em, AdministratorRepository $administratorRepository, AdministratorFactoryInterface $administratorFactory, AdministratorRoleFacade $administratorRoleFacade, PasswordHasherFactoryInterface $passwordHasherFactory, TokenStorageInterface $tokenStorage)
        ```
    - `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(EntityManagerInterface $em, CustomerUserRepository $customerUserRepository, EncoderFactoryInterface $encoderFactory, ResetPasswordMailFacade $resetPasswordMailFacade, HashGenerator $hashGenerator, CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade)
        + __construct(EntityManagerInterface $em, CustomerUserRepository $customerUserRepository, PasswordHasherFactoryInterface $passwordHasherFactory, ResetPasswordMailFacade $resetPasswordMailFacade, HashGenerator $hashGenerator, CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade)
        ```
    - `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade` class:
      - method `__construct` changed its interface:
      ```diff
      - __construct(CustomerUserRefreshTokenChainDataFactoryInterface $customerUserRefreshTokenChainDataFactory, CustomerUserRefreshTokenChainFactoryInterface $customerUserRefreshTokenChainFactory, EncoderFactoryInterface $encoderFactory, CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository)
      + __construct(CustomerUserRefreshTokenChainDataFactoryInterface $customerUserRefreshTokenChainDataFactory, CustomerUserRefreshTokenChainFactoryInterface $customerUserRefreshTokenChainFactory, PasswordHasherFactoryInterface $passwordHasherFactory, CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository)
      ```
    - `Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade` class:
        - method `__construct` changed its interface:
      ```diff
      - __construct(FlashBagInterface $flashBag, EntityManagerInterface $em, CartWatcher $cartWatcher, CurrentCustomerUser $currentCustomerUser, Environment $twigEnvironment)
      + __construct(RequestStack $requestStack, EntityManagerInterface $em, CartWatcher $cartWatcher, CurrentCustomerUser $currentCustomerUser, Environment $twigEnvironment)
      ```
    - `Shopsys\FrameworkBundle\Component\Error\LogoutExceptionSubscriber` class:
        - method `__construct` changed its interface:
      ```diff
      - __construct(FlashBagInterface $flashBag, CurrentCustomerUser $currentCustomerUser, RouterInterface $router, Domain $domain)
      + __construct(RequestStack $requestStack, CurrentCustomerUser $currentCustomerUser, RouterInterface $router, Domain $domain)
      ```
    - also see #project-base-diff for more information about changes needed to be done in your project
- lock version of npm `jquery-ui` to version `1.12.1` in order to fix category sorting in admin ([#2558](https://github.com/shopsys/shopsys/pull/2558))
    - see #project-base-diff for more information about changes needed to be done in your project
- use `Shopsys\FrameworkBundle\Component\Translation\Translator::VALIDATOR_TRANSLATION_DOMAIN` for validators translations ()
    - `Shopsys\FrameworkBundle\Component\Translation\ConstraintMessageExtractor` class:
        - constant `CONSTRAINT_MESSAGE_DOMAIN` has been removed, use `Shopsys\FrameworkBundle\Component\Translation\Translator::VALIDATOR_TRANSLATION_DOMAIN` instead
    - also see #project-base-diff for more information about changes needed to be done in your project
- GQL resolvers and mutations refactoring ([#2563](https://github.com/shopsys/shopsys/pull/2563))
    - all frontend API `Resolver` classes were renamed to Query:
        - `Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdverPositionsResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdverPositionsResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdverPositionsQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdversResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdversResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdversQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticleResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticleResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticleQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticlesResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticlesResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticlesQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandsResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandsResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandsQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesSearchResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesSearchResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesSearchQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\User\CurrentCustomerUserResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\User\CurrentCustomerUserResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\User\CurrentCustomerUserQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Payment\PaymentResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Payment\PaymentResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Payment\PaymentQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Payment\PaymentsResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Payment\PaymentsResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Payment\PaymentsQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Price\PriceResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Price\PriceResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Price\PriceQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Products\PromotedProductsResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Products\PromotedProductsResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Products\PromotedProductsQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportsResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportsResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportsQuery
        ```
        - `Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportResolver` class was renamed:
        ```diff
        - Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportResolver
        + Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportQuery
        ```
    - All query method names in `Query` classes should end with suffix `Query` in order to be automatically aliased for use in GQL definitions
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdverPositionsQuery` class method was renamed:
        ```diff
        - public function resolve(): array
        + public function advertPositionsQuery(): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdversQuery` class method was renamed:
        ```diff
        - public function resolve(?string $positionName = null): array
        + public function advertsQuery(?string $positionName = null): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticleQuery` class methods were renamed:
        ```diff
        - public function resolver(?string $uuid = null, ?string $urlSlug = null): Article
        + public function articleByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): Article
        ```
        ```diff
        - public function termsAndConditionsArticle(): Article
        + public function termsAndConditionsArticleQuery(): Article
        ```
        ```diff
        - public function privacyPolicyArticle(): Article
        + public function privacyPolicyArticleQuery(): Article
        ```
        ```diff
        - public function cookiesArticle(): Article
        + public function cookiesArticleQuery(): Article
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Article\ArticlesQuery` class method was renamed:
        ```diff
        - public function resolve(Argument $argument, ?string $placement)
        + public function articlesQuery(Argument $argument, ?string $placement)
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery` class method was renamed:
        ```diff
        - public function resolver(?string $uuid = null, ?string $urlSlug = null): Brand
        + public function brandByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): Brand
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandsQuery` class method was renamed:
        ```diff
        - public function resolve(): array
        + public function brandsQuery(): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery` class method was renamed:
        ```diff
        - public function resolveByUuidOrUrlSlug(?string $uuid = null, ?string $urlSlug = null): Category
        + public function categoryByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): Category
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesQuery` class method was renamed:
        ```diff
        - public function resolve(): array
        + public function categoriesQuery(): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesSearchQuery` class method was renamed:
        ```diff
        - public function resolveSearch(Argument $argument)
        + public function categoriesSearchQuery(Argument $argument)
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\User\CurrentCustomerUserQuery` class method was renamed:
        ```diff
        - public function resolver(): CustomerUser
        + public function currentCustomerUserQuery(): CustomerUser
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesQuery` class are:
          - methods `resolveByCategory`, `resolveByPayment`, `resolveByTransport`, `resolveByBrand` were replaced by new universal method `imagesByEntityQuery`
          - renamed methods:
        ```diff
        - public function resolveByProduct($data, ?string $type, ?string $size): array
        + public function imagesByProductQuery($data, ?string $type, ?string $size): array
        ```
        ```diff
        - public function resolveByAdvert(Advert $advert, ?string $type, ?string $size): array
        + public function imagesByAdvertQuery(Advert $advert, ?string $type, ?string $size): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderQuery` class method was renamed:
        ```diff
        - public function resolver(?string $uuid = null, ?string $urlHash = null): Order
        + public function orderByUuidOrUrlHashQuery(?string $uuid = null, ?string $urlHash = null): Order
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersQuery` class method was renamed:
        ```diff
        - public function resolve(Argument $argument)
        + public function ordersQuery(Argument $argument)
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Payment\PaymentQuery` class method was renamed:
        ```diff
        - public function resolver(string $uuid): Payment
        + public function paymentQuery(string $uuid): Payment
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Payment\PaymentsQuery` class method was renamed:
        ```diff
        - public function resolve(): array
        + public function paymentsQuery(): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Price\PriceQuery` class methods were renamed:
        ```diff
        - public function resolveByProduct($data): ProductPrice
        + public function priceByProductQuery($data): ProductPrice
        ```
        ```diff
        - public function resolveByPayment(Payment $payment): Price
        + public function priceByPaymentQuery(Payment $payment): Price
        ```
        ```diff
        - public function resolveByTransport(Transport $transport): Price
        + public function priceByTransportQuery(Transport $transport): Price
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Products\PromotedProductsQuery` class method was renamed:
        ```diff
        - public function resolve(): array
        + public function promotedProductsQuery(): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsQuery` class methods were renamed:
        ```diff
        - public function resolve(Argument $argument)
        + public function productsQuery(Argument $argument)
        ```
        ```diff
        - public function resolveByCategory(Argument $argument, Category $category)
        + public function productsByCategoryQuery(Argument $argument, Category $category)
        ```
        ```diff
        - public function resolveByBrand(Argument $argument, Brand $brand)
        + public function productsByBrandQuery(Argument $argument, Brand $brand)
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductDetailQuery` class method was renamed:
        ```diff
        - public function resolver(?string $uuid = null, ?string $urlSlug = null): array
        + public function productDetailQuery(?string $uuid = null, ?string $urlSlug = null): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportsQuery` class method was renamed:
        ```diff
        - public function resolve(): array
        + public function transportsQuery(): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportQuery` class method was renamed:
        ```diff
        - public function resolver(string $uuid): Transport
        + public function transportByTransportUuidQuery(string $uuid): Transport
        ```
    - in all GQL queries resolve definitions are all old aliases replaced by renamed Query functions, e.g.:
        ```diff
        - resolve: '@=query("Shopsys\\FrontendApiBundle\\Model\\Resolver\\Image\\ImagesResolver::resolveByAdvert", value, args["type"], args["size"])'
        + resolve: '@=query("imagesByAdvertQuery", value, args["type"], args["size"])'
        ```
    - type definition of `images` in `AdvertImageDecorator`, `BrandDecorator` and `CategoryDecorator` has changed:
        ```diff
        - type: "[Image]"
        + type: "[Image!]!"
        ```
    - in GQL definition of `AdvertImageDecorator` is attribute `image` renamed to `images` and its type definition has changed:
        ```diff
        - image:
        -     type: "[Image]"
        + images:
        +     type: "[Image!]!"
        ```
    - All mutation methods in `*Mutation` classes should end with suffix `Mutation` in order to be automatically aliased for use in GQL definitions
        - in `Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation` class methods were renamed:
        ```diff
        - public function changePassword(Argument $argument, InputValidator $validator): CustomerUser
        + public function changePasswordMutation(Argument $argument, InputValidator $validator): CustomerUser
        ```
        ```diff
        - public function changePersonalData(Argument $argument, InputValidator $validator): CustomerUser
        + public function changePersonalDataMutation(Argument $argument, InputValidator $validator): CustomerUser
        ```
        ```diff
        - public function register(Argument $argument, InputValidator $validator): array
        + public function registerMutation(Argument $argument, InputValidator $validator): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation` class method was renamed:
        ```diff
        - public function login(Argument $argument): array
        + public function loginMutation(Argument $argument): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Mutation\Login\RefreshTokensMutation` class method was renamed:
        ```diff
        - public function refreshTokens(Argument $argument): array
        + public function refreshTokensMutation(Argument $argument): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Mutation\Logout\LogoutMutation` class method was renamed:
        ```diff
        - public function logout(): array
        + public function logoutMutation(): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Mutation\Newsletter\NewsletterMutation` class method was renamed:
        ```diff
        - public function newsletterSubscribe(Argument $argument, InputValidator $validator): array
        + public function newsletterSubscribeMutation(Argument $argument, InputValidator $validator): array
        ```
        - in `Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation` class method was renamed:
        ```diff
        - public function createOrder(Argument $argument, InputValidator $validator): Order
        + public function createOrderMutation(Argument $argument, InputValidator $validator): Order
        ```
    - in GQL mutation definitions are replaced all old aliases for renamed Mutation methods:
        ```diff
        - resolve: "@=mutation('create_order', args, validator)"
        + resolve: "@=mutation('createOrderMutation', args, validator)"
        ```
        ```diff
        - resolve: "@=mutation('user_login', args)"
        + resolve: "@=mutation('loginMutation', args)"
        ```
        ```diff
        - resolve: "@=mutation('user_logout')"
        + resolve: "@=mutation('logoutMutation')"
        ```
        ```diff
        - resolve: "@=mutation('refresh_tokens', args)"
        + resolve: "@=mutation('refreshTokensMutation', args)"
        ```
        ```diff
        - resolve: "@=mutation('customer_user_change_password', args, validator)"
        + resolve: "@=mutation('changePasswordMutation', args, validator)"
        ```
        ```diff
        - resolve: "@=mutation('customer_user_change_personal_data', args, validator)"
        + resolve: "@=mutation('changePersonalDataMutation', args, validator)"
        ```
        ```diff
        - resolve: "@=mutation('customer_user_register', args, validator)"
        + resolve: "@=mutation('registerMutation', args, validator)"
        ```
        ```diff
        - resolve: "@=mutation('newsletter_subscribe', args, validator)"
        + resolve: "@=mutation('newsletterSubscribeMutation', args, validator)"
        ```
        ```diff
        - resolve: "@=mutation('create_order', args, validator)"
        + resolve: "@=mutation('createOrderMutation', args, validator)"
        ```
        ```diff
        - resolve: "@=mutation('create_order', args, validator)"
        + resolve: "@=mutation('createOrderMutation', args, validator)"
        ```
    - in `services.yaml` class suffix `Resolver` replaced by suffix `Query` for autowiring classes. You should rename your Resolver classes to Query classes as well and update your `services.yaml`.
        ```diff
        - resource: '../../**/*{Facade,Factory,Mapper,Mutation,Repository,Resolver,Validator}.php'
        + resource: '../../**/*{Facade,Factory,Mapper,Mutation,Query,Repository,Validator}.php'
        ```
  - see #project-base-diff for more information about changes needed to be done in your project
- update your Elasticsearch Docker Image to latest version ([#2569](https://github.com/shopsys/shopsys/pull/2569))
    - see #project-base-diff for more information about changes needed to be done in your project
- add images names ([#2566](https://github.com/shopsys/shopsys/pull/2566))
    - update all `*DataFactory::createInstance()` methods that you have extended in your project and are related to entities with images to create instance of `ImageUploadData` same as it is done in their parent classes from Shopsys Framework
        - also update your own application code to use `ImageUploadDataFactory` instead of creating `ImageUploadData` object directly
    - `Shopsys\FrameworkBundle\Model\Advert\AdvertDataFactory` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(ImageFacade $imageFacade)
        + __construct(ImageUploadDataFactory $imageUploadDataFactory)
        ```
    - `Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(CategoryRepository $categoryRepository, FriendlyUrlFacade $friendlyUrlFacade, PluginCrudExtensionFacade $pluginCrudExtensionFacade, Domain $domain, ImageFacade $imageFacade)
        + __construct(CategoryRepository $categoryRepository, FriendlyUrlFacade $friendlyUrlFacade, PluginCrudExtensionFacade $pluginCrudExtensionFacade, Domain $domain, ImageUploadDataFactory $imageUploadDataFactory)
        ```
    - `Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(PaymentFacade $paymentFacade, VatFacade $vatFacade, Domain $domain, ImageFacade $imageFacade)
        + __construct(PaymentFacade $paymentFacade, VatFacade $vatFacade, Domain $domain, ImageUploadDataFactory $imageUploadDataFactory)
        ```
    - `Shopsys\FrameworkBundle\Model\Product\ProductDataFactory` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(VatFacade $vatFacade, ProductInputPriceFacade $productInputPriceFacade, UnitFacade $unitFacade, Domain $domain, ProductRepository $productRepository, ParameterRepository $parameterRepository, FriendlyUrlFacade $friendlyUrlFacade, ProductAccessoryRepository $productAccessoryRepository, ImageFacade $imageFacade, PluginCrudExtensionFacade $pluginDataFormExtensionFacade, ProductParameterValueDataFactoryInterface $productParameterValueDataFactory, PricingGroupFacade $pricingGroupFacade, AvailabilityFacade $availabilityFacade)
        + __construct(VatFacade $vatFacade, ProductInputPriceFacade $productInputPriceFacade, UnitFacade $unitFacade, Domain $domain, ProductRepository $productRepository, ParameterRepository $parameterRepository, FriendlyUrlFacade $friendlyUrlFacade, ProductAccessoryRepository $productAccessoryRepository, PluginCrudExtensionFacade $pluginDataFormExtensionFacade, ProductParameterValueDataFactoryInterface $productParameterValueDataFactory, PricingGroupFacade $pricingGroupFacade, AvailabilityFacade $availabilityFacade, ImageUploadDataFactory $imageUploadDataFactory)
        ```
    - `Shopsys\FrameworkBundle\Model\Product\Brand\BrandDataFactory` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(FriendlyUrlFacade $friendlyUrlFacade, BrandFacade $brandFacade, Domain $domain, ImageFacade $imageFacade)
        + __construct(FriendlyUrlFacade $friendlyUrlFacade, BrandFacade $brandFacade, Domain $domain, ImageUploadDataFactory $imageUploadDataFactory)
        ```
    - `Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactory` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(ImageFacade $imageFacade)
        + __construct(ImageUploadDataFactory $imageUploadDataFactory)
        ```
    - `Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(TransportFacade $transportFacade, VatFacade $vatFacade, Domain $domain, ImageFacade $imageFacade)
        + __construct(TransportFacade $transportFacade, VatFacade $vatFacade, Domain $domain, ImageUploadDataFactory $imageUploadDataFactory)
    - `Shopsys\FrameworkBundle\Component\Image\Image` class:
        - method `__construct` changed its interface:
        ```diff
        - __construct(string $entityName, int $entityId, ?string $type, ?string $temporaryFilename)
        + __construct(string $entityName, int $entityId, array $namesIndexedByLocale, ?string $temporaryFilename, ?string $type)
        ```
    - `Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface` interface and `Shopsys\FrameworkBundle\Component\Image\ImageFactory` class:
        - method `create` changed its interface:
        ```diff
        - create(string $entityName, int $entityId, ?string $type, string $temporaryFilename)
        + create(string $entityName, int $entityId, array $namesIndexedByLocale, string $temporaryFilename, ?string $type)
        ```
        - method `createMultiple` changed its interface:
        ```diff
        - createMultiple(ImageEntityConfig $imageEntityConfig, int $entityId, ?string $type, array $temporaryFilenames)
        + createMultiple(ImageEntityConfig $imageEntityConfig, int $entityId, array $names, array $temporaryFilenames, ?string $type)
        ```
    - `Shopsys\FrameworkBundle\Component\Image\ImageFacade` class:
        - method `uploadImage` changed its interface:
        ```diff
        - uploadImage($entity, $temporaryFilenames, $type)
        + uploadImage(object $entity, array $currentFilenamesIndexedByImageIdAndLocale, array $temporaryFilenamesIndexedByImageId, ?string $type)
        ```
        - method `uploadImages` changed its interface:
        ```diff
        - uploadImages($entity, $temporaryFilenames, $type)
        + uploadImages(object $entity, array $currentFilenamesIndexedByImageIdAndLocale, ?array $temporaryFilenamesIndexedByImageId, ?string $type)
        ```
    - also see #project-base-diff for more information about changes needed to be done in your project
- update abstract class of UserErrors in frontend API ([#])
    - `UserEntityNotFoundError` has been renamed to `EntityNotFoundUserError` update all your usages accordingly

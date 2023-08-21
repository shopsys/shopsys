# [Upgrade from v11.1.0 to v12.0.0-dev](https://github.com/shopsys/shopsys/compare/v11.1...12.0)

This guide contains instructions to upgrade from version v11.1.0 to v12.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/12.0/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- encapsulation of AdditionalImageData ([#1934](https://github.com/shopsys/shopsys/pull/1934))
    - `Shopsys\FrameworkBundle\Component\Image\AdditionalImageData`
        - type of property `$media` changed from having no type to `string`
        - type of property `$url` changed from having no type to `string`
- remove unused dependencies and attributes ([#1954](https://github.com/shopsys/shopsys/pull/1954))
    - see #project-base-diff to update your project
    - `Shopsys\FrontendApiBundle\Component\Constraints\PaymentTransportRelationValidator`
        - removed property `$domain`
        - removed property `$paymentPriceCalculation`
        - removed property `$currencyFacade`
    - `Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation`
        - removed property `$customerUserRefreshTokenChainFacade`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
                TokenStorageInterface $tokenStorage,
                protected readonly FrontendCustomerUserProvider $frontendCustomerUserProvider,
                protected readonly UserPasswordHasherInterface $userPasswordHasher,
                protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
        -       protected readonly CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
                protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
                protected readonly CustomerUserFacade $customerUserFacade,
                protected readonly CustomerUserDataFactory $customerUserDataFactory,
                protected readonly TokenFacade $tokenFacade,
            ) {
        ```
    - property `Shopsys\FrameworkBundle\Component\Translation\JsFileExtractor::$catalogue` was removed
    - `Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory`
        - removed property `$transportFacade`
        - removed property `$imageFacade`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
        -       protected TransportFacade $transportFacade,
                protected VatFacade $vatFacade,
                protected Domain $domain,
        -       protected ImageFacade $imageFacade,
                protected readonly ImageUploadDataFactory $imageUploadDataFactory,
            ) {
        ```
    - `Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory`
        - removed property `$categoryRepository`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
        -       protected readonly CategoryRepository $categoryRepository,
                protected readonly FriendlyUrlFacade $friendlyUrlFacade,
                protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
                protected readonly Domain $domain,
                protected readonly ImageUploadDataFactory $imageUploadDataFactory,
            ) {
        ```
    - `Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory`
        - removed property `$paymentFacade`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
        -       protected readonly PaymentFacade $paymentFacade,
                protected readonly VatFacade $vatFacade,
                protected readonly Domain $domain,
                protected readonly ImageUploadDataFactory $imageUploadDataFactory,
            ) {
        ```
    - `Shopsys\FrameworkBundle\Model\Payment\Grid\PaymentGridFactory`
        - removed property `$currencyFacade`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
                GridFactory $gridFactory,
                PaymentRepository $paymentRepository,
                Localization $localization,
                PaymentFacade $paymentFacade,
                AdminDomainTabsFacade $adminDomainTabsFacade,
        -       CurrencyFacade $currencyFacade
                AdminDomainTabsFacade $adminDomainTabsFacade
            ) {
        ```
    - `Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade`
        - removed property `$productRepository`
        - removed property `$domain`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
        -       protected readonly ProductRepository $productRepository,
                protected readonly ImageConfig $imageConfig,
                protected readonly ImageRepository $imageRepository,
                protected readonly ImageFacade $imageFacade,
                protected readonly FriendlyUrlRepository $friendlyUrlRepository,
                protected readonly ParameterRepository $parameterRepository,
        -       protected readonly Domain $domain,
                protected readonly FriendlyUrlFacade $friendlyUrlFacade,
            ) {
        ```
    - `Shopsys\FrameworkBundle\Model\Product\Brand\BrandDataFactory`
        - removed property `$brandFacade`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
                protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        -       protected readonly BrandFacade $brandFacade,
                protected readonly Domain $domain,
                protected readonly ImageUploadDataFactory $imageUploadDataFactory,
            ) {
        ```
    - `Shopsys\FrameworkBundle\Model\Product\ProductFacade`
        - removed property `$availabilityFacade`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
                EntityManagerInterface $em,
                ProductRepository $productRepository,
                ProductVisibilityFacade $productVisibilityFacade,
                ParameterRepository $parameterRepository,
                Domain $domain,
                ImageFacade $imageFacade,
                ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
                PricingGroupRepository $pricingGroupRepository,
                ProductManualInputPriceFacade $productManualInputPriceFacade,
                ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
                FriendlyUrlFacade $friendlyUrlFacade,
                ProductHiddenRecalculator $productHiddenRecalculator,
                ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
                ProductAccessoryRepository $productAccessoryRepository,
        -       AvailabilityFacade $availabilityFacade,
                PluginCrudExtensionFacade $pluginCrudExtensionFacade,
                ProductFactoryInterface $productFactory,
                ProductAccessoryFactoryInterface $productAccessoryFactory,
                ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
                ProductParameterValueFactoryInterface $productParameterValueFactory,
                ProductVisibilityFactoryInterface $productVisibilityFactory,
                ProductPriceCalculation $productPriceCalculation,
                ProductExportScheduler $productExportScheduler
            ) {
        ```
        - `Shopsys\FrameworkBundle\Model\Product\ProductDataFactory`
            - removed property `$productRepository`
            - constructor `__construct()` changed its interface
            ```diff
                public function __construct(
                    protected readonly VatFacade $vatFacade,
                    protected readonly ProductInputPriceFacade $productInputPriceFacade,
                    protected readonly UnitFacade $unitFacade,
                    protected readonly Domain $domain,
            -       protected readonly ProductRepository $productRepository,
                    protected readonly ParameterRepository $parameterRepository,
                    protected readonly FriendlyUrlFacade $friendlyUrlFacade,
                    protected readonly ProductAccessoryRepository $productAccessoryRepository,
                    protected readonly PluginCrudExtensionFacade $pluginDataFormExtensionFacade,
                    protected readonly ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
                    protected readonly PricingGroupFacade $pricingGroupFacade,
                    protected readonly AvailabilityFacade $availabilityFacade,
                    protected readonly ImageUploadDataFactory $imageUploadDataFactory,
                ) {
            ```
    - `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade`
        - removed property `$currencyFacade`
        - removed property `$domainFacade`
        - removed property `$pricingGroupFacade`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
                EntityManagerInterface $em,
        -       CurrencyFacade $currencyFacade,
                PricingSetting $pricingSetting,
                ProductManualInputPriceRepository $productManualInputPriceRepository,
        -       PricingGroupFacade $pricingGroupFacade,
                ProductRepository $productRepository,
                ProductInputPriceRecalculator $productInputPriceRecalculator
            ) {
        ```
    - `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository`
        - removed property `$domain`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
                EntityManagerInterface $em,
                ParameterRepository $parameterRepository,
                ProductFacade $productFacade,
                FriendlyUrlRepository $friendlyUrlRepository,
        -       Domain $domain,
                ProductVisibilityRepository $productVisibilityRepository,
                FriendlyUrlFacade $friendlyUrlFacade,
                CategoryFacade $categoryFacade,
                ProductAccessoryFacade $productAccessoryFacade,
                BrandCachedFacade $brandCachedFacade
            ) {
        ```
    - property `Shopsys\FrameworkBundle\Model\Feed\FeedExport::$fileContentBuffer` was removed
    - property `Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade::$authorizationChecker` was removed
    - `Shopsys\FrameworkBundle\Model\Pricing\Rounding`
        - removed property `$pricingSetting`
        - removed constructor `__construct()`
    - property `Shopsys\FrameworkBundle\Model\Pricing\PricingSetting::$productPriceRecalculationScheduler` was removed
    - `Shopsys\FrameworkBundle\Model\Pricing\Vat\VatGridFactory`
        - removed property `$priceCalculation`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
                EntityManagerInterface $em,
                GridFactory $gridFactory,
                VatFacade $vatFacade,
        -       PriceCalculation $priceCalculation,
                AdminDomainTabsFacade $adminDomainTabsFacade
            ) {
        ```
    - `Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade`
        - removed property `$paymentRepository`
        - removed property `$transportRepository`
        - removed property `$paymentPriceFactory`
        - removed property `$transportPriceFactory`
        - removed property `$vatFacade`
        - type of property `$em` changed from having no type to `Doctrine\ORM\EntityManagerInterface`
        - type of property `$currencyRepository` changed from having no type to `Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyRepository`
        - type of property `$pricingSetting` changed from having no type to `Shopsys\FrameworkBundle\Model\Pricing\PricingSetting`
        - type of property `$orderRepository` changed from having no type to `Shopsys\FrameworkBundle\Model\Order\OrderRepository`
        - type of property `$domain` changed from having no type to `Shopsys\FrameworkBundle\Component\Domain\Domain`
        - type of property `$productPriceRecalculationScheduler` changed from having no type to `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler`
        - type of property `$currencyFactory` changed from having no type to `Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFactoryInterface`
        - constructor `__construct()` changed its interface
        ```diff
            public function __construct(
        -       EntityManagerInterface $em,
        +       protected readonly EntityManagerInterface $em,
        -       CurrencyRepository $currencyRepository,
        +       protected readonly CurrencyRepository $currencyRepository,
        -       PricingSetting $pricingSetting,
        +       protected readonly PricingSetting $pricingSetting,
        -       OrderRepository $orderRepository,
        +       protected readonly OrderRepository $orderRepository,
        -       Domain $domain,
        +       protected readonly Domain $domain,
        -       ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        +       protected readonly ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        -       PaymentRepository $paymentRepository,
        -       TransportRepository $transportRepository,
        -       PaymentPriceFactoryInterface $paymentPriceFactory,
        -       TransportPriceFactoryInterface $transportPriceFactory,
        -       CurrencyFactoryInterface $currencyFactory,
        +       protected readonly CurrencyFactoryInterface $currencyFactory
        -       VatFacade $vatFacade
            ) {
        ```
    - `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade`
        - removed property `$billingAddressFactory`
        - constructor `__construct` changed its interface
        ```diff
            public function __construct(
                EntityManagerInterface $em,
                CustomerUserRepository $customerUserRepository,
                CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
                CustomerMailFacade $customerMailFacade,
        -       BillingAddressFactoryInterface $billingAddressFactory,
                BillingAddressDataFactoryInterface $billingAddressDataFactory,
                CustomerUserFactoryInterface $customerUserFactory,
                CustomerUserPasswordFacade $customerUserPasswordFacade,
                CustomerFacade $customerFacade,
                DeliveryAddressFacade $deliveryAddressFacade,
                CustomerDataFactoryInterface $customerDataFactory,
                BillingAddressFacade $billingAddressFacade,
                CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
            ) {
        ```
    - property `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory::$customerFactory` was removed
    - `Shopsys\FrameworkBundle\Twig\PriceExtension`
        - removed property `$numberFormatRepository`
        - constructor `__construct` changed its interface
        ```diff
            public function __construct(
                CurrencyFacade $currencyFacade,
                Domain $domain,
                Localization $localization,
        -       NumberFormatRepositoryInterface $numberFormatRepository,
                CurrencyRepositoryInterface $intlCurrencyRepository,
                CurrencyFormatterFactory $currencyFormatterFactory
            ) {
        ```
- add support for CDN ([#2602](https://github.com/shopsys/shopsys/pull/2602))
    - constructor `Shopsys\FrameworkBundle\Form\WysiwygTypeExtension::__construct()` changed its interface
        ```diff
            public function __construct(
        -       Domain $domain,
        +       private readonly Domain $domain,
        -       Localization $localization,
        +       private readonly Localization $localization,
        -       string $entrypointsPath
        +       private readonly string $entrypointsPath,
        +       private readonly WysiwygCdnDataTransformer $wysiwygCdnDataTransformer,
            ) {
        ```
    - return type of `Shopsys\FrameworkBundle\Form\WysiwygTypeExtension::configureOptions()` changed from no type to `void`
    - `Shopsys\FrameworkBundle\Component\Image\ImageFacade`
        - type of property `$em` changed from having no type to `Doctrine\ORM\EntityManagerInterface`
        - type of property `$imageConfig` changed from having no type to `Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig`
        - type of property `$imageRepository` changed from having no type to `Shopsys\FrameworkBundle\Component\Image\ImageRepository`
        - type of property `$filesystem` changed from having no type to `League\Flysystem\FilesystemOperator`
        - type of property `$mountManager` changed from having no type to `League\Flysystem\MountManager`
        - type of property `$fileUpload` changed from having no type to `Shopsys\FrameworkBundle\Component\FileUpload\FileUpload`
        - type of property `$imageLocator` changed from having no type to `Shopsys\FrameworkBundle\Component\Image\ImageLocator`
        - type of property `$imageUrlPrefix` changed from having no type to `string`
        - type of property `$imageFactory` changed from having no type to `Shopsys\FrameworkBundle\Component\Image\ImageFactoryInterface`
        - method `__construct()` changed its interface
            ```diff
                public function __construct(
            -       $imageUrlPrefix,
            +       protected readonly string $imageUrlPrefix,
            -       EntityManagerInterface $em,
            +       protected readonly EntityManagerInterface $em,
            -       ImageConfig $imageConfig,
            +       protected readonly ImageConfig $imageConfig,
            -       ImageRepository $imageRepository,
            +       protected readonly ImageRepository $imageRepository,
            -       FilesystemOperator $filesystem,
            +       protected readonly FilesystemOperator $filesystem,
            -       FileUpload $fileUpload,
            +       protected readonly FileUpload $fileUpload,
            -       ImageLocator $imageLocator,
            +       protected readonly ImageLocator $imageLocator,
            -       ImageFactoryInterface $imageFactory,
            +       protected readonly ImageFactoryInterface $imageFactory,
            -       MountManager $mountManager,
            +       protected readonly MountManager $mountManager,
            -       LoggerInterface $logger
            +       protected readonly LoggerInterface $logger,
            +       protected readonly CdnFacade $cdnFacade,
                ) {
            ```
    - see #project-base-diff to update your project
- added typehints and return types to `Shopsys\FrameworkBundle\Component\Image\ImageFacade` ([#1935](https://github.com/shopsys/shopsys/pull/1935))
    - parameter `$orderedImages` of `saveImageOrdering()` changed from no type to `array`
    - parameter `$entity` of `deleteImages()` changed from no type to `object`
    - return type of `getImageByEntity()` changed from no type to `Shopsys\FrameworkBundle\Component\Image\Image`
    - parameter `$entity` of `getImageByEntity()` changed from no type to `object`
    - parameter `$type` of `getImageByEntity()` changed from no type to `string|null`
    - return type of `getImagesByEntityIndexedById()` changed from no type to `array`
    - parameter `$entity` of `getImagesByEntityIndexedById()` changed from no type to `object`
    - parameter `$type` of `getImagesByEntityIndexedById()` changed from no type to `string|null`
    - return type of `getImagesByEntityIdAndNameIndexedById()` changed from no type to `array`
    - parameter `$type` of `getImagesByEntityIdAndNameIndexedById()` changed from no type to `string|null`
    - return type of `getAllImagesByEntity()` changed from no type to `array`
    - parameter `$entity` of `getAllImagesByEntity()` changed from no type to `object`
    - return type of `deleteImageFiles()` changed from no type to `void`
    - return type of `getEntityId()` changed from no type to `int`
    - parameter `$entity` of `getEntityId()` changed from no type to `object`
    - return type of `getAllImageEntityConfigsByClass()` changed from no type to `array`
    - return type of `getImageUrl()` changed from no type to `string`
    - parameter `$imageOrEntity` of `getImageUrl()` changed from no type to `object`
    - parameter `$sizeName` of `getImageUrl()` changed from no type to `string|null`
    - parameter `$type` of `getImageUrl()` changed from no type to `string|null`
    - return type of `getAdditionalImagesData()` changed from no type to `array`
    - parameter `$imageOrEntity` of `getAdditionalImagesData()` changed from no type to `object`
    - return type of `getAdditionalImageUrl()` changed from no type to `string`
    - return type of `getImageByObject()` changed from no type to `Shopsys\FrameworkBundle\Component\Image\Image`
    - parameter `$imageOrEntity` of `getImageByObject()` changed from no type to `object`
    - parameter `$type` of `getImageByObject()` changed from no type to `string|null`
    - return type of `getById()` changed from no type to `Shopsys\FrameworkBundle\Component\Image\Image`
    - parameter `$imageId` of `getById()` changed from no type to `int`
    - return type of `copyImages()` changed from no type to `void`
    - parameter `$sourceEntity` of `copyImages()` changed from no type to `object`
    - parameter `$targetEntity` of `copyImages()` changed from no type to `object`
    - return type of `setImagePositionsByOrder()` changed from no type to `void`
    - parameter `$orderedImages` of `setImagePositionsByOrder()` changed from no type to `array`

- improvements ([#2609](https://github.com/shopsys/shopsys/pull/2609))
    - `Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade` class:
        - type of property `$errorPagesDir` changed from having no type to `string`
        - type of property `$domain` changed from having no type to `Shopsys\FrameworkBundle\Component\Domain\Domain`
        - type of property `$domainRouterFactory` changed from having no type to `Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory`
        - type of property `$errorIdProvider` changed from having no type to `Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider`
        - property `$filesystem` was removed
        - constructor `__construct` changed its interface
        ```diff
            public function __construct(
        -       $errorPagesDir,
        +       protected readonly string $errorPagesDir,
        -       Domain $domain,
        +       protected readonly Domain $domain,
        -       DomainRouterFactory $domainRouterFactory,
        +       protected readonly DomainRouterFactory $domainRouterFactory,
        -       Filesystem $filesystem,
        -       ErrorIdProvider $errorIdProvider
        +       protected readonly ErrorIdProvider $errorIdProvider,
        +       protected readonly FilesystemOperator $mainFilesystem,
            ) {
        ```
    - return type of `generateAllErrorPagesForProduction()` changed from no type to `void`
    - return type of `getErrorPageContentByDomainIdAndStatusCode()` changed from no type to `string`
    - parameter `$domainId` of `getErrorPageContentByDomainIdAndStatusCode()` changed from no type to `int`
    - parameter `$statusCode` of `getErrorPageContentByDomainIdAndStatusCode()` changed from no type to `int`
    - return type of `getErrorPageStatusCodeByStatusCode()` changed from no type to `int`
    - parameter `$statusCode` of `getErrorPageStatusCodeByStatusCode()` changed from no type to `int`
    - return type of `generateAndSaveErrorPage()` changed from no type to `void`
    - parameter `$domainId` of `generateAndSaveErrorPage()` changed from no type to `int`
    - parameter `$statusCode` of `generateAndSaveErrorPage()` changed from no type to `int`
    - return type of `getErrorPageFilename()` changed from no type to `string`
    - parameter `$domainId` of `getErrorPageFilename()` changed from no type to `int`
    - parameter `$statusCode` of `getErrorPageFilename()` changed from no type to `int`
    - return type of `getUrlContent()` changed from no type to `string`
    - parameter `$errorPageUrl` of `getUrlContent()` changed from no type to `string`
    - parameter `$expectedStatusCode` of `getUrlContent()` changed from no type to `int`
    - maintenance mode is now detected by checking presence of `maintenance` key in Redis instead of `MAINTENANCE` file in root directory
    - maintenance page template is now standard twig template (`@ShopsysFramework/Common/maintenance.html.twig`) instead of php file
    - class `Shopsys\FrameworkBundle\Command\CommandResultCodes` was removed
    - constructor `CronCommand` changed its interface
    ```diff
        public function __construct(
    -       CronFacade $cronFacade,
    +       protected readonly CronFacade $cronFacade,
    -       MutexFactory $mutexFactory,
    +       protected readonly MutexFactory $mutexFactory,
    -       ParameterBagInterface $parameterBag
    +       protected readonly ParameterBagInterface $parameterBag,
    +       protected readonly LockInterface $lock,
        ) {
    ```
    - see #project-base-diff for more details
- fix deprecated usage of required parameters after optional parameters
    - `Shopsys\ReadModelBundle\Product\Listed\ListedProductView` class:
        - method `__construct` changed order of its parameters, new order is like this:
        ```diff
            public function __construct(
                protected readonly int $id,
                protected readonly string $name,
                protected readonly string $availability,
                protected readonly ProductPrice $sellingPrice,
                protected readonly ProductActionView $action,
                protected readonly ?ImageView $image = null,
                protected readonly ?string $shortDescription = null,
                protected readonly array $flagIds = [],
            )
        ```
    - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory` class:
        - method `create` changed order of its parameters, new order is like this:
        ```diff
            protected function create(
                int $id,
                string $name,
                string $availability,
                ProductPrice $sellingPrice,
                ProductActionView $action,
                ?ImageView $image,
                ?string $shortDescription,
                array $flagIds = [],
            )
        ```
    - `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData` class:
        - method `__construct` changed order of its parameters, new order is like this:
        ```diff
            public function __construct(
                public BillingAddressData $billingAddressData,
                public CustomerUserData $customerUserData,
                public ?DeliveryAddressData $deliveryAddressData,
            )
        ```
    - `Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry` class:
        - method `__construct` changed order of its parameters, new order is like this:
        ```diff
            public function __construct(
                protected readonly string $frameworkRootDir,
                protected readonly array $entityExtensionMap = []
            )
        ```
    - `Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItem` class:
        - method `__construct` changed order of its parameters, new order is like this:
        ```diff
            public function __construct(
                protected readonly int $id,
                protected readonly string $name,
                protected readonly bool $sellingDenied,
                protected readonly Price $price,
                protected readonly Currency $currency,
                protected readonly string $url,
                protected readonly ?string $brandName,
                protected readonly ?string $description,
                protected readonly ?string $ean = null,
                protected readonly ?string $partno = null,
                protected readonly ?string $imgUrl = null,
            )
        ```
    - `Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItem` class:
        - method `__construct` changed order of its parameters, new order is like this:
        ```diff
            public function __construct(
                protected readonly int $id,
                protected readonly string $name,
                protected readonly array $parametersByName,
                protected readonly string $url,
                protected readonly Price $price,
                protected readonly ?int $mainVariantId = null,
                protected readonly ?string $description = null,
                protected readonly ?string $imgUrl = null,
                protected readonly ?string $brandName = null,
                protected readonly ?string $ean = null,
                protected readonly ?int $availabilityDispatchTime = null,
                protected readonly ?string $heurekaCategoryFullName = null,
                protected readonly ?Money $cpc = null
            )
        ```
    - `Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItem` class:
        - method `__construct` changed order of its parameters, new order is like this:
        ```diff
            public function __construct(
                protected readonly int $id,
                protected readonly string $name,
                protected readonly string $url,
                protected readonly Price $price,
                protected readonly array $pathToMainCategory,
                protected readonly array $parametersByName,
                protected readonly ?int $mainVariantId = null,
                protected readonly ?string $description = null,
                protected readonly ?string $imgUrl = null,
                protected readonly ?string $brandName = null,
                protected readonly ?string $ean = null,
                protected readonly ?string $partno = null,
                protected readonly ?int $availabilityDispatchTime = null,
                protected readonly ?Money $cpc = null,
                protected readonly ?Money $cpcSearch = null
            )
        ```
    - `Shopsys\ReadModelBundle\Product\Detail\ProductDetailView` class:
        - method `__construct` changed order of its parameters, new order is like this:
        ```diff
            public function __construct(
                protected readonly int $id,
                protected readonly ?string $name,
                protected readonly ?string $description,
                protected readonly string $availability,
                protected readonly ?string $catnum,
                protected readonly ?string $partno,
                protected readonly ?string $ean,
                protected readonly bool $isSellingDenied,
                protected readonly bool $isInStock,
                protected readonly bool $isMainVariant,
                protected readonly array $flagIds,
                protected readonly ?string $seoPageTitle,
                protected readonly ?string $seoMetaDescription,
                protected readonly ProductActionView $actionView,
                protected readonly array $galleryImageViews,
                protected readonly array $parameterViews,
                protected readonly array $accessories,
                protected readonly array $variants,
                protected readonly ?ProductPrice $sellingPrice = null,
                protected readonly ?int $mainCategoryId = null,
                protected readonly ?int $mainVariantId = null,
                protected readonly ?BrandView $brandView = null,
                protected readonly ?ImageView $mainImageView = null,
            )
        ```
    - `Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig` class:
        - method `__construct` changed order of its parameters, new order is like this:
        ```diff
            public function __construct(
                protected readonly string $media,
                protected readonly ?int $width = null, 
                protected readonly ?int $height = null, 
            )
        ```
- apply new coding standards in your application
   - run `php phing standards-fix` multiple times after no problems are found or after only problems that need to be fixed manually are found
       - fix all manually fixable problems
   - run `php phing phpstan` and fix all problems found
   - see #project-base-diff to see changes that you should consider applying to your project
   - fix deprecated usage of required parameters after optional parameters
       - `Shopsys\ReadModelBundle\Product\Listed\ListedProductView` class:
           - method `__construct` changed order of its parameters, new order is like this:
           ```diff
               public function __construct(
                   protected readonly int $id,
                   protected readonly string $name,
                   protected readonly string $availability,
                   protected readonly ProductPrice $sellingPrice,
                   protected readonly ProductActionView $action,
                   protected readonly ?ImageView $image = null,
                   protected readonly ?string $shortDescription = null,
                   protected readonly array $flagIds = [],
               )
           ```
       - `Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory` class:
           - method `create` changed order of its parameters, new order is like this:
           ```diff
               protected function create(
                   int $id,
                   string $name,
                   string $availability,
                   ProductPrice $sellingPrice,
                   ProductActionView $action,
                   ?ImageView $image,
                   ?string $shortDescription,
                   array $flagIds = [],
               )
           ```
       - `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData` class:
           - method `__construct` changed order of its parameters, new order is like this:
           ```diff
               public function __construct(
                   public BillingAddressData $billingAddressData,
                   public CustomerUserData $customerUserData,
                   public ?DeliveryAddressData $deliveryAddressData,
               )
           ```
       - `Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry` class:
           - method `__construct` changed order of its parameters, new order is like this:
           ```diff
               public function __construct(
                   protected readonly string $frameworkRootDir,
                   protected readonly array $entityExtensionMap = []
               )
           ```
       - `Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItem` class:
           - method `__construct` changed order of its parameters, new order is like this:
           ```diff
               public function __construct(
                   protected readonly int $id,
                   protected readonly string $name,
                   protected readonly bool $sellingDenied,
                   protected readonly Price $price,
                   protected readonly Currency $currency,
                   protected readonly string $url,
                   protected readonly ?string $brandName,
                   protected readonly ?string $description,
                   protected readonly ?string $ean = null,
                   protected readonly ?string $partno = null,
                   protected readonly ?string $imgUrl = null,
               )
           ```
       - `Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItem` class:
           - method `__construct` changed order of its parameters, new order is like this:
           ```diff
               public function __construct(
                   protected readonly int $id,
                   protected readonly string $name,
                   protected readonly array $parametersByName,
                   protected readonly string $url,
                   protected readonly Price $price,
                   protected readonly ?int $mainVariantId = null,
                   protected readonly ?string $description = null,
                   protected readonly ?string $imgUrl = null,
                   protected readonly ?string $brandName = null,
                   protected readonly ?string $ean = null,
                   protected readonly ?int $availabilityDispatchTime = null,
                   protected readonly ?string $heurekaCategoryFullName = null,
                   protected readonly ?Money $cpc = null
               )
           ```
       - `Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItem` class:
           - method `__construct` changed order of its parameters, new order is like this:
           ```diff
               public function __construct(
                   protected readonly int $id,
                   protected readonly string $name,
                   protected readonly string $url,
                   protected readonly Price $price,
                   protected readonly array $pathToMainCategory,
                   protected readonly array $parametersByName,
                   protected readonly ?int $mainVariantId = null,
                   protected readonly ?string $description = null,
                   protected readonly ?string $imgUrl = null,
                   protected readonly ?string $brandName = null,
                   protected readonly ?string $ean = null,
                   protected readonly ?string $partno = null,
                   protected readonly ?int $availabilityDispatchTime = null,
                   protected readonly ?Money $cpc = null,
                   protected readonly ?Money $cpcSearch = null
               )
           ```
       - `Shopsys\ReadModelBundle\Product\Detail\ProductDetailView` class:
           - method `__construct` changed order of its parameters, new order is like this:
           ```diff
               public function __construct(
                   protected readonly int $id,
                   protected readonly ?string $name,
                   protected readonly ?string $description,
                   protected readonly string $availability,
                   protected readonly ?string $catnum,
                   protected readonly ?string $partno,
                   protected readonly ?string $ean,
                   protected readonly bool $isSellingDenied,
                   protected readonly bool $isInStock,
                   protected readonly bool $isMainVariant,
                   protected readonly array $flagIds,
                   protected readonly ?string $seoPageTitle,
                   protected readonly ?string $seoMetaDescription,
                   protected readonly ProductActionView $actionView,
                   protected readonly array $galleryImageViews,
                   protected readonly array $parameterViews,
                   protected readonly array $accessories,
                   protected readonly array $variants,
                   protected readonly ?ProductPrice $sellingPrice = null,
                   protected readonly ?int $mainCategoryId = null,
                   protected readonly ?int $mainVariantId = null,
                   protected readonly ?BrandView $brandView = null,
                   protected readonly ?ImageView $mainImageView = null,
               )
           ```
       - `Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig` class:
           - method `__construct` changed order of its parameters, new order is like this:
           ```diff
               public function __construct(
                   protected readonly string $media,
                   protected readonly ?int $width = null, 
                   protected readonly ?int $height = null, 
               )
           ```
   - first unused parameter `$message` from `Shopsys\FrameworkBundle\Component\Breadcrumb\Exception\UnableToGenerateBreadcrumbItemsException` exception constructor has been removed, update you code appropriately
   - see #project-base-diff to add required configurations to your project and check suggested changes to your project

## Removed deprecations

- check that your code don't use any removed code ([#2719](https://github.com/shopsys/shopsys/pull/2719))
    - `Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade`
        - method `__construct`  changed its interface:
            ```diff
                public function __construct(
                    protected readonly EntityManagerInterface $em,
                    protected readonly CronModuleRepository $cronModuleRepository,
                    protected readonly CronFilter $cronFilter,
            -       protected ?CronModuleRunFactory $cronModuleRunFactory = null,
            +       protected readonly CronModuleRunFactory $cronModuleRunFactory,
                )
            ```
    - `Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor`
        - property `$canRunTo` was removed
        - method `__construct`  changed its interface:
            ```diff
                public function __construct(
            -       int $secondsTimeout,
            -       protected ?CronConfig $cronConfig = null,
            +       protected readonly CronConfig $cronConfig,
                )
            ```
        - method `canRun` changed its interface
            ```
            -   public function canRun(): bool
            +   public function canRun(CronModuleInterface $cronModule): bool
            ```
    - `Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig`
        - method `registerCronModuleInstance` changed its interface
            ```diff
                public function registerCronModuleInstance(
                    $service,
                    string $serviceId,
                    string $timeHours,
                    string $timeMinutes,
                    string $instanceName,
                    ?string $readableName = null,
            +       int $runEveryMin = CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
            +       int $timeoutIteratedCronSec = CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
                ): void
            ```
    - `Shopsys\FrameworkBundle\Controller\Admin\DefaultController`
        - constant `HOUR_IN_SECONDS` was removed
        - method `getFormattedDuration()` was removed
        - method `__construct`  changed its interface:
            ```diff
                public function __construct(
                    protected readonly StatisticsFacade $statisticsFacade,
                    protected readonly StatisticsProcessingFacade $statisticsProcessingFacade,
                    protected readonly MailTemplateFacade $mailTemplateFacade,
                    protected readonly UnitFacade $unitFacade,
                    protected readonly Setting $setting,
                    protected readonly AvailabilityFacade $availabilityFacade,
                    protected readonly CronModuleFacade $cronModuleFacade,
                    protected readonly GridFactory $gridFactory,
                    protected readonly CronConfig $cronConfig,
                    protected readonly CronFacade $cronFacade,
            -       protected ?BreadcrumbOverrider $breadcrumbOverrider = null,
            -       protected ?DateTimeFormatterExtension $dateTimeFormatterExtension = null,
            +       protected readonly BreadcrumbOverrider $breadcrumbOverrider,
            +       protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
                )
            ```
    - ENV variable `MAILER_DELIVERY_WHITELIST` was removed
    - ENV variable `MAILER_MASTER_EMAIL_ADDRESS` was removed
    - `Shopsys\FrameworkBundle\Controller\Admin\SuperadminController`
        - method `__construct`  changed its interface:
            ```diff
                public function __construct(
                    protected readonly ModuleList $moduleList,
                    protected readonly ModuleFacade $moduleFacade,
                    protected readonly PricingSetting $pricingSetting,
                    protected readonly DelayedPricingSetting $delayedPricingSetting,
                    protected readonly GridFactory $gridFactory,
                    protected readonly Localization $localization,
                    protected readonly LocalizedRouterFactory $localizedRouterFactory,
            -       protected ?MailSettingFacade $mailSettingFacade = null,
            -       protected ?MailerSettingProvider $mailerSettingProvider = null,
            -       protected ?AdminDomainTabsFacade $adminDomainTabsFacade = null,
            +       protected readonly MailSettingFacade $mailSettingFacade,
            +       protected readonly MailerSettingProvider $mailerSettingProvider,
            +       protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
                )
            ```
        - variable `isOverridden` is no longer passed to `@ShopsysFramework/Admin/Content/Superadmin/mailWhitelist.html.twig` template while rendering `mailWhitelistAction`
    - `Shopsys\FrameworkBundle\Model\Mail\EventListener\EnvelopeListener`
        - method `getAllowedRecipients` was removed
    - class `Shopsys\FrameworkBundle\Model\Mail\Exception\MasterMailNotSetException` was removed
    - `Shopsys\FrameworkBundle\Model\Mail\Mailer`
        - method `send` was removed, use `sendForDomain` instead
        - method `getMessageWithReplacedVariables` changed its interface
            ```diff
                protected function getMessageWithReplacedVariables(
                    MessageData $messageData,
            +       int $domainId,
            -   ): Symfony\Component\Mime\Email {
            +   ): Shopsys\FrameworkBundle\Model\Mail\Email
            ```
    - `Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider`
        - property `$mailerWhitelistExpressions` was removed
        - property `$mailerMasterEmailAddress` was removed
        - method `getMailerWhitelistExpressions` was removed
        - method `isMailerWhitelistExpressionsSet` was removed
        - method `getMailerMasterEmailAddress` was removed
        - method `isMailerMasterEmailSet` was removed
        - method `__construct` changed its interface
            ```diff
                public function __construct(
            -       string $mailerWhitelist,
            -       string $mailerMasterEmailAddress,
                    string $mailerDsn,
            -       protected readonly ?bool $whitelistForced = null,
            -       protected ?MailSettingFacade $mailSettingFacade = null,
            +       protected readonly bool $whitelistForced,
            +       protected readonly MailSettingFacade $mailSettingFacade,
                )
            ```
    - method `Shopsys\FrameworkBundle\Model\Advert\AdvertRepository::getAdvertByPositionQueryBuilder()` now throws exception when `POSITION_PRODUCT_LIST` is requested without category
    - `Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdvertsQuery`
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    protected readonly AdvertFacade $advertFacade,
                    protected readonly Domain $domain,
            -       protected ?CategoryFacade $categoryFacade = null,
            +       protected readonly CategoryFacade $categoryFacade,
                )
            ```
        - method `advertsQuery` now throws exception when `POSITION_PRODUCT_LIST` is requested without category
    - `Shopsys\FrameworkBundle\Model\Product\Search\AggregationResultToProductFilterCountDataTransformer`
        - method `translateFlagsPlusNumbers` was removed, use `getFlagCount` instead
        - method `translateBrandsPlusNumbers` was removed, use `getBrandCount` instead
        - method `getFlagCount` is now public
        - method `getBrandCount` is now public
    - `Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository`
        - second parameter in `calculateBrandsPlusNumbers` changed its name from `plusFlagsQuery` to `plusBrandsQuery`
    - `Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation`
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    protected readonly FrontendCustomerUserProvider $frontendCustomerUserProvider,
                    protected readonly UserPasswordHasherInterface $userPasswordHasher,
                    protected readonly TokenFacade $tokenFacade,
            -       protected ?DefaultLoginRateLimiter $loginRateLimiter = null,
            -       protected ?RequestStack $requestStack = null,
            +       protected readonly DefaultLoginRateLimiter $loginRateLimiter,
            +       protected readonly RequestStack $requestStack,
                )
            ```
    - remove setter injection in `App\Controller\Front\RobotsController`
        - see #project-base-diff for more details

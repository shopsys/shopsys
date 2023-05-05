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
    - constructor `CronCommand` changed its interface
    - class `Shopsys\FrameworkBundle\Command\CommandResultCodes` was removed
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

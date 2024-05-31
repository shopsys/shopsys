<?php

use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff;
use PHP_CodeSniffer\Standards\PSR2\Sniffs\Methods\MethodDeclarationSniff;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use Shopsys\CodingStandards\Sniffs\ForbiddenDoctrineInheritanceSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenDumpSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenSuperGlobalSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Shopsys\CodingStandards\Sniffs\ValidVariableNameSniff;
use SlevomatCodingStandard\Sniffs\Classes\ClassLengthSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DeprecatedAnnotationDeclarationSniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use Symplify\CodingStandard\Fixer\Commenting\RemoveUselessDefaultCommentFixer;

return [
    __DIR__ . '/tests/App/Test/Codeception/_generated/AcceptanceTesterActions.php',
    RemoveUselessDefaultCommentFixer::class,
    PhpdocToPropertyTypeFixer::class => [
        __DIR__ . '/src',
        __DIR__ . '/app',
        __DIR__ . '/tests/App/Acceptance',
        __DIR__ . '/tests/App/Functional/EntityExtension/Model/*',
    ],
    FunctionLengthSniff::class => [
        __DIR__ . '/src/Migrations/Version20190801103940.php',
        __DIR__ . '/src/DataFixtures/*/*DataFixture.php',
        __DIR__ . '/src/DataFixtures/Demo/ProductDataFixtureLoader.php',
        __DIR__ . '/src/Controller/Front/OrderController.php',
        __DIR__ . '/src/Form/Front/Customer/BillingAddressFormType.php',
        __DIR__ . '/src/Form/Front/Customer/DeliveryAddressFormType.php',
        __DIR__ . '/src/Form/Admin/FriendlyUrlFormType.php',
        __DIR__ . '/src/Form/Admin/ProductFormTypeExtension.php',
        __DIR__ . '/src/Model/Product/ProductVisibilityRepository.php',
        __DIR__ . '/src/Form/Front/Order/PersonalInfoFormType.php',
        __DIR__ . '/src/Model/Order/Preview/OrderPreviewCalculation.php',
        __DIR__ . '/tests/App/Functional/EntityExtension/EntityExtensionTest.php',
        __DIR__ . '/tests/App/Functional/Model/Order/OrderFacadeTest.php',
        __DIR__ . '/tests/App/Functional/Model/Order/Preview/OrderPreviewCalculationTest.php',
        __DIR__ . '/tests/App/Functional/Model/Pricing/InputPriceRecalculationSchedulerTest.php',
        __DIR__ . '/tests/App/Smoke/BackendApiCreateProductTest.php',
        __DIR__ . '/tests/App/Smoke/Http/RouteConfigCustomization.php',
        __DIR__ . '/tests/App/Functional/Model/Product/ProductOnCurrentDomainElasticFacadeCountDataTest.php',
        __DIR__ . '/tests/App/Functional/Model/Product/Availability/ProductAvailabilityFacadeTest.php',
        __DIR__ . '/tests/App/Functional/Model/Cart/CartMigrationFacadeTest.php',
        __DIR__ . '/src/Model/Product/Transfer/Akeneo/ProductTransferAkeneoValidator.php',
        __DIR__ . '/src/Component/Akeneo/Transfer/AbstractAkeneoImportTransfer.php',
        __DIR__ . '/src/Component/DataBridge/Transfer/AbstractBridgeImportTransfer.php',
        __DIR__ . '/src/Migrations/Version20200319113341.php',
        __DIR__ . '/src/Migrations/Version20200831091231.php',
        __DIR__ . '/src/Controller/Admin/CategorySeoController.php',
        __DIR__ . '/src/Form/Admin/TransportFormTypeExtension.php',
        __DIR__ . '/src/Form/Front/Customer/User/CustomerUserFormType.php',
        __DIR__ . '/src/Form/Front/Registration/RegistrationFormType.php',
        __DIR__ . '/src/Model/Product/ProductDataFactory.php',
        __DIR__ . '/src/Controller/Front/ProductController.php',
        __DIR__ . '/src/Form/Admin/NotificationBarFormType.php',
        __DIR__ . '/tests/App/Functional/Model/Product/Elasticsearch/ProductExportRepositoryTest.php',
        __DIR__ . '/src/Model/Product/Elasticsearch/ProductExportRepository.php',
        __DIR__ . '/src/Model/Product/Availability/ProductAvailabilityFacade.php',
        __DIR__ . '/src/Model/Product/Parameter/ParameterFacade.php',
        __DIR__ . '/src/Controller/Front/CartController.php',
        __DIR__ . '/src/Model/Product/ProductSellingDeniedRecalculator.php',
        __DIR__ . '/tests/FrontendApiBundle/Functional/*',
        __DIR__ . '/src/Model/Security/Roles.php',
        __DIR__ . '/src/Model/Security/MenuItemsGrantedRolesSetting.php',
        __DIR__ . '/src/Form/Admin/Mail/MailTemplateFormTypeExtension.php',
        __DIR__ . '/src/Migrations/Version20221205123619.php',
    ],
    ClassLengthSniff::class => [
        __DIR__ . '/tests/App/Functional/Model/Product/ProductVisibilityRepositoryTest.php',
        __DIR__ . '/src/Component/Image/ImageFacade.php',
        __DIR__ . '/src/DataFixtures/Demo/OrderDataFixture.php',
        __DIR__ . '/src/DataFixtures/Demo/ProductDataFixture.php',
        __DIR__ . '/tests/App/Functional/Model/Product/ProductOnCurrentDomainElasticFacadeCountDataTest.php',
        __DIR__ . '/src/Model/Product/Elasticsearch/ProductExportRepository.php',
        __DIR__ . '/src/Model/Product/Product.php',
        __DIR__ . '/src/Model/Order/OrderFacade.php',
        __DIR__ . '/src/Controller/Front/CartController.php',
        __DIR__ . '/src/Controller/Front/OrderController.php',
        __DIR__ . '/src/Controller/Front/ProductController.php',
        __DIR__ . '/src/Model/Product/Transfer/Akeneo/ProductTransferAkeneoMapper.php',
        __DIR__ . '/src/Form/Admin/ProductFormTypeExtension.php',
        __DIR__ . '/src/Model/Product/Availability/ProductAvailabilityFacade.php',
        __DIR__ . '/tests/App/Test/Codeception/Module/StrictWebDriver.php',
        __DIR__ . '/tests/App/Test/Codeception/ActorInterface.php',
        __DIR__ . '/tests/App/Smoke/Http/RouteConfigCustomization.php',
        __DIR__ . '/tests/FrontendApiBundle/Functional/*',
    ],
    CyclomaticComplexitySniff::class => [
        __DIR__ . '/src/DataFixtures/Demo/ProductDataFixture.php',
        __DIR__ . '/src/DataFixtures/Demo/CategoryDataFixture.php',
        __DIR__ . '/src/Controller/Front/OrderController.php',
        __DIR__ . '/src/Model/Order/OrderFacade.php',
        __DIR__ . '/src/Model/Product/Search/ProductElasticsearchConverter.php',
        __DIR__ . '/src/FrontendApi/Resolver/Category/CategoryResolverMap.php',
        __DIR__ . '/src/Model/Blog/Article/Elasticsearch/BlogArticleElasticsearchDataFetcher.php',
        __DIR__ . '/src/Model/Product/Transfer/Akeneo/ProductTransferAkeneoMapper.php',
        __DIR__ . '/src/Model/Product/Transfer/Akeneo/ProductTransferAkeneoValidator.php',
        __DIR__ . '/src/Model/Product/Elasticsearch/ProductExportRepository.php',
    ],
    CamelCapsFunctionNameSniff::class => [
        __DIR__ . '/tests/App/Test/Codeception/ActorInterface.php',
    ],
    ValidVariableNameSniff::class => [
        __DIR__ . '/tests/App/Functional/EntityExtension/EntityExtensionTest.php',
        __DIR__ . '/tests/App/Test/Codeception/_generated/AcceptanceTesterActions.php',
        __DIR__ . '/tests/App/Functional/Controller/CdnTest.php',
        __DIR__ . '/tests/App/Test/Codeception/ActorInterface.php',
    ],
    ObjectIsCreatedByFactorySniff::class => [
        __DIR__ . '/tests',
        __DIR__ . '/src/Model/Order/Preview/OrderPreviewCalculation.php',
        __DIR__ . '/src/Model/Product/Filter/Elasticsearch/ProductFilterConfigFactory.php',
    ],
    ForbiddenDumpSniff::class => [
        __DIR__ . '/tests/App/Functional/Model/Cart/CartFacadeTest.php',
        __DIR__ . '/src/Model/GoPay/Exception/GoPayPaymentDownloadException.php',
    ],
    ForbiddenDoctrineInheritanceSniff::class => [
        __DIR__ . '/src',
        __DIR__ . '/tests/App',
    ],
    MethodDeclarationSniff::class . '.Underscore' => [
        __DIR__ . '/tests/App/Test/Codeception/Helper/CloseNewlyOpenedWindowsHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Helper/DatabaseHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Helper/DomainHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Helper/LocalizationHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Helper/NumberFormatHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Helper/SymfonyHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Module/Db.php'
    ],
    // @deprecated File is excluded as the comments are already missing and deprecated methods will not be in next major
    DeprecatedAnnotationDeclarationSniff::class => [
        __DIR__ . '/tests/App/Test/Codeception/Module/StrictWebDriver.php',
    ],
    ForbiddenSuperGlobalSniff::class => [
        __DIR__ . '/tests/App/Functional/Controller/CdnTest.php',
    ],
    PropertyTypeHintSniff::class => [
        ...json_decode(file_get_contents(__DIR__ . '/var/cache/entities-dump.json'), true, 512, JSON_THROW_ON_ERROR),
        __DIR__ . '/tests/App/Functional/EntityExtension/Model/*',
        '**Data.php',
    ],
];

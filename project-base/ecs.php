<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\ClassTraitAndInterfaceLengthSniff;
use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Shopsys\CodingStandards\Sniffs\ForbiddenDoctrineInheritanceSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenDumpSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Shopsys\CodingStandards\Sniffs\ValidVariableNameSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

/**
 * @param Symplify\EasyCodingStandard\Config\ECSConfig $ecsConfig
 */
return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rule(DeclareStrictTypesFixer::class);
    $ecsConfig->rule(PhpdocToPropertyTypeFixer::class);

    $ecsConfig->skip([
        __DIR__ . '/tests/App/Test/Codeception/_generated/AcceptanceTesterActions.php',
        __DIR__ . '/var/cache/*',
        PhpdocToPropertyTypeFixer::class => [
            __DIR__ . '/src/*',
            __DIR__ . '/app/*',
            __DIR__ . '/tests/App/Acceptance/*',
        ],
        FunctionLengthSniff::class => [
            __DIR__ . '/src/DataFixtures/*/*DataFixture.php',
            __DIR__ . '/src/DataFixtures/Demo/ProductDataFixtureLoader.php',
            __DIR__ . '/src/Controller/Front/OrderController.php',
            __DIR__ . '/src/Form/Front/Customer/BillingAddressFormType.php',
            __DIR__ . '/src/Form/Front/Customer/DeliveryAddressFormType.php',
            __DIR__ . '/src/Form/Front/Order/PersonalInfoFormType.php',
            __DIR__ . '/tests/App/Functional/EntityExtension/EntityExtensionTest.php',
            __DIR__ . '/tests/App/Functional/Model/Order/OrderFacadeTest.php',
            __DIR__ . '/tests/App/Functional/Model/Order/Preview/OrderPreviewCalculationTest.php',
            __DIR__ . '/tests/App/Functional/Model/Pricing/InputPriceRecalculationSchedulerTest.php',
            __DIR__ . '/tests/App/Smoke/BackendApiCreateProductTest.php',
            __DIR__ . '/tests/App/Smoke/Http/RouteConfigCustomization.php',
            __DIR__ . '/tests/App/Functional/Model/Product/ProductOnCurrentDomainFacadeCountDataTest.php',
            __DIR__ . '/tests/App/Functional/Model/Cart/CartMigrationFacadeTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Advert/GetAdvertsTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Article/GetArticlesTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Image/ProductImagesTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Payment/PaymentsTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Transport/TransportsTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Order/CompanyFieldsAreValidatedTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Order/DeliveryFieldsAreValidatedTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Order/DynamicFieldsInOrderTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Order/FullOrderTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Order/GetOrdersAsAuthenticatedCustomerUserTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Order/MinimalOrderTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Order/MultipleProductsInOrderTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Product/ProductTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Product/ProductVariantTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Product/ProductTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Product/ProductsTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Product/PromotedProductsTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Brand/BrandTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Brand/BrandsTest.php',
            __DIR__ . '/tests/FrontendApiBundle/Functional/Product/ProductsFilteringOptionsTest.php',
        ],
        ClassTraitAndInterfaceLengthSniff::class => [
            __DIR__ . '/tests/App/Functional/Model/Product/ProductVisibilityRepositoryTest.php',
            __DIR__ . '/src/DataFixtures/Demo/OrderDataFixture.php',
            __DIR__ . '/src/DataFixtures/Demo/ProductDataFixture.php',
            __DIR__ . '/tests/App/Functional/Model/Product/ProductOnCurrentDomainFacadeCountDataTest.php',
            __DIR__ . '/tests/App/Test/Codeception/Module/StrictWebDriver.php',
        ]
        ,
        CyclomaticComplexitySniff::class => [
            __DIR__ . '/src/DataFixtures/Demo/ProductDataFixture.php',
            __DIR__ . '/src/DataFixtures/Demo/CategoryDataFixture',
        ],
        ValidVariableNameSniff::class => [
            __DIR__ . '/tests/App/Functional/EntityExtension/EntityExtensionTest.php',
            __DIR__ . '/tests/App/Test/Codeception/_generated/AcceptanceTesterActions.php',
        ],
        ObjectIsCreatedByFactorySniff::class => [
            __DIR__ . '/tests/*',
        ],
        ForbiddenDumpSniff::class => [
            __DIR__ . '/tests/App/Functional/Model/Cart/CartFacadeTest.php',
        ],
        ForbiddenDoctrineInheritanceSniff::class => [
            __DIR__ . '/src/*',
            __DIR__ . '/tests/App/*',
        ],
        'PHP_CodeSniffer\Standards\PSR2\Sniffs\Methods\MethodDeclarationSniff.Underscore' => [
            __DIR__ . '/tests/App/Test/Codeception/Helper/CloseNewlyOpenedWindowsHelper.php',
            __DIR__ . '/tests/App/Test/Codeception/Helper/DatabaseHelper.php',
            __DIR__ . '/tests/App/Test/Codeception/Helper/DomainHelper.php',
            __DIR__ . '/tests/App/Test/Codeception/Helper/LocalizationHelper.php',
            __DIR__ . '/tests/App/Test/Codeception/Helper/NumberFormatHelper.php',
            __DIR__ . '/tests/App/Test/Codeception/Helper/SymfonyHelper.php',
            __DIR__ . '/tests/App/Test/Codeception/Module/Db.php',
        ],
    ]);

    $ecsConfig->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);
};

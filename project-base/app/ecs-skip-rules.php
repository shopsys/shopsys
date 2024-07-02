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
        __DIR__ . '/src/Component/DataBridge/Transfer/AbstractBridgeImportTransfer.php',
        __DIR__ . '/src/Controller/Admin/CategorySeoController.php',
        __DIR__ . '/src/DataFixtures/*/*DataFixture.php',
        __DIR__ . '/src/Form/Admin/FriendlyUrlFormType.php',
        __DIR__ . '/src/Form/Admin/NotificationBarFormType.php',
        __DIR__ . '/src/Migrations/Version20200319113341.php',
        __DIR__ . '/src/Migrations/Version20221205123619.php',
        __DIR__ . '/tests/App/Functional/EntityExtension/EntityExtensionTest.php',
        __DIR__ . '/tests/App/Functional/Model/Product/ProductOnCurrentDomainElasticFacadeCountDataTest.php',
        __DIR__ . '/tests/App/Smoke/Http/RouteConfigCustomization.php',
        __DIR__ . '/tests/FrontendApiBundle/Functional/*',
    ],
    ClassLengthSniff::class => [
        __DIR__ . '/src/DataFixtures/Demo/OrderDataFixture.php',
        __DIR__ . '/src/DataFixtures/Demo/ProductDataFixture.php',
        __DIR__ . '/tests/FrontendApiBundle/Functional/*',
    ],
    CyclomaticComplexitySniff::class => [
        __DIR__ . '/src/DataFixtures/Demo/ProductDataFixture.php',
        __DIR__ . '/src/Model/Product/Elasticsearch/ProductExportRepository.php',
        __DIR__ . '/src/Model/Product/Search/ProductElasticsearchConverter.php',
    ],
    CamelCapsFunctionNameSniff::class => [
        __DIR__ . '/tests/App/Test/Codeception/ActorInterface.php',
    ],
    ValidVariableNameSniff::class => [
        __DIR__ . '/tests/App/Functional/Controller/CdnTest.php',
        __DIR__ . '/tests/App/Functional/EntityExtension/EntityExtensionTest.php',
        __DIR__ . '/tests/App/Test/Codeception/_generated/AcceptanceTesterActions.php',
        __DIR__ . '/tests/App/Test/Codeception/ActorInterface.php',
    ],
    ObjectIsCreatedByFactorySniff::class => [
        __DIR__ . '/tests/*',
        __DIR__ . '/src/Model/Product/Filter/Elasticsearch/ProductFilterConfigFactory.php',
    ],
    ForbiddenDumpSniff::class => [
        __DIR__ . '/tests/App/Functional/Model/Cart/CartFacadeTest.php',
        __DIR__ . '/src/Model/GoPay/Exception/GoPayPaymentDownloadException.php',
    ],
    ForbiddenDoctrineInheritanceSniff::class => [
        __DIR__ . '/src/*',
        __DIR__ . '/tests/App/*',
    ],
    \Shopsys\CodingStandards\Sniffs\ForbiddenExitSniff::class => [
        __DIR__ . '/app/downloadPhing.php',
    ],
    MethodDeclarationSniff::class . '.Underscore' => [
        __DIR__ . '/tests/App/Test/Codeception/Helper/CloseNewlyOpenedWindowsHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Helper/DatabaseHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Helper/DomainHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Helper/LocalizationHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Helper/NumberFormatHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Helper/SymfonyHelper.php',
        __DIR__ . '/tests/App/Test/Codeception/Module/Db.php',
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

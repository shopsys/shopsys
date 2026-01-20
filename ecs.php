<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\NamingConventions\ValidVariableNameSniff as PhpCsValidVariableNameSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\DisallowMultipleAssignmentsSniff;
use Shopsys\CodingStandards\CsFixer\FinalFormTypeFixer;
use Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer;
use Shopsys\CodingStandards\Helper\CyclomaticComplexitySniffSetting;
use Shopsys\CodingStandards\Sniffs\ForbiddenDumpSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenSuperGlobalSniff;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Shopsys\CodingStandards\Sniffs\ValidVariableNameSniff;
use SlevomatCodingStandard\Sniffs\Classes\ClassLengthSniff;
use SlevomatCodingStandard\Sniffs\Classes\ParentCallSpacingSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\EarlyExitSniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$packagePaths = [];
$packagesDirectoryIterator = new DirectoryIterator(__DIR__ . '/packages');

foreach ($packagesDirectoryIterator as $path) {
    if ($path->isDir() && !$path->isDot()) {
        $pathCandidates = [
            $path->getPathname() . '/src',
            $path->getPathname() . '/tests',
        ];

        foreach ($pathCandidates as $pathCandidate) {
            if (file_exists($pathCandidate)) {
                $packagePaths[] = $pathCandidate;
            }
        }
    }
}

/**
 * Beware, the following variable needs to have a distinct name from the one defined in ecs-skip-rules.php.
 * As ecs-skip-rules.php file is included in this file, the variable would be overridden.
 */
$pathsExcludedFromStrictTyping = [
    __DIR__ . '/packages/framework/tests/Unit/Component/ClassExtension/Source/*',
    __DIR__ . '/packages/framework/src/Model/Localization/TranslatableEntityTrait.php',
    __DIR__ . '/packages/framework/src/Model/Security/UniqueLoginInterface.php',
    __DIR__ . '/packages/framework/src/Model/Security/TimelimitLoginInterface.php',
    __DIR__ . '/packages/framework/src/Component/Security/ResetPasswordInterface.php',
    __DIR__ . '/packages/framework/src/Component/FileUpload/EntityFileUploadInterface.php',
    __DIR__ . '/packages/framework/src/Component/AbstractUploadedFile/UploadedFileInterface.php',
];

return ECSConfig::configure()
    ->withPaths([
        ...$packagePaths,
        __DIR__ . '/project-base/app/app',
        __DIR__ . '/project-base/app/src',
        __DIR__ . '/project-base/app/tests',
        __DIR__ . '/utils/releaser/src',
        __DIR__ . '/utils/releaser/tests',
    ])
    ->withSets([
        __DIR__ . '/project-base/app/ecs.php',
    ])
    ->withRules([
        ForceLateStaticBindingForProtectedConstantsSniff::class,
        FinalFormTypeFixer::class,
    ])
    ->withConfiguredRule(ForbiddenPrivateVisibilityFixer::class,
        [
            'analyzed_namespaces' => [
                'Shopsys\ArticleFeed\LuigisBoxBundle\Model',
                'Shopsys\BrandFeed\LuigisBoxBundle\Model',
                'Shopsys\CategoryFeed\LuigisBoxBundle\Model',
                'Shopsys\FrameworkBundle\Component',
                'Shopsys\FrameworkBundle\Controller',
                'Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch',
                'Shopsys\FrameworkBundle\Form\Constraints',
                'Shopsys\FrameworkBundle\Form\Transformer',
                'Shopsys\FrameworkBundle\Model',
                'Shopsys\FrameworkBundle\Twig',
                'Shopsys\FrontendApiBundle',
                'Shopsys\LuigisBoxBundle',
                'Shopsys\MakerBundle',
                'Shopsys\MigrationBundle\Command',
                'Shopsys\MigrationBundle\Component',
                'Shopsys\ProductFeed\GoogleBundle\Model',
                'Shopsys\ProductFeed\MergadoBundle\Model',
                'Shopsys\ProductFeed\HeurekaBundle\Model',
                'Shopsys\ProductFeed\HeurekaDeliveryBundle\Model',
                'Shopsys\ProductFeed\LuigisBoxBundle\Model',
                'Shopsys\ProductFeed\ZboziBundle\Model',
                'Shopsys\S3Bridge',
            ],
        ],
    )
    ->withConfiguredRule(CyclomaticComplexitySniff::class, [
        'absoluteComplexity' => CyclomaticComplexitySniffSetting::DEFAULT_ABSOLUTE_COMPLEXITY,
    ])
    ->withSkip(array_merge_recursive(
        include __DIR__ . '/project-base/app/ecs-skip-rules.php',
        [
            __DIR__ . '/packages/framework/tests/Test/Codeception/ActorInterface.php',
            __DIR__ . '/packages/framework/src/Component/Filesystem/Flysystem/VolumeDriver.php',
            __DIR__ . '/packages/coding-standards/tests/Unit/**/wrong/*',
            __DIR__ . '/packages/coding-standards/tests/Unit/**/Wrong/*',
            __DIR__ . '/packages/coding-standards/tests/Unit/**/correct/*',
            __DIR__ . '/packages/coding-standards/tests/Unit/**/Correct/*',
            __DIR__ . '/packages/coding-standards/tests/Unit/**/fixed/*',
            __DIR__ . '/packages/coding-standards/tests/Unit/**/Fixed/*',
            __DIR__ . '/packages/maker/templates/*',
            AssignmentInConditionSniff::class => [
                __DIR__ . '/project-base/app/src/Kernel.php',
            ],
            CamelCapsFunctionNameSniff::class => [
                __DIR__ . '/packages/framework/src/Component/Doctrine/MoneyType.php',
                __DIR__ . '/packages/framework/src/Component/EntityExtension/QueryBuilder.php',
                __DIR__ . '/packages/administration/src/Component/Action/AbstractAction.php',
            ],
            ClassLengthSniff::class => [
                __DIR__ . '/packages/framework/src/Form/Admin/Product/ProductFormType.php',
                __DIR__ . '/packages/framework/src/Model/Order/Order.php',
                __DIR__ . '/packages/framework/src/Model/Product/Search/FilterQuery.php',
                __DIR__ . '/packages/framework/src/Model/Product/Product.php',
                __DIR__ . '/packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php',
                __DIR__ . '/packages/framework/src/Model/Security/Roles.php',
                __DIR__ . '/packages/framework/src/Model/Product/Parameter/ParameterRepository.php',
                __DIR__ . '/project-base/app/tests/App/Functional/Model/Product/ProductOnCurrentDomainElasticFacadeCountDataTest.php',
            ],
            CyclomaticComplexitySniff::class => [
                __DIR__ . '/packages/framework/src/Migrations/Version20231124121921.php',
                __DIR__ . '/packages/framework/src/Migrations/Version20240403091822.php',
                __DIR__ . '/packages/framework/src/Migrations/Version20240704143616.php',
                __DIR__ . '/packages/framework/src/Model/Blog/Article/Elasticsearch/BlogArticleElasticsearchDataFetcher.php',
                __DIR__ . '/packages/framework/src/Model/Product/Elasticsearch/ProductExportRepository.php',
                __DIR__ . '/packages/framework/src/Model/Product/Search/ProductElasticsearchConverter.php',
                __DIR__ . '/packages/maker/src/EntityConfig/EntityFieldsConfigurator.php',
                __DIR__ . '/project-base/app/src/DataFixtures/Demo/UnitDataFixture.php',
            ],
            DisallowMultipleAssignmentsSniff::class => [
                __DIR__ . '/project-base/app/src/Kernel.php',
            ],
            EarlyExitSniff::class => [
                __DIR__ . '/packages/framework/src/Migrations/Version*.php',
            ],
            DisallowEmptySniff::class => [
                __DIR__ . '/packages/framework/src/Component/Filesystem/Flysystem/VolumeDriver.php',
                __DIR__ . '/packages/framework/src/Model/AdminNavigation/RoutingExtension.php',
            ],
            ForbiddenDumpSniff::class => [
                __DIR__ . '/packages/framework/src/Component/DateTimeHelper/Exception/CannotParseDateTimeException.php',
                __DIR__ . '/packages/framework/src/Twig/VarDumperExtension.php',
            ],
            ForceLateStaticBindingForProtectedConstantsSniff::class => [
                __DIR__ . '/project-base',
            ],
            FunctionLengthSniff::class => [
                __DIR__ . '/packages/framework/src/Controller/Admin/CategorySeoController.php',
                __DIR__ . '/packages/framework/src/Controller/Admin/PriceListController.php',
                __DIR__ . '/packages/framework/src/Migrations/Version*.php',
                __DIR__ . '/packages/framework/src/Form/FileUploadType.php',
                __DIR__ . '/packages/framework/src/Form/Admin/*/*FormType.php',
                __DIR__ . '/packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php',
                __DIR__ . '/packages/framework/src/Model/Customer/User/CustomerUserRepository.php',
                __DIR__ . '/packages/framework/src/Model/Mail/MailTemplateBuilder.php',
                __DIR__ . '/packages/framework/src/Model/Mail/MailTemplateConfiguration.php',
                __DIR__ . '/packages/framework/src/Model/Order/Preview/OrderPreviewCalculation.php',
                __DIR__ . '/packages/framework/src/Model/Product/Elasticsearch/ProductExportRepository.php',
                __DIR__ . '/packages/framework/src/Model/Product/Elasticsearch/Scope/ProductExportScopeConfig.php',
                __DIR__ . '/packages/framework/src/Model/Product/Search/FilterQuery.php',
                __DIR__ . '/packages/framework/src/Model/Product/ProductVisibilityRepository.php',
                __DIR__ . '/packages/framework/src/Model/Security/Roles.php',
                __DIR__ . '/packages/framework/src/Model/Sitemap/SitemapListener.php',
                __DIR__ . '/packages/framework/tests/Unit/Component/Domain/DomainDataCreatorTest.php',
                __DIR__ . '/packages/framework/tests/Unit/Component/Domain/DomainTest.php',
                __DIR__ . '/packages/framework/tests/Unit/Component/Router/DomainRouterFactoryTest.php',
                __DIR__ . '/packages/framework/tests/Unit/Model/Category/CategoryNestedSetCalculatorTest.php',
                __DIR__ . '/packages/framework/tests/Unit/Model/Mail/EnvelopeListenerTest.php',
                __DIR__ . '/packages/framework/tests/Unit/Model/Payment/PaymentPriceCalculationTest.php',
                __DIR__ . '/packages/framework/tests/Unit/Model/Payment/IndependentPaymentVisibilityCalculationTest.php',
                __DIR__ . '/packages/framework/tests/Unit/Model/Product/Search/ProductElasticsearchConverterTest.php',
                __DIR__ . '/packages/frontend-api/src/Model/Resolver/Customer/User/CustomerUserResolverMap.php',
                __DIR__ . '/packages/maker/src/EntityConfig/EntityFieldsConfigurator.php',
                __DIR__ . '/packages/migrations/tests/Unit/Component/Doctrine/Migrations/MigrationsLockComparatorTest.php',
                __DIR__ . '/packages/product-feed-zbozi/src/DataFixtures/ZboziPluginDataFixture.php',
                __DIR__ . '/utils/releaser/src/ReleaseWorker/Release/CreateAndPushGitTagsExceptProjectBaseReleaseWorker.php',
            ],
            ParentCallSpacingSniff::class . '.IncorrectLinesCountBeforeControlStructure' => [
                __DIR__ . '/packages/framework/src/Component/Filesystem/Flysystem/VolumeDriver.php',
            ],
            PhpCsValidVariableNameSniff::class => [
                __DIR__ . '/packages/product-feed-heureka/src/Model/HeurekaCategory/HeurekaCategoryDownloader.php',
            ],
            ValidVariableNameSniff::class => [
                __DIR__ . '/packages/framework/src/Component/HttpFoundation/Exception/NotFoundRedirectToStorefrontException.php',

            ],
            ForbiddenSuperGlobalSniff::class => [
                __DIR__ . '/packages/framework/src/Component/HttpFoundation/Exception/NotFoundRedirectToStorefrontException.php',
            ],
            PropertyTypeHintSniff::class => $pathsExcludedFromStrictTyping,
            ParameterTypeHintSniff::class . '.' . ParameterTypeHintSniff::CODE_MISSING_NATIVE_TYPE_HINT => $pathsExcludedFromStrictTyping,
            ParameterTypeHintSniff::class . '.' . ParameterTypeHintSniff::CODE_USELESS_ANNOTATION => $pathsExcludedFromStrictTyping,
            ReturnTypeHintSniff::class . '.' . ReturnTypeHintSniff::CODE_MISSING_NATIVE_TYPE_HINT => $pathsExcludedFromStrictTyping,
            ReturnTypeHintSniff::class . '.' . ReturnTypeHintSniff::CODE_USELESS_ANNOTATION => $pathsExcludedFromStrictTyping,
            ObjectIsCreatedByFactorySniff::class => [
                __DIR__ . '/packages/framework/src/Component/Domain/DomainFactoryOverwritingDomainUrl.php',
                __DIR__ . '/packages/framework/src/Component/EntityExtension/EntityExtensionSubscriber.php',
                __DIR__ . '/packages/framework/src/DependencyInjection/Compiler/RegisterExtendedEntitiesCompilerPass.php',
                __DIR__ . '/packages/framework/src/Model/Order/Preview/OrderPreviewCalculation.php',
                __DIR__ . '/packages/*/tests/*',
            ],
            FinalFormTypeFixer::class => [
                __DIR__ . '/project-base',
                __DIR__ . '/packages/framework/src/Form/Locale/LocalizedType.php',
            ],
        ],
    ));

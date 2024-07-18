<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\NamingConventions\ValidVariableNameSniff as PhpCsValidVariableNameSniff;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer;
use Shopsys\CodingStandards\Helper\CyclomaticComplexitySniffSetting;
use Shopsys\CodingStandards\Sniffs\ForbiddenDumpSniff;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use SlevomatCodingStandard\Sniffs\Classes\ClassLengthSniff;
use SlevomatCodingStandard\Sniffs\Classes\ParentCallSpacingSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\EarlyExitSniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
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
        PhpdocToPropertyTypeFixer::class,
        ForceLateStaticBindingForProtectedConstantsSniff::class,
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
                'Shopsys\MigrationBundle\Command',
                'Shopsys\MigrationBundle\Component',
                'Shopsys\ProductFeed\GoogleBundle\Model',
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
            __DIR__ . '/packages/coding-standards/tests/Unit/**/wrong/*',
            __DIR__ . '/packages/coding-standards/tests/Unit/**/Wrong/*',
            __DIR__ . '/packages/coding-standards/tests/Unit/**/correct/*',
            __DIR__ . '/packages/coding-standards/tests/Unit/**/Correct/*',
            __DIR__ . '/packages/coding-standards/tests/Unit/**/fixed/*',
            CamelCapsFunctionNameSniff::class => [
                __DIR__ . '/packages/framework/src/Component/Doctrine/MoneyType.php',
                __DIR__ . '/packages/framework/src/Component/EntityExtension/QueryBuilder.php',
            ],
            ClassLengthSniff::class => [
                __DIR__ . '/packages/framework/src/Form/Admin/Product/ProductFormType.php',
                __DIR__ . '/packages/framework/src/Model/Product/Search/FilterQuery.php',
            ],
            CyclomaticComplexitySniff::class => [
                __DIR__ . '/packages/framework/src/Migrations/Version20231124121921.php',
                __DIR__ . '/packages/framework/src/Migrations/Version20240403091822.php',
                __DIR__ . '/packages/framework/src/Migrations/Version20240704143616.php',
                __DIR__ . '/packages/framework/src/Model/Blog/Article/Elasticsearch/BlogArticleElasticsearchDataFetcher.php',
                __DIR__ . '/packages/framework/src/Model/Product/Elasticsearch/ProductExportRepository.php',
                __DIR__ . '/packages/framework/src/Model/Product/Search/ProductElasticsearchConverter.php',
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
                __DIR__ . '/packages/framework/src/Migrations/Version*.php',
                __DIR__ . '/packages/framework/src/Form/Admin/*/*FormType.php',
                __DIR__ . '/packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php',
                __DIR__ . '/packages/framework/src/Model/Order/Preview/OrderPreviewCalculation.php',
                __DIR__ . '/packages/framework/src/Model/Product/Elasticsearch/ProductExportRepository.php',
                __DIR__ . '/packages/framework/src/Model/Product/Elasticsearch/Scope/ProductExportScopeConfig.php',
                __DIR__ . '/packages/framework/src/Model/Product/Search/FilterQuery.php',
                __DIR__ . '/packages/framework/src/Model/Product/ProductVisibilityRepository.php',
                __DIR__ . '/packages/framework/src/Model/Security/MenuItemsGrantedRolesSetting.php',
                __DIR__ . '/packages/framework/src/Model/Security/Roles.php',
                __DIR__ . '/packages/framework/tests/Unit/Component/Domain/DomainDataCreatorTest.php',
                __DIR__ . '/packages/framework/tests/Unit/Model/Category/CategoryNestedSetCalculatorTest.php',
                __DIR__ . '/packages/framework/tests/Unit/Model/Mail/EnvelopeListenerTest.php',
                __DIR__ . '/packages/migrations/tests/Unit/Component/Doctrine/Migrations/MigrationsLockComparatorTest.php',
                __DIR__ . '/packages/product-feed-zbozi/src/DataFixtures/ZboziPluginDataFixture.php',
                __DIR__ . '/utils/releaser/src/ReleaseWorker/Release/CreateAndPushGitTagsExceptProjectBaseReleaseWorker.php',
            ],
            ParentCallSpacingSniff::class . '.IncorrectLinesCountBeforeControlStructure' => [
                __DIR__ . '/packages/framework/src/Component/Filesystem/Flysystem/VolumeDriver.php',
            ],
            PhpdocToPropertyTypeFixer::class => [
                __DIR__ . '/packages/*/src/*',
                __DIR__ . '/packages/framework/tests/Unit/Component/ClassExtension/Source/*',
            ],
            PhpCsValidVariableNameSniff::class => [
                __DIR__ . '/packages/product-feed-heureka/src/Model/HeurekaCategory/HeurekaCategoryDownloader.php',
            ],
            PropertyTypeHintSniff::class => [
                __DIR__ . '/packages/framework/tests/Unit/Component/ClassExtension/Source/*',
            ],
            ObjectIsCreatedByFactorySniff::class => [
                __DIR__ . '/packages/framework/src/Component/Domain/DomainFactoryOverwritingDomainUrl.php',
                __DIR__ . '/packages/framework/src/Component/EntityExtension/EntityExtensionSubscriber.php',
                __DIR__ . '/packages/framework/src/DependencyInjection/Compiler/RegisterExtendedEntitiesCompilerPass.php',
                __DIR__ . '/packages/framework/src/Model/Order/Preview/OrderPreviewCalculation.php',
                __DIR__ . '/packages/*/tests/*',
            ],
        ],
    ));

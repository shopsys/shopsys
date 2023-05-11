<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\EmptyStatementSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff;
use PHP_CodeSniffer\Standards\PSR2\Sniffs\Methods\MethodDeclarationSniff;
use Shopsys\CodingStandards\CsFixer\ForbiddenDumpFixer;
use Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer;
use Shopsys\CodingStandards\Sniffs\ForbiddenDumpSniff;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Shopsys\CodingStandards\Sniffs\ValidVariableNameSniff;
use SlevomatCodingStandard\Sniffs\Classes\ClassLengthSniff;
use SlevomatCodingStandard\Sniffs\Classes\ParentCallSpacingSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff;
use Symplify\CodingStandard\Fixer\Commenting\RemoveUselessDefaultCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

/**
 * @param Symplify\EasyCodingStandard\Config\ECSConfig $ecsConfig
 */
return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rule(ForceLateStaticBindingForProtectedConstantsSniff::class);

    /*
     * this package is meant to be extensible using class inheritance,
     * so we want to avoid private visibilities in the model namespace
     */
    $services = $ecsConfig->services();
    $services->set('forbidden_private_visibility_fixer.framework', ForbiddenPrivateVisibilityFixer::class)
        ->call('configure', [
            [
                'analyzed_namespaces' => [
                    'Shopsys\FrameworkBundle\Model',
                    'Shopsys\FrameworkBundle\Component',
                    'Shopsys\FrameworkBundle\Controller',
                    'Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch',
                    'Shopsys\FrameworkBundle\Form\Constraints',
                    'Shopsys\FrameworkBundle\Form\Transformer',
                    'Shopsys\FrameworkBundle\Twig',
                ],
            ],
        ]);

    $ecsConfig->skip([
        FunctionLengthSniff::class => [
            __DIR__ . '/src/Controller/Admin/DefaultController.php',
            __DIR__ . '/src/Form/Admin/*/*FormType.php',
            __DIR__ . '/src/Migrations/Version*.php',
            __DIR__ . '/src/Model/AdminNavigation/SideMenuBuilder.php',
            __DIR__ . '/src/Model/Order/Preview/OrderPreviewCalculation.php',
            __DIR__ . '/src/Model/Product/ProductVisibilityRepository.php',
            __DIR__ . '/src/Model/Product/Search/FilterQuery.php',
            __DIR__ . '/src/Controller/Admin/AdministratorController.php',
            __DIR__ . '/tests/Unit/Model/Customer/CustomerUserUpdateDataFactoryTest.php',
            __DIR__ . '/tests/Unit/Component/Domain/DomainDataCreatorTest.php',
            __DIR__ . '/tests/Unit/Component/Image/ImageLocatorTest.php',
            __DIR__ . '/tests/Unit/Component/Image/Config/ImageConfigLoaderTest.php',
            __DIR__ . '/tests/Unit/Component/Image/Config/ImageConfigTest.php',
            __DIR__ . '/tests/Unit/Model/Category/CategoryNestedSetCalculatorTest.php',
            __DIR__ . '/tests/Unit/Model/Payment/PaymentPriceCalculationTest.php',
            __DIR__ . '/src/Form/Constraints/FileAbstractFilesystemValidator.php',
            __DIR__ . '/tests/Unit/Model/Mail/EnvelopeListenerTest.php',
        ],
        ClassLengthSniff::class => [
            __DIR__ . '/src/Form/Admin/Product/ProductFormType.php',
            __DIR__ . '/src/Command/TranslationReplaceSourceCommand.php',
            __DIR__ . '/src/Component/Grid/Grid.php',
            __DIR__ . '/src/Model/Order/Order.php',
            __DIR__ . '/src/Model/Order/OrderFacade.php',
            __DIR__ . '/src/Model/Product/Product.php',
            __DIR__ . '/src/Model/Product/ProductRepository.php',
            __DIR__ . '/src/Model/Product/Elasticsearch/ProductExportRepository.php',
            __DIR__ . '/tests/Test/Codeception/ActorInterface.php',
            __DIR__ . '/tests/Unit/Component/Money/MoneyTest.php',
            __DIR__ . '/src/Model/Product/Search/FilterQuery.php',
            __DIR__ . '/src/Model/Category/CategoryFacade.php',
            __DIR__ . '/src/Model/Category/CategoryRepository.php',
            __DIR__ . '/src/Model/Product/ProductFacade.php',
            __DIR__ . '/src/Component/Image/ImageFacade.php',
        ],
        EmptyStatementSniff::class . '.DetectedWhile' => [
            __DIR__ . '/src/Model/Product/Availability/ProductAvailabilityRecalculator.php',
            __DIR__ . '/src/Model/Product/Pricing/ProductPriceRecalculator.php',
        ],
        CamelCapsFunctionNameSniff::class => [
            __DIR__ . '/src/Component/Doctrine/MoneyType.php',
            __DIR__ . '/tests/Test/Codeception/ActorInterface.php',
            __DIR__ . '/src/Component/EntityExtension/QueryBuilder.php',
        ],
        ForbiddenDumpFixer::class => [
            __DIR__ . '/src/Resources/views/Debug/Elasticsearch/template.html.twig',
        ],
        ObjectIsCreatedByFactorySniff::class => [
            __DIR__ . '/tests/*',
            __DIR__ . '/src/Component/Domain/DomainFactoryOverwritingDomainUrl.php',
            __DIR__ . '/src/DependencyInjection/Compiler/RegisterExtendedEntitiesCompilerPass.php',
            __DIR__ . '/src/Model/Order/Preview/OrderPreviewCalculation.php',
            __DIR__ . '/src/Component/EntityExtension/EntityExtensionSubscriber.php',
        ],
        ForbiddenDumpSniff::class => [
            __DIR__ . '/src/Component/DateTimeHelper/Exception/CannotParseDateTimeException.php',
            __DIR__ . '/src/Component/Doctrine/Cache/PermanentPhpFileCache.php',
            __DIR__ . '/src/Twig/VarDumperExtension.php',
            __DIR__ . '/src/Resources/views/Migration/migration.php.twig',
        ],
        ValidVariableNameSniff::class => [
            __DIR__ . '/tests/Test/Codeception/ActorInterface.php',
        ],
        MethodDeclarationSniff::class . '.Underscore' => [
            __DIR__ . '/src/Component/Filesystem/Flysystem/VolumeDriver.php',
        ],
        CyclomaticComplexitySniff::class . '.MaxExceeded' => [
            __DIR__ . '/src/Form/Constraints/FileAbstractFilesystemValidator.php',
            __DIR__ . '/src/Model/Product/Search/ProductElasticsearchConverter.php',
        ],
        EmptyStatementSniff::class . '.DetectedCatch' => [
            __DIR__ . '/src/Component/Elasticsearch/Debug/ElasticsearchTracer.php',
            __DIR__ . '/src/DependencyInjection/Compiler/RegisterProjectFrameworkClassExtensionsCompilerPass.php',
        ],
        ParentCallSpacingSniff::class . '.IncorrectLinesCountBeforeControlStructure' => [
            __DIR__ . '/src/Component/Filesystem/Flysystem/VolumeDriver.php',
        ],
        DisallowEmptySniff::class . '.DisallowedEmpty' => [
            __DIR__ . '/src/Component/Filesystem/Flysystem/VolumeDriver.php',
            __DIR__ . '/src/Model/AdminNavigation/RoutingExtension.php',
        ],
        UnusedVariableSniff::class => [
            __DIR__ . '/src/Component/Form/ResizeFormListener.php',
        ],
        RemoveUselessDefaultCommentFixer::class => [
            __DIR__ . '/tests/Unit/Component/ClassExtension/Source/AnnotationsAdderTest/DummyClassWithAnAnnotation.php',
        ],
        PropertyTypeHintSniff::class => [
            __DIR__ . '/tests/Unit/Component/ClassExtension/Source/*/*.php',
            __DIR__ . '/tests/Unit/Component/ClassExtension/Source/*.php',
        ],
    ]);

    $ecsConfig->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);
};

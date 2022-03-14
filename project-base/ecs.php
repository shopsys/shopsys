<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\ClassTraitAndInterfaceLengthSniff;
use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Classes\ValidClassNameSniff;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Shopsys\CodingStandards\Sniffs\ForbiddenDoctrineInheritanceSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenDumpSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Shopsys\CodingStandards\Sniffs\ValidVariableNameSniff;
use SlevomatCodingStandard\Sniffs\Classes\ParentCallSpacingSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DeprecatedAnnotationDeclarationSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\EarlyExitSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\UselessIfConditionWithReturnSniff;
use SlevomatCodingStandard\Sniffs\Exceptions\ReferenceThrowableOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Operators\DisallowEqualOperatorsSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::SETS,
        [
            SetList::PHP_70,
            SetList::PHP_71,
            SetList::PSR_12,
            SetList::DEAD_CODE,
            SetList::CLEAN_CODE,
            SetList::ARRAY,
            SetList::COMMENTS,
            SetList::CONTROL_STRUCTURES,
            SetList::DOCBLOCK,
            SetList::NAMESPACES,
            SetList::STRICT,
        ]
    );

    $parameters->set(
        Option::EXCLUDE_PATHS,
        [
            __DIR__ . '/tests/App/Test/Codeception/_generated/AcceptanceTesterActions.php',
            __DIR__ . '/var/cache/*',
        ]
    );

    $parameters->set(
        Option::SKIP,
        [
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
                __DIR__ . '/tests/App/Test/Codeception/Module/StrictWebDriver.php']
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
            // @deprecated This will be moved from project-base to coding-standards package in next major version
            // rule is applied via `clean-code` set, but we do not want to use it for now
            // some variables exist just because of the right annotation
            ReturnAssignmentFixer::class => null,
            // @deprecated This will be moved from project-base to coding-standards package in next major version
            // rule is applied via `control-structures` set, but we do not want to use it for now
            OrderedClassElementsFixer::class => null,
            // @deprecated This will be moved from project-base to coding-standards package in next major version
            // rule is applied via `docblock` set, but we do not want to use it for now
            // remove variable name from @var and @type annotations
            PhpdocVarWithoutNameFixer::class => null,
            // @deprecated This will be moved from project-base to coding-standards package in next major version
            // rule is applied via `docblock` set, but we do not want to use it for now
            // remove inheritdoc
            NoSuperfluousPhpdocTagsFixer::class => null,
            // @deprecated This will be moved from project-base to coding-standards package in next major version
            // rule is applied via `php70` set, but we cannot use it until next major because of possible BC breaks
            ReferenceThrowableOnlySniff::class => null,
            // @deprecated This will be moved from project-base to coding-standards package in next major version
            // rule is applied via `php71` set, but we cannot use it until next major because of possible BC breaks
            VoidReturnFixer::class => null,
            // @deprecated File is excluded as the comments are already missing and deprecated methods will not be in next major
            DeprecatedAnnotationDeclarationSniff::class => [
                __DIR__ . '/tests/App/Test/Codeception/Module/StrictWebDriver.php',
            ],
            // @deprecated This will be moved from project-base to coding-standards package in next major version
            // rule breaks jms/translation-budle as it fails on this usage: `[, $b] = $var`
            ListSyntaxFixer::class => null,
        ]
    );

    $services->set(DeclareStrictTypesFixer::class);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(DisallowEqualOperatorsSniff::class);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(ValidClassNameSniff::class);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(NoUselessElseFixer::class);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(AssignmentInConditionSniff::class);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(DisallowEmptySniff::class);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(EarlyExitSniff::class)
        ->property('ignoreStandaloneIfInScope', true)
        ->property('ignoreOneLineTrailingIf', true)
        ->property('ignoreTrailingIfWithOneInstruction', true);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(ParentCallSpacingSniff::class)
        ->property('linesCountBeforeParentCall', 1)
        ->property('linesCountAfterParentCall', 1);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(ReferenceUsedNamesOnlySniff::class)
        ->property('allowPartialUses', true);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(DeprecatedAnnotationDeclarationSniff::class);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(DocCommentSpacingSniff::class)
        ->property('linesCountBetweenDifferentAnnotationsTypes', 0);

    // @deprecated This will be moved from project-base to coding-standards package in next major version
    $services->set(UselessIfConditionWithReturnSniff::class);

    $containerConfigurator->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);
};

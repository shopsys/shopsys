<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\ClassTraitAndInterfaceLengthSniff;
use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use ObjectCalisthenics\Sniffs\Metrics\PropertyPerClassLimitSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Classes\ValidClassNameSniff;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
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
use Sniffer\ExtendedApiClassNamespaceSniffer;
use Sniffer\FrontendApiNamespaceSniffer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

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
            PhpdocToPropertyTypeFixer::class => [
                __DIR__ . '/src/*',
                __DIR__ . '/app/*',
                __DIR__ . '/tests/App/Acceptance/*',
            ],
            FunctionLengthSniff::class => [
                __DIR__ . '/src/Migrations/Version20190801103940.php',
                __DIR__ . '/src/DataFixtures/*/*DataFixture.php',
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
                __DIR__ . '/tests/App/Smoke/Http/RouteConfigCustomization.php',
                __DIR__ . '/tests/App/Functional/Model/Product/ProductOnCurrentDomainFacadeCountDataTest.php',
                __DIR__ . '/tests/App/Functional/Model/Product/Availability/ProductAvailabilityFacadeTest.php',
                __DIR__ . '/tests/App/Functional/Model/Cart/CartMigrationFacadeTest.php',
                __DIR__ . '/src/Model/Product/Transfer/Akeneo/ProductTransferAkeneoValidator.php',
                __DIR__ . '/src/Component/Akeneo/Transfer/AbstractAkeneoImportTransfer.php',
                __DIR__ . '/src/Component/SsfwccBridge/Transfer/AbstractBridgeImportTransfer.php',
                __DIR__ . '/src/Migrations/Version20200319113341.php',
                __DIR__ . '/src/Migrations/Version20200831091231.php',
                __DIR__ . '/src/Controller/Admin/CategorySeoController.php',
                __DIR__ . '/src/Form/Admin/StockFormType.php',
                __DIR__ . '/src/Form/Admin/Store/StoreFormType.php',
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
            ],
            ClassTraitAndInterfaceLengthSniff::class => [
                __DIR__ . '/tests/App/Functional/Model/Product/ProductVisibilityRepositoryTest.php',
                __DIR__ . '/src/Component/Image/ImageFacade.php',
                __DIR__ . '/src/DataFixtures/Demo/OrderDataFixture.php',
                __DIR__ . '/src/DataFixtures/Demo/ProductDataFixture.php',
                __DIR__ . '/tests/App/Functional/Model/Product/ProductOnCurrentDomainFacadeCountDataTest.php',
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
                __DIR__ . '/src/FrontendApi/Resolver/Category/CategoryResolverMap.php'],
            CamelCapsFunctionNameSniff::class => [
                __DIR__ . '/tests/App/Test/Codeception/ActorInterface.php',
            ],
            ValidVariableNameSniff::class => [
                __DIR__ . '/tests/App/Functional/EntityExtension/EntityExtensionTest.php',
                __DIR__ . '/tests/App/Test/Codeception/_generated/AcceptanceTesterActions.php',
                __DIR__ . '/tests/App/Test/Codeception/ActorInterface.php',
            ],
            ObjectIsCreatedByFactorySniff::class => [
                __DIR__ . '/tests/*',
                __DIR__ . '/src/Model/Order/Preview/OrderPreviewCalculation.php',
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
            'PHP_CodeSniffer\Standards\PSR2\Sniffs\Methods\MethodDeclarationSniff.Underscore' => [
                __DIR__ . '/tests/App/Test/Codeception/Helper/CloseNewlyOpenedWindowsHelper.php',
                __DIR__ . '/tests/App/Test/Codeception/Helper/DatabaseHelper.php',
                __DIR__ . '/tests/App/Test/Codeception/Helper/DomainHelper.php',
                __DIR__ . '/tests/App/Test/Codeception/Helper/LocalizationHelper.php',
                __DIR__ . '/tests/App/Test/Codeception/Helper/NumberFormatHelper.php',
                __DIR__ . '/tests/App/Test/Codeception/Helper/SymfonyHelper.php',
                __DIR__ . '/tests/App/Test/Codeception/Module/Db.php'],
            PropertyPerClassLimitSniff::class => [
                __DIR__ . '/src/Model/Product/ProductData.php',
            ],
            'SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff.WriteOnlyProperty' => [
                __DIR__ . '/src/Model/Category/LinkedCategory/LinkedCategory.php',
                __DIR__ . '/src/Model/Order/Item/OrderItem.php',
                __DIR__ . '/src/Model/Order/PromoCode/PromoCode.php',
                __DIR__ . '/src/Model/Blog/Article/BlogArticleBlogCategoryDomain.php',
                __DIR__ . '/src/Model/Blog/Article/BlogArticleDomain.php',
                __DIR__ . '/src/Model/Blog/Category/BlogCategoryDomain.php',
                __DIR__ . '/src/Model/CategorySeo/ReadyCategorySeoMix.php',
                __DIR__ . '/src/Model/CategorySeo/ReadyCategorySeoMixParameterParameterValue.php',
                __DIR__ . '/src/Model/Order/PromoCode/PromoCodeFlag/PromoCodeFlag.php',
                __DIR__ . '/src/Model/Order/PromoCode/PromoCodeLimit.php',
                __DIR__ . '/src/Model/Order/PromoCode/PromoCodePricingGroup.php',
                __DIR__ . '/src/Model/Store/ProductStore.php',
            ],
            'SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff.UnusedProperty' => [
                __DIR__ . '/src/Model/Category/LinkedCategory/LinkedCategory.php',
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
            ListSyntaxFixer::class => null]
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

    $services->set(FrontendApiNamespaceSniffer::class);

    $services->set(ExtendedApiClassNamespaceSniffer::class);

    $services->set(CyclomaticComplexitySniff::class)
        ->property('absoluteComplexity', 19);

    $services->set(PhpdocToPropertyTypeFixer::class);

    $containerConfigurator->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);

    $services->set(CyclomaticComplexitySniff::class)
        ->property('absoluteComplexity', 19);
};

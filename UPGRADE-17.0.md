# UPGRADING FROM 16.x to 17.0

The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading

Since there are two possible scenarios for using Shopsys Platform, instructions are divided into these scenarios.

### You use our packages only

Follow the instructions in relevant sections, e.g. `shopsys/coding-standards` or `shopsys/http-smoke-testing`.

### You are developing a project based on the project-base repository

- upgrade only your composer dependencies and follow the instructions in the guide below
- upgrade locally first - after you fix all issues caused by the upgrade, commit your changes, test your application, and then continue with a deployment onto your server
- upgrade one version at a time:
    - start with a working application
    - upgrade to the next version
    - fix all the issues you encounter
    - repeat
- check the instructions in all sections; any of them could be relevant to you
- the typical upgrade sequence should be:
    - run `docker compose down --volumes` to turn off your containers
    - _(macOS only)_ run `mutagen-compose down --volumes` instead
    - follow upgrade notes in the _Infrastructure_ section (related to `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    - _(MacOS, Windows only)_ run `docker-sync start` to create volumes
    - run `docker compose build --no-cache --pull` to build your images without cache and with the latest version
    - run `docker compose up -d --force-recreate --remove-orphans` to start the application again
    - update the `shopsys/*` dependencies in `composer.json` to the version you are upgrading to
        - e.g., `"shopsys/framework": "v7.0.0"`
    - follow upgrade notes in the _Composer dependencies_ section (related with `composer.json`)
    - run `composer update shopsys/* --with-dependencies`
    - run `npm install` to update the NPM dependencies
    - follow all upgrade notes you have not done yet
    - run `php phing clean`
    - run `php phing db-migrations` to run the database migrations
    - test your app locally
    - commit your changes
    - run `composer update` to update the rest of your dependencies, test the app again, and commit `composer.lock`
- if any of the database migrations do not suit you, there is an option to skip it; see [our Database Migrations docs](https://docs.shopsys.com/en/latest/introduction/database-migrations/#reordering-and-skipping-migrations)
- we may miss something even if we care a lot about these instructions. In case something doesn't work after the upgrade, you'll find more information in the [CHANGELOG](CHANGELOG.md)

#### Movement of features from project-base to packages

- in this version, there are quite a lot of features that have been moved from `project-base` to the packages, mostly to the `framework` and the `frontend-api` package
- each section in the upgrade guide contains a link to the `project-base` diff and besides the particular upgrade instructions, there is also a list of the moved features you should be aware of (if there are any)
- if your project was originally not developed from the Commerce Cloud version, or it was developed on a version lower than `v13.0.0`, these feature movements should not affect you during the upgrade
- otherwise, you might need to adjust your project to the changes:
- if you had no custom changes in the moved features, you should be fine, you can safely remove the features from your project and use the ones from the packages (project-base diff in each section will help you with that)
- if you had custom changes in the moved features, you will need to adjust your project to the changes
- you should remove everything that was not modified in your project and keep just the custom changes using the recommended ways of the [framework extensibility](https://docs.shopsys.com/en/latest/extensibility/)
- one way or another, you should pay a special attention to the database migrations that were added with the feature movement. If they suit your needs, you should keep them and remove the original migrations from your project, otherwise, you should skip the newly added migrations.

#### Introduction of strict types

- with each change, we are updating most classes that have been altered by that change to use strict types
- this means that you will need to update your project to use strict types as well
- we do not see writing upgrade notes for such changes as beneficial, as it would mean for you to check every single change manually even if only a few occurrences would apply to your project
- we are currently not aware of easy way to automate this process, so you will need to do it manually
- probably the easiest way is to run `composer install`, `php phing standards-fix` and `php phing phpstan` commands, which will fail on errors caused by incompatibility strict types and fix those errors manually

### Upgrade from v17.0.0 to v17.0.1

#### fixed superadmin behavior ([#4267](https://github.com/shopsys/shopsys/pull/4267))

- method `Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade::getAllListableQueryBuilder()` is deprecated, use `getAllListableExcludingSuperadminQueryBuilder()` instead
- method `Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository::getAllListableQueryBuilder()` is deprecated, use `getAllListableExcludingSuperadminQueryBuilder()` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0fa823b7106720e2dbed1d6677017c491fe668bb) to update your project

#### moved frontend api roles to framework bundle ([#4270](https://github.com/shopsys/shopsys/pull/4270))

- classes were moved from the frontend api package to the framework package
    - `Shopsys\FrontendApiBundle\Component\Security\FrontendApiRoleHierarchyProvider` -> `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleHierarchyProvider`
    - `Shopsys\FrontendApiBundle\Component\Security\FrontendApiRoleProvider` -> `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleProvider`
    - warning: this is a potencial BC break, if you used the classes directly!

### Upgrade from v16.0.0 to v17.0.0

#### Implement store features ([#3413](https://github.com/shopsys/shopsys/pull/3413))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api packages:
    - `StoreAvailabilityResolverMap` class
    - `StoreQuery` class
    - `StoreResolverMap` class
    - `StoresQuery` class
    - `StoresBatchLoader` class
- `StoresQuery` class now uses new `StoreFacade` class from frontend-api package
    - Instead of `getStoresByDomainId` method, use `getFilteredStores` method
    - Instead of `getStoresCountByDomainId` method, use `getFilteredStoresCount` method
- method `getStoresCountByDomainId` in `StoreFacade` and `StoreRepository` in framework package was removed
- attribute `Store::$contactInfo` was removed. If you still want to use it, you need to implement it by yourself and ignore `Version20241216210633` migration which removes it from database
- new attributes `Store::$email`, `Store::$phone` and `Store::$directions` were introduced
- `StoreFormType` form was refactored into multiple groups. Look into the `StoreFormType` class to see the changes
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/133922d6802732a8e87c73c27b27b60ae0efc83c) to update your project

#### Admin Grid performance improvements ([#2460](https://github.com/shopsys/shopsys/pull/2460))

- Twig filter `getProductListDisplayName` from `ProductExtension` is removed. Use `getProductListDisplayNameByName` instead.
- Grid pulls out entire product (entity): Query is modified to return only the data that will be displayed later. Instead of entity, associated field is returned.
- add to `pg_trgm` extension to your postgreSQL database
    - you need to install the extension before running the migration `Version20241205144230`
    - for your production and devel environment. you need to install the extension manually (`CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA pg_catalog`)
    - localhost and CI is covered by `db-create` target.
    - check your code for usages of the `NORMALIZED(columnName) LIKE NORMALIZED(:text)` and create the indexes for corresponding columns the same way they are created in the migration `Version20241205144230`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/62877ed8332d2093de4e2019ac0e1883c6ddac86) to update your project
- see also [project-base diff](https://www.github.com/shopsys/project-base/commit/981b7ae7306202cc0eb85ee1bc912b43262b1abe) of [#3737](https://github.com/shopsys/shopsys/pull/3737) with an additional fix

#### add time-limited price lists with product special prices ([#3628](https://github.com/shopsys/shopsys/pull/3628))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `disabled` parameter in twig macro `productsWidgetItem` used in product picker
    - `filtering_minimal_price` and `filtering_maximal_price` elasticsearch fields in product price
- update Elasticsearch and Kibana to version 7.17.2 on all your environments
    - you can use docker images `docker.elastic.co/elasticsearch/elasticsearch:7.17.2` and `docker.elastic.co/kibana/kibana:7.17.2`
    - the minimum required version is 7.11, otherwise you cannot use the runtime fields to filter, search and get proper aggregations by price
- `Shopsys\FrameworkBundle\Controller\Admin\ProductPickerController::pickMultipleAction()` now have to accept optional `withPrice` argument and render the proper js class in the `@ShopsysFramework/Admin/Content/ProductPicker/listGrid.html.twig` template
- `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser`
    - methods `calculatePriceForCurrentUser()` and `calculatePriceForPricingGroup()` now respects the special price set in the price list
    - if you need the basic price without the special price applied, for example to render the original price on storefront, use `calculateBasicPriceForCurrentUser()` method
    - check the usage of the methods if it matches your needs
- check your custom implementation of `Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery` and adjust the custom logic regarding the new runtime fields `max_current_selling_price_with_vat` and `min_current_selling_price_with_vat`
    - those fields are used for sorting, filtering and price min/max aggregations
- GraphQL field `price` on `Product` is now required
- GraphQL field `ProductPrice` was enriched with fields `nextPriceChange`, `percentageDiscount`, and `basicPrice`
    - see GraphQL documentation in introspection to learn the purpose of those fields
- `Shopsys\FrontendApiBundle\Model\Resolver\Price\PriceQuery::priceByProductQuery()` was removed, use the `Shopsys\FrontendApiBundle\Model\Resolver\Price\ProductPriceQuery::priceByProductQuery()` instead
    - this new query respects the special price set and returns the `PriceInfo` object that represents the improved `ProductPrice` GraphQL field
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d49d1ae31ce4da99684026d206ae24854d08e8ad) to update your project
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6492801a6ed1fb9c75533f9fcc1d6575abdca821) for additional fix

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d49d1ae31ce4da99684026d206ae24854d08e8ad) to update your project

#### Upgrade postgreSQL version to 17.4 ([#3659](https://github.com/shopsys/shopsys/pull/3659))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/85adec08da4e2b717852029184054cb97259046c) to update your project

#### Search in administration ([#3679](https://github.com/shopsys/shopsys/pull/3679))

- in the header in the administration it is now possible to search by the name of the sections that the given administrator has allowed according to his rights
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/68cebad53ce886d3ff1993ab56d58afd2f820fdf) to update your project

#### fix localization based on admin language for GrapesJS ([#3680](https://github.com/shopsys/shopsys/pull/3680))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9619b8e680408833d32a5956dc69da4cfd4d81f5) to update your project

#### improve administrator authentication code ([#3683](https://github.com/shopsys/shopsys/pull/3683))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `AdministratorController::editAction()` - moved permission check for editing administrator to the framework
- class `Shopsys\FrameworkBundle\Model\Security\Authenticator` was removed, use `Symfony\Bundle\SecurityBundle\Security` and `Symfony\Component\Security\Http\Authentication\AuthenticationUtils` instead
- exception `Shopsys\FrameworkBundle\Model\Security\Exception\LoginFailedException` was removed, check different implementation in `Shopsys\FrameworkBundle\Controller\Admin\LoginController`

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/67974e70f2832863d149e0c1ee6bbe98e5cc3b01) to update your project

#### category automated filters ([#3672](https://github.com/shopsys/shopsys/pull/3672))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `ProductsBatchLoader::loadByEntities()`
    - `ProductElasticsearchBatchProvider::getBatchedByEntities()`
    - `ProductElasticsearchBatchProvider::getFilterQuery()`
    - `ProductElasticsearchBatchProvider::getFilterQueryForCategory()`
    - `ProductElasticsearchBatchProvider::getFilterQueryForBrand()`
    - `ProductElasticsearchBatchProvider::getFilterQueryForFilterData()`
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `ProductExportFieldProvider::IS_SALE_EXCLUSION` with the related logic
- the following unused methods were removed with no replacement:
    - `Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory::createListableProductsBySearchText()`
    - `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getFilteredProductsByCategory()`
    - `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getFilteredProductsByCategoryCount()`
    - `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getFilteredProductsByBrand()`
    - `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getFilteredProductsByBrandCount()`
    - `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade::getVisibleProductById()`
    - `Shopsys\FrameworkBundle\Model\Product\ProductRepository::getVisible()`
- obsolete `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface` was removed, you should directly use `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade` instead
- `Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory` class has changed:
    - `createListableProductsByCategoryId()` was renamed to `createListableProductsByCategory()` and accepts `Category` instance instead of its ID
    - `createListableProductsByBrandId()` was renamed to `createListableProductsByBrand()` and accepts `Brand` instance instead of its ID
- `Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData` now uses object `$entity` instead of `$entityId` and `$entityClass`
- `Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterElasticFacade::getProductFilterDataInCategory()` now accepts `Category` instance instead of its ID
- `Shopsys\FrameworkBundle\Model\Product\Filter\ProductOnCurrentDomainElasticFacade::getProductFilterCountDataInCategory()` now accepts `Category` instance instead of its ID
- `Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery::filterByCategory()` now accepts only one category instead of an array of categories
    - in order to filter products in a category along with the category automated filters, you should always use `Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterFacade::applyFiltersByCategory()`
- `Shopsys\FrontendApiBundle\Model\Resolver\Products\BestsellingProductsQuery` now uses new `productsSellableInCategoryByIdsBatchLoader` (alias `products_sellable_in_category_by_ids_batch_loader`) instead of `productsSellableByIdsBatchLoader` (alias `products_sellable_by_ids_batch_loader`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6771b44361b11f19f6929d82d6238c1b7ff52fc8) to update your project

#### updated email templates ([#3711](https://github.com/shopsys/shopsys/pull/3711))

- `Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail::TRANSPORT_VARIABLE_TRACKING_NUMBER` constant was renamed to `VARIABLE_TRANSPORT_TRACKING_NUMBER`
- `Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail::TRANSPORT_VARIABLE_TRACKING_URL` constant was renamed to `VARIABLE_TRANSPORT_TRACKING_URL`
- `Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder::getContentBaseUrl()` protected method was replaced with new public `getMailImageSrc()` method
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/71ba1be1eba6f0c75b6a111252038cd40ee90a44) to update your project

#### add colors to data fixtures of flags ([#3721](https://github.com/shopsys/shopsys/pull/3721))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/dc4f9fb323ae43ad3613aa9dd73b4b4a5b6892ba) to update your project

#### remove linked categories ([#3718](https://github.com/shopsys/shopsys/pull/3718))

- Frontend API field `Category#linkedCategories` has been removed along with its loader
- if your project uses this functionality
    - skip the migration `Shopsys\FrameworkBundle\Migrations\Version20250324141622`
    - skip removing the code
    - add the field `linkedCategories` to the `Category` type in your project in Frontend API schema
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/65f0a1db440fa76f736771e2a47d4a3a70ecdec7) to update your project

#### order mail: hide total price for customers that are not allowed to see prices ([#3719](https://github.com/shopsys/shopsys/pull/3719))

- `Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper::HIDDEN_FORMAT` constant was removed, use `Shopsys\FrameworkBundle\Component\Money\HiddenMoney::HIDDEN_FORMAT` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7ca2ee6ced767fbb9ea282dcd3b78da1a84b1cc0) to update your project

#### Removed the Multidomain design functionality ([#3720](https://github.com/shopsys/shopsys/pull/3720))

    - The `styles_directory` configuration option was removed from `project-base/app/config/domains.yaml`
    - The `design_id` configuration option was removed from `project-base/app/config/domains.yaml`
    - The `styles_directory` configuration option was removed from `project-base/app/tests/App/Functional/Component/Domain/Config/test_domains.yaml`
    - The `design_id` configuration option was removed from `project-base/app/tests/App/Functional/Component/Domain/Config/test_domains.yaml`
        - property `Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig::$stylesDirectory` was removed
        - property `Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig::$designId` was removed
        - class `Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterMultiDesignFilesystemLoaderCompilerPass` was removed
        - class `Shopsys\FrameworkBundle\Component\Domain\Multidomain\Twig\FilesystemLoader` was removed
        - class `Shopsys\FrameworkBundle\Component\Domain\Multidomain\Twig\Exception\MissingDependencyException` was removed
        - the following unused methods were removed with no replacement:
            - `Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig::getStylesDirectory()`
            - `Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig::getDesignId()`
            - `Shopsys\FrameworkBundle\Component\Domain\Domain::getDesignId()`

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1f54d200f8c6ab8d190c9aff634b9f740b883c64) to update your project

#### add graphql schema validation before schema generating ([#3722](https://github.com/shopsys/shopsys/pull/3722))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3b85113fd3e57bace7af1e445bcabb07b9ac9866) to update your project

#### extensibility enhancements ([#3724](https://github.com/shopsys/shopsys/pull/3724))

- `Shopsys\FrameworkBundle\Model\Product\ProductDeleteResult` class was removed without replacement
- `Shopsys\FrameworkBundle\Model\Product\Product::getProductDeleteResult()` method was removed, the logic is now implemented directly in `Shopsys\FrameworkBundle\Model\Product\ProductFacade::delete()` method
- the following classes are now final:
    - `Shopsys\FrameworkBundle\Model\Pricing\Price`
    - `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice`
    - `Shopsys\FrameworkBundle\Model\Order\OrderTotalPrice`
    - `Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice`
    - if you extended any of these classes, you need to adjust your code
        - for each of the classes, there is now a corresponding interface that you can use to implement your own logic, e.g.:

        ```diff
        - class AppProductPrice extends Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
        + class AppProductPrice implements Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface
        ```

        - the interfaces are now also used in the typehints instead of the original class throughout the code so if you extended such a method, you need to adjust your code as well, e.g.:

        ```diff
        - public function someMethod(ProductPrice $productPrice)
        + public function someMethod(ProductPriceInterface $productPrice)
        ```

- `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice` was removed, use `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice` instead
    - `ProductFacade::getAllProductSellingPricesIndexedByDomainId()` was renamed to `getAllProductPricesIndexedByDomainId()`
    - `ProductFacade::getAllProductSellingPricesByDomainId()` was renamed to `getAllProductPricesByDomainId()`
    - `ProductPricesWithVatSelectType` option `selling_prices` was renamed to `product_prices`
    - `PricesWithCalculatedSellingPricesType` was renamed to `PricesByPricingGroupsType` and its option `selling_prices` was renamed to `product_prices`
        - the corresponding twig theme (`pricesWithCalculatedSellingPricesType.html.twig`) was renamed to `pricesByPricingGroupsType.html.twig`
        - the corresponding twig block changed from `prices_with_calculated_selling_prices_row` to `prices_by_pricing_groups_row`
    - `MoneyWithCalculatedPriceType` was renamed to `PricingGroupPriceType` and its option `selling_price` was renamed to `product_price`
        - the corresponding twig block changed from `prices_with_calculated_selling_prices_input_row` to `pricing_group_price_input_row`
- `Shopsys\FrameworkBundle\Model\Order\OrderTotalPrice` class was changed:
    - `getPriceWithVat()` was removed, you can use to `getPrice()->getPriceWithVat()` instead
    - `getPriceWithoutVat()` was removed, you can use to `->getPrice()->getPriceWithoutVat()` instead
    - `getProductPriceWithVat()` was removed, you can use to `->getProductPrice()->getPriceWithVat()` instead
    - `getProductPriceWithoutVat()` was removed, you can use to `->getProductPrice()->getPriceWithoutVat()` instead
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `ProductExportRepository::getMaximalVariantPriceForFilteringMinimalPrice()`
    - `ProductExportRepository::getMinimalVariantPriceForFilteringMaximalPrice()`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/95e4a22f0af2907f427efff28ce01279f0e854ee) to update your project

#### interfaces cleanup ([#3732](https://github.com/shopsys/shopsys/pull/3732))

- all unnecessary exception interfaces, entity factory interfaces, and entity data factory interfaces were removed
    - if you used any of the exception interfaces in your try-catch block, you need to catch the particular exception now instead
    - if you used any of the interfaces in your code, you need to use the corresponding implementation from the shopsys package instead, e.g.:

    ```diff
        /**
         * @param \Doctrine\ORM\EntityManagerInterface $em
    -    * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleFactoryInterface $cronModuleFactory
    +    * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleFactory $cronModuleFactory
         */
        public function __construct(
            protected readonly EntityManagerInterface $em,
    -       protected readonly CronModuleFactoryInterface $cronModuleFactory,
    +       protected readonly CronModuleFactory $cronModuleFactory,
        ) {
    ```

    - if you implemented any of the interfaces in your code, you need to extend the corresponding implementation from the shopsys package instead, e.g.:

    ```diff
    - class CategoryDataFactory implements Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface
    + class CategoryDataFactory extends Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory

    # services.yaml:

    - Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface:
    + Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory:
        alias: App\Model\Category\CategoryDataFactory
    ```

- unused `Shopsys\FrameworkBundle\Model\Product\Product::checkIsNotMainVariant()` method was removed, similar check is already performed within `setAsMainVariant()` method
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d151d45bb1f6afc5f5d45d267c9c8d53a7b85eaf) to update your project

#### fix MissingParamAnnotationsFixer for multiline params ([#3736](https://github.com/shopsys/shopsys/pull/3736))

- If you have custom implementation of PHP CS Fixer then these changes may affect you:
    - `Shopsys\CodingStandards\CsFixer\Phpdoc\OrderedParamAnnotationsFixer` was replaced by `PhpCsFixer\Fixer\Phpdoc\PhpdocParamOrderFixer`
    - `Shopsys\CodingStandards\CsFixer\Phpdoc\MissingParamAnnotationsFixer` now correctly recognize multiline params

#### movements of features from project-base to packages ([#3735](https://github.com/shopsys/shopsys/pull/3735))

- `LegalConditionsSettingFormType` was split into `PrivacyPolicySettingFormType` and `TermsAndConditionsSettingFormType`
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `CspHeaderController` and all the related logic (see the usages of `Setting::CSP_HEADER` constant)
    - `FlagController` - `editAction`, `newAction`, `deleteAction`, `deleteConfirmAction`, and all the related logic
    - `FlagData::$urls` and all the related logic
    - whole language constant agenda in admin (see `LanguageConstantController`)
    - `LegalConditionsController::privacyPolicyAction()`
    - `LegalConditionsController::termsAndConditionsAction()`
    - whole notification bars agenda in admin (see `NotificationBarController`)
    - `ProductController::catnumExistsAction()`
    - `ProductController::productNamesByCatnumsAction()`
    - `ProductFacade::findAllByCatnums()`
    - `ProductRepository::findAllByCatnums()`
    - all the menu entries defined in `SideMenuConfigurationSubscriber` are now moved directly to `SideMenuBuilder`
    - `Admin/Content/StorefrontCache/clean.html.twig` twig template
    - `Product::$productVideos` property, `ProductVideo` entity, and all the related logic
    - `SliderItem::$datetimeVisibleFrom` and `$datetimeVisibleTo` properties
    - `SliderItemRepository::getAllVisibleByDomainId()` method extension
    - `SliderItemRepository::getSliderItemQueryBuilder()` method
    - `advert.js` javascript file
    - `validateUniquePromoCodeByDomain` in `validationPromoCode.js` javascript file
    - `productDetailFormTheme.html.twig` twig template
    - extensions of the following twig templates:
        - `Admin/Content/Administrator/detail.html.twig`
        - `Admin/Content/Flag/listGrid.html.twig`
        - `Admin/Content/Slider/detail.html.twig`
        - `Admin/Content/Slider/edit.html.twig`
        - `Admin/Content/Slider/list.html.twig`
        - `Admin/Content/Slider/new.html.twig`
        - `Admin/Content/TopCategory/list.html.twig`
        - `Admin/Content/TopProduct/list.html.twig`
        - `Admin/Content/TransportAndPayment/list.html.twig`
        - `Admin/Content/Unit/list.html.twig`
        - `Admin/Form/productParameterValue.html.twig`
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `Breadcrumb` interface graphql type (`BreadcrumbDecorator.types.yaml`)
    - `Slug` interface graphql type (`SlugDecorator.types.yaml`)
    - `ProductListable` interface graphql type (`ProductListableDecorator.types.yaml`)
    - `Flag.products` graphql field
    - `Flag.breadcrumb` graphql field
    - `Flag.categories` graphql field
    - `Brand.breadcrumb` graphql field
    - `Brand.slug` graphql field
    - language constant types and query (`LanguageConstantQuery` and `LanguageConstantDecorator.types.yaml`)
    - notification bars types and query (`NotificationBarsQuery` and `NotificationBarDecorator.types.yaml`)
    - `Product.productVideos` graphql field and `VideoToken` type (`VideoTokenDecorator.types.yaml`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/af4faf166d70cf7faad5411300a49881b01969a0) to update your project

#### many of the static methods are no longer static but public only for better extensibility ([#3715](https://github.com/shopsys/shopsys/pull/3715)))

- many of the static methods are no longer static but public only, run `php phing standards-fix phpstan` to find such occurrences and fix them in your project
- `Shopsys\FrameworkBundle\Model\Category\CategoryRepository::MOVE_DOWN_TO_BOTTOM` has been removed
- `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository::getProductParameterValuesByParameter()` has been removed
- `Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver\DateTimeDataTypeResolver::DATE_TIME_FORMAT_WITH_TIMEZONE` and `Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver\DateTimeDataTypeResolver::DATE_TIME_FORMAT_FOR_HUMAN` has been removed
- `Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem::SMALL_IMAGE_SIZE`, `Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem::MEDIUM_IMAGE_SIZE` and `Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem::LARGE_IMAGE_SIZE` has been moved to `\Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItemFactory`
- `Shopsys\ProductFeed\LuigisBoxBundle\Model\LuigisBoxProductFeedItem::SMALL_IMAGE_SIZE`, `Shopsys\ProductFeed\LuigisBoxBundle\Model\LuigisBoxProductFeedItem::MEDIUM_IMAGE_SIZE` and `Shopsys\ProductFeed\LuigisBoxBundle\Model\LuigisBoxProductFeedItem::LARGE_IMAGE_SIZE` has been moved to `\Shopsys\ProductFeed\LuigisBoxBundle\Model\LuigisBoxProductFeedItemFactory`
- `Shopsys\FrameworkBundle\Component\String\EncodingConverter` has been removed
- `Shopsys\FrameworkBundle\Component\String\TransformString` has been renamed to `Shopsys\FrameworkBundle\Component\String\TransformStringHelper`
- `Shopsys\FrameworkBundle\Component\String\TransformStringHelper::replaceInvalidUtf8CharactersByQuestionMark()` has been removed
- `Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorter` has been renamed to `Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorterHelper`
- `Shopsys\FrameworkBundle\Component\Utils\Utils::getArrayValue()` has been removed
- `Shopsys\FrameworkBundle\Form\Admin\CategorySeo\ReadyCategorySeoCombinationFormType` `choseCategorySeoMixCombinationJson` input has been renamed to `selectedCategorySeoMixCombinationJson`
- `Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ChoseCategorySeoMixCombinationIsNotValidException` has been renamed to `Shopsys\FrameworkBundle\Model\CategorySeo\Exception\SelectedCategorySeoMixCombinationIsNotValidException`
- `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix::$choseCategorySeoMixCombinationJson` has been renamed to `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix::$selectedCategorySeoMixCombinationJson`
- `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix::getChoseCategorySeoMixCombinationJson()` has been renamed to `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix::getSelectedCategorySeoMixCombinationJson`
- `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData::$choseCategorySeoMixCombinationJson` has been renamed to `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData::$selectedCategorySeoMixCombinationJson`
- `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixDataFactory::fillValuesFromChoseCategorySeoMixCombination()` has been renamed to `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixDataFactory::fillValuesFromSelectedCategorySeoMixCombination()`
- `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade::findByChoseCategorySeoMixCombination()` has been renamed to `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade::findBySelectedCategorySeoMixCombination()`
- `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixRepository::findByChoseCategorySeoMixCombination` has been renamed to `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixRepository::findBySelectedCategorySeoMixCombination()`
- `Shopsys\FrameworkBundle\Model\CategorySeo\ChoseCategorySeoMixCombination` has been renamed to `Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombination`
- `Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombination::createFromJson()` and `Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombination::createFromArray()` has been moved to `Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombinationFactory`
- `Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombination::getSelectedCategorySeoMixCombinationArray()` has been moved to `Shopsys\FrameworkBundle\Model\CategorySeo\SelectedCategorySeoMixCombinationFactory::createArrayFromSelectedCategorySeoMixCombination()`
- `Shopsys\FrontendApiBundle\Model\Resolver\Cart\CartInputDefaultValueInitializer` has been removed and its code was moved to method from which it was called
- `Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFile::setNameAndSlug()` has been removed and its logic has been moved to the appropriate factories
- `Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface::setNameAndSlug()` has been removed and its logic has been moved to the appropriate factories
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a7fd9b5c1823c6be9e57b319b0d835183083dbd3) to update your project

#### category special prices automated filter ([#3754](https://github.com/shopsys/shopsys/pull/3754))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d3b4e9fb5cd5da9c878110f08fbeb4d7d2a8892b) to update your project

#### new B2B customer user roles for cart and order access and manipulation ([#3756](https://github.com/shopsys/shopsys/pull/3756))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `lastOrderQuery`
    - `orderSentPageContent`, `orderPaymentSuccessfulContent`, and `orderPaymentFailedContent` queries
- there are new customer roles for B2B domain - `ROLE_API_CART_AND_ORDER_CREATION` and `ROLE_API_COMPANY_ORDERS_VIEW`
    - `ROLE_API_CART_AND_ORDER_CREATION` is added to all the customers in `Version20250124125113` - check the migration whether it suits your needs
- FE API: `CurrentCustomerUserDecorator.roles` field is now an array of new `CustomerUserRoleEnum`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2306f2fa0435e6ae8f7342fd11bbdc832ffeaac9) to update your project

#### extend complaints with preferred resolution ([#3759](https://github.com/shopsys/shopsys/pull/3759))

- Migration `Version20250127142754` sets default resolution as `fix` for all existing complaints, skip migration and create new one if you want to change it
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4259cf1ef76d24fb9e449a73dc79549a536fc0ab) to update your project

#### limit resources for Elasticsearch on local ([#3763](https://github.com/shopsys/shopsys/pull/3763))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cf9eff6034a97fdf85b81ca5b37eaf886fbef1d0) to update your project
- make the same changes in your uncommitted `docker-compose.yml` file and recreate the `elasticsearch` container

#### minor dependencies fixes ([#3764](https://github.com/shopsys/shopsys/pull/3764))

- add the missing `sentry/sentry` dependency to your `composer.json` file
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6ee5fb002a9d42d8fd9846930e08ba50cc6a1bb3) to update your project

#### new B2B customer user roles for complaints ([#3768](https://github.com/shopsys/shopsys/pull/3768)

- there are new customer roles for B2B domain - `ROLE_API_COMPLAINT_CREATION` and `ROLE_API_COMPANY_COMPLAINTS_VIEW`
    - `ROLE_API_COMPLAINT_CREATION` is added to all the customers in `Version20250130153449` - check the migration whether it suits your needs
- the customer users structure was changed on FE API so it is possible to distinguish among logged/unlogged and company/regular customers
    - check the new schema and update your queries and mutations as they might need to be updated to reflect the new structure
    - the structure now looks like this:
        - `BaseCustomerUser` (interface) provides basic fields that are common for all customer users.
        - `CurrentCustomerUser` (interface) extends `BaseCustomerUser` and adds information about the last login.
        - `RegularCustomerUser` (object type) implements `BaseCustomerUser`, representing a basic individual customer
        - `CompanyCustomerUser` (object type) implements `BaseCustomerUser`, representing a basic company customer (with `companyName`, `companyNumber`, etc.).
        - `CurrentRegularCustomerUser` (object type) extends `RegularCustomerUser` and implements `CurrentCustomerUser` (adding last login information), represents a logged-in regular customer.
        - `CurrentCompanyCustomerUser` (object type) extends `CompanyCustomerUser` and implements `CurrentCustomerUser` (adding last login information), represents a logged-in company customer.
    - check also the resolver map defined in `Shopsys\FrontendApiBundle\Model\Resolver\Customer\User\CustomerUserResolver` and if you extended the class, modify your extension appropriately
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `CurrentCustomerUserQuery` extension (`currentCustomerUserQuery` now may return `null`)
    - `PersonalDataMutation`
    - `PersonalDataQuery`
    - `NewsletterSubscriber`, `PersonalData`, `PersonalDataAccessRequestInput`, `PersonalDataAccessRequestTypeEnum`, and `PersonalDataPage` GraphQL API types definitions (there are now new decorators for these types)
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `PersonalDataExportFacade`
    - `PersonalDataExportMail::getVariablePersonalDataAccessUrl()` extension
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e1f8ea4fd7b90ed59a6699773b9fc54ec5926f99) to update your project

#### replace custom webfont icons with tabler icons provided by ux_icons ([#3765](https://github.com/shopsys/shopsys/pull/3765))

- all icons are now rendered by the `ux_icons` twig function provided by the `symfony/ux-icons` package
    - see the documentation page [Icons](https://docs.shopsys.com/administration/icons) to learn how to render icons the right way
- all your custom icons, rendered as `<i class="svg svg-{iconName}"></i>`, should be replaced with the `ux_icons` function
- you can use upgrade command `bin/console upgrade:replace-obsolete-icons` to replace all custom icons in your project in twig templates
    - check if all icons were replaced correctly and are displayed as expected (search for `svg-` in your codebase)
    - your custom icons may be used referenced via alias or directly by their name. It depends on your preferences
    - remember to run `php bin/console ux:icons:lock` to download any custom icons you are using and commit them
    - see the documentation page [Icons](https://docs.shopsys.com/administration/icons#1-using-icons-in-twig-templates) for more information
- icons in JavaScript files should be replaced manually, see the documentation page [Icons](https://docs.shopsys.com/administration/icons#2-using-icons-in-javascript) for more information
- if necessary, improve CSS styles, so the SVG icons are displayed correctly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8dd67886bc6d1b98a4bb0cf128c6c01bda24f294) to update your project, see also [project-base diff](https://www.github.com/shopsys/project-base/commit/8dd67886bc6d1b98a4bb0cf128c6c01bda24f294)-3817 for fixed gitlab check

#### fix file upload roles ([#3777](https://github.com/shopsys/shopsys/pull/3777))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/51acb3c64784d1fa12e939468156b9526e2a0767) to update your project

#### fix email templates for Outlook classic ([#3779](https://github.com/shopsys/shopsys/pull/3779))

- added Outlook specific styles and classes to the email templates

#### add product recalculation queue deduplication ([#3669](https://github.com/shopsys/shopsys/pull/3669))

- ensure you have Redis on version 7.4 (see [#3673](https://github.com/shopsys/shopsys/pull/3673))
- method use `\Shopsys\FrameworkBundle\Model\Product\ProductFacade::iterateAllProductIds()` was removed, use `iterateAllProductIdsExceptVariant()`instead
- method use `\Shopsys\FrameworkBundle\Model\Product\ProductRepository::iterateAllProductIds()` was removed, use `iterateAllProductIdsExceptVariant()`instead
- `\Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage` no longer contain `exportScopes`. Scopes are now stored in Redis.
    - check also `ProductRecalculationPriorityHighMessage` and `ProductRecalculationPriorityRegularMessage`
- `Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationRepository`
    - method `getIdsToRecalculate()` was removed
    - method `getMainVariantIdsOfVariants()` was removed
    - method `dropVariantIdsFromProductIds()` was removed
    - method `getVariantIds()` was removed
    - use `replaceVariantIdsWithMainVariantIds()` or `getIdsToRecalculateByMainVariantIds()` instead
- check the documentation for more details about [deduplication of product recalculation queue](https://docs.shopsys.com/asynchronous-processing/product-recalculations#product-deduplication-and-batch-dispatching)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6738da6aca221ef32a8455ed026ea374160a62cd) to update your project

#### fix YAML Standards checking ([#3781](https://github.com/shopsys/shopsys/pull/3781))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/63f0440285e37fe638e43e8e1c8c0b7bac5dc2dc) to update your project

#### fix price filtering for products with special prices ([#3782](https://github.com/shopsys/shopsys/pull/3782))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6e4ac47680146d6262c1adcb0b1da9898de18a64) to update your project

#### manual complaint creation (without order) ([#3784](https://github.com/shopsys/shopsys/pull/3784))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b9402a9b4507fa7adb0b41c4e188f3f0cf7efcd2) to update your project

#### rename contact page to contact-form page ([#3776](https://github.com/shopsys/shopsys/pull/3776))

- removed demodata for contact page due to duplication with users new contact page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/07cf474599f16c7b858eef64a2cb8803b04136d6) to update your project

#### simplified rendering form with sticky save button ([#3789](https://github.com/shopsys/shopsys/pull/3789))

- check your extended templates from `@ShopsysFrameworkBundle` in `app/templates/bundles/ShopsysFrameworkBundle`
    - replace custom form rendering with simple `{{ form(form) }}`
    - it's no longer necessary to use `{% embed '@ShopsysFramework/Admin/Inline/FixedBar/fixedBar.html.twig' %}` in forms, use `Shopsys\FormTypesBundle\ActionBarType` instead
    - check the [documentation page](https://docs.shopsys.com/introduction/using-form-types/#actionbartype) for more details

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/092b51de33829e07e662a9fb37e90eb8dcc96bac) to update your project

#### update your tests to work with both English + Eur and Czech + CZK ([#3792](https://github.com/shopsys/shopsys/pull/3792))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f376b68618a0d9d988c796d87350e8257ff7e632) to update your project

#### fix Gitlab security check ([#3800](https://github.com/shopsys/shopsys/pull/3800))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7a5ea778527a8a7efb4a5be3ce2f3b3bb60e0685) to update your project

#### remove no longer necessary image migration for proxy ([#3801](https://github.com/shopsys/shopsys/pull/3801))

- remove `Version20231020173331` migration from your `migrations-lock.yaml`
- `Shopsys\FrameworkBundle\Command\MigrateImagesCommand` has been removed
- `Shopsys\FrameworkBundle\Component\Setting\Setting::IMAGE_STRUCTURE_MIGRATED_FOR_PROXY` has been removed
- `migrate-images-for-proxy` phing target has been removed
- if you are upgrading from Shopsys Platform 14.0 or newer, you do not need to do any other steps
- if you are upgrading from Shopsys Platform 13.0 or older, you need to check this PR and do the migration of images folder manually
    - note: in version 14.0 we have changed image folder structure and introduced migration command to easily switch to this new structure, after three versions we are removing this command as it was one time action and is no longer necessary
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a3fd2ad8bcf59f0c44aad44fa55d42a354842dbe) to update your project

#### ROLE_API_CUSTOMER_SELF_MANAGE tweaks ([#3788](https://github.com/shopsys/shopsys/pull/3788))

- `Shopsys\FrontendApiBundle\Voter\CustomerUserVoter::isRoleApiCustomerSelfManageGranted()` was removed
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f8d9bc067277665b4139194451dc9859158425c7) to update your project

#### notification bars validity now includes time ([#3805](https://github.com/shopsys/shopsys/pull/3805))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/865ff3e2ee89bfe617459c2e6578d7e13fa6345e) to update your project

#### conflict codeception/codeception 5.2.0 ([#3806](https://github.com/shopsys/shopsys/pull/3806))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/729f6baa5315d6c30b2079669e0e5671f5c9f244) to update your project

#### change a route loading source for the administration bundle ([#3812](https://github.com/shopsys/shopsys/pull/3812))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/df99293bbd24f029f860e33f113cdea309a4de41) to update your project

#### Elasticsearch URLs are now relative instead of absolute ([#3811](https://github.com/shopsys/shopsys/pull/3811))

- Product Elasticsearch field `detail_url` has been removed, use `slug` instead, field `brand_url` has been removed, use `brand_slug` instead
- `BrandCachedFacade` has been removed as its functionality was redundant with `FriendlyUrlFacade::getMainFriendlyUrlSlug()` where is slug already cached
- BlogArticle Elasticsearch field `url` has been removed, use `slug` instead
- `ArticleExportScheduler` and `ArticleExportSubscriber` have been replaced with `ArticleExportMessageDispatcher` and `ArticleExportMessageHandler`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/235db887434acd032969a7f52b315c8bdfcd5182) to update your project

#### add support for single domain friendly URLs ([#3809](https://github.com/shopsys/shopsys/pull/3809))

- `Article` and `Store` friendly URL addresses are now restricted to only domain selected at the entity. Remove previously created friendly URLs on other domains to clean your application.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f7ee3f2518a24465bee1763f7e865165db5784c6) to update your project

#### Fix cart & missing whishlist/comparison products after external login ([#3822](https://github.com/shopsys/shopsys/pull/3822))

- for external logins you need to make sure to properly clear the localStorage entries
- if you have extended `loginAsCustomerUser.html.twig` template, you need to manually perform `handleActionsAfterLogin`, mainly reset `localStorage` entries for `cartUuid`, `productListUuids`, `authLoading` and `userEntry`

#### Create crud skeleton ([#3629](https://github.com/shopsys/shopsys/pull/3629))

- The `Shopsys\FrameworkBundle\Component\Grid\Grid::addColumn` method now accepts an array of options as the fifth argument. You can use it to set additional data for the column
    - `template` - string, path to the Twig template file that is used to render the field
    - `help` - string, the help text that is displayed next to label in column header
- A new Datagrid component was introduced. It is used to easily and quickly define data grids in administration. You can look into the documentation for [more information](https://docs.shopsys.com/en/17.0/administration/datagrid/) about the component
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c4c092e2861ec3c23845927e071efc45e7e23547) to update your project

#### improve administration asset loading ([#3815](https://github.com/shopsys/shopsys/pull/3815))

- `@shopsys/framework` NPM package is no longer used, and the asset files are now used directly from composer package `shopsys/framework`
- Phing build changes
    - the following properties were removed from Phing configuration file `build.xml`:
        - `path.eslint.executable`
        - `path.node_modules`
        - `path.node_modules.bin`
        - `path.stylelint.executable`
    - targets `eslint-check-diff` and `eslint-fix-diff` were removed, use `eslint-check` and `eslint-fix` instead
    - targets `eslint-check`, `eslint-fix`, `stylelint-check`, `stylelint-fix` now run appropriate script defined in `package.json` file
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/628c5234b08b0371f898bfd18e63723a269a9394) to update your project

#### remove generated error pages ([#3827](https://github.com/shopsys/shopsys/pull/3827))

- phing targets `error-pages-generate` and `test-error-pages-generate` were removed
- `shopsys:error-page:generate-all` command was removed
- Classes that handled errors were removed:
    - `Shopsys\FrameworkBundle\Controller\Admin\ErrorController`
    - `Shopsys\FrameworkBundle\Command\GenerateErrorPagesCommand`
    - `Shopsys\FrameworkBundle\Component\Error\Exception\BadErrorPageStatusCodeException`
    - `Shopsys\FrameworkBundle\Component\Error\Exception\FakeHttpException`
    - `Shopsys\FrameworkBundle\Component\Error\ErrorPageCronModule`
    - `Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade`
    - `Shopsys\FrameworkBundle\Component\Error\NotLogFakeHttpExceptionsErrorListener`
- You can now filter errors in Sentry by `errorId`
- if you are not using JS storefront, consider re-implementing this functionality in your project
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4df9557fec7cda20b705f19c63a842098816fe94) to update your project

#### update your application to reflect input price settings correctly ([#3836](https://github.com/shopsys/shopsys/pull/3836))

- the application now calculates prices for input price without VAT slightly differently, check these changes, to ensure your application is working correctly:
    - calculation of prices (products, transport and payment):
      price without VAT = input price without VAT rounded by Currency settings
      price with VAT = input price with applied VAT percent rounded using Currency settings
    - calculation of total price for product in cart:
      total price without VAT = price without VAT _ quantity
      total price with VAT = price with VAT _ quantity
- the application now reflects input price into account in places it did not before, check these changes, to ensure your limits are working correctly:
    - the free transport and payment limit is newly set to the input price type, which means if you have set the input price type as price without VAT, the transport and payment limit will be applied to the order price without VAT
    - the same applies to the promo code limits
- `Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation::calculateBasePriceRoundedByCurrency()` has been renamed to `calculateRoundedBasePrice()`
- `Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation::getBasePriceWithVatRoundedByCurrency()` has been removed as its functionality has been merged to `calculateRoundedBasePrice()`
- `Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation::getTotalPriceWithoutVat()` has been renamed to `getTotalPriceWithoutVatForInputPriceWithVat()`
- `Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation::getTotalPriceWithVat()` has been renamed to `getTotalPriceWithoutVatForInputPriceWithoutVat()`
- `Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation::getTotalPriceVatAmount()` has been renamed to `getTotalPriceVatAmountForInputPriceWithVat()`
- `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation::calculatePriceWithoutVat()` has been renamed to `calculatePriceWithoutVatForInputPriceWithVat()`
- `Shopsys\FrameworkBundle\Model\Pricing\PriceConverter::DEFAULT_SCALE` has been removed
- `Shopsys\FrameworkBundle\Model\Pricing\PriceConverter::convertPriceToInputPriceWithoutVatInDomainDefaultCurrency()` has been renamed to `convertPriceToInputPriceInDomainDefaultCurrency()`
- `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit::$fromPriceWithVat` has been renamed to `$fromPrice`
- `Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeLimitType` input `fromPriceWithVat` has been renamed to `fromPrice`
- `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\ApplyNominalPromoCodeMiddleware::calculateTotalApplicableProductsPriceAmountWithVat()` has been renamed to `calculateTotalApplicableProductsPrice()`
- `Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade::getRemainingPriceWithVat()` has been renamed to `getRemainingAmount()`
- `Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult::$remainingAmountWithVatForFreeTransport` has been renamed to `$remainingAmountForFreeTransport`
- `Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult::getRemainingAmountWithVatForFreeTransport()` has been renamed to `getRemainingAmountForFreeTransport()`
- `Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult::setRemainingAmountWithVatForFreeTransport()` has been renamed to `setRemainingAmountForFreeTransport()`
- Frontend API type `Cart::remainingAmountWithVatForFreeTransport` has been renamed to `remainingAmountForFreeTransport`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d7ec161e9233cd2b9231ef8cf940b42610d29dc6) to update your project

#### make navigation independent on czech domain presence ([#3837](https://github.com/shopsys/shopsys/pull/3837))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework packages:
    - `CategoryFacade::getFullPathsIndexedByIds()` method
    - `CategoryRepository::getFullPathsIndexedByIds()` method
    - `CategoryRepository::getPreOrderTreeTraversalForAllCategoriesQueryBuilder()` method
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/47ea1bc67e22124a1ad114e523b008559f81e166) to update your project

#### allow adding non-string constant to category data fixture ([#3854](https://github.com/shopsys/shopsys/pull/3854))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/46f902631e87c5830089cc4a396b82468b204476) to update your project

#### ChoiceType values are not automatically translated ([#3857](https://github.com/shopsys/shopsys/pull/3857))

- almost every use of the form field `ChoiceType` contains already translated values as they originate from the data, so by default the translation of the choice values was removed
- if you have used a `ChoiceType` that count on the automatic translation, set the `choice_translation_domain` option

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/52b1ea35971404c64d7ea799364aa8154300fac2) to update your project

#### Prices in mail templates reflect input price type setting ([#3865](https://github.com/shopsys/shopsys/pull/3865))

- MailTemplate variable `{total_price}` has been replaced with `{total_price_with_vat}` and `{total_price_without_vat}`
    - for this we have introduced migration `Shopsys\FrameworkBundle\Migrations\Version20250331122654` that replaces `{total_price}` with `{total_price_with_vat}` in order mail templates
    - skip this migration if you do not want to do this change
- new parameter `shopsys.mail_template.display_price` that accepts `selling_price` (default) or `both` values to set displayed values in mail templates for transport, payment and product price
- `Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail::VARIABLE_TOTAL_PRICE` has been replaced with `Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail::VARIABLE_TOTAL_PRICE_WITH_VAT` and `Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail::VARIABLE_TOTAL_PRICE_WITHOUT_VAT`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c5483af8ff5f383af37e01aa761bd1e09be9819) to update your project

#### make search by catnum case-insensitive on storefront ([#3870](https://github.com/shopsys/shopsys/pull/3870))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4385d96b61ac8296e61306db8538d64b1029ffe6) to update your project

#### Convert latitude and longitude to numeric to ensure the distance function works correctly ([#3873](https://github.com/shopsys/shopsys/pull/3873))

- `Store::latitude` and `Store::longitude` are now stored as `NUMERIC(20, 10)` in the database and this is changed via migration `Version20250320124625` and current values are converted to numeric
    - if you want to store latitude and longitude in a different format, you need to skip this migration and update your application appropriately
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/901c58315b4a56c8a5b0a720780a4450af113efa) to update your project
- see also [this commit](https://github.com/shopsys/shopsys/commit/1e79fc4924f75698cdce8c401da6148f00afa5df) with additional fix

#### remove old whitelist configuration for ingress ([#3875](https://github.com/shopsys/shopsys/pull/3875))

- use `WHITELIST_IPS` variable to define whitelist IPs for ingress. See: https://github.com/shopsys/deployment?tab=readme-ov-file#whitelist-ip-addresses
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/eb7ff8a289742b5f1c8936b192728f3ccc4af302) to update your project

#### admin locale now can be set to any locale ([#3881](https://github.com/shopsys/shopsys/pull/3881))

- if you extended `translations-dump` phing target, be sure to add `admin-locales-load` dependency and `${admin-locales}` argument
- `Localization::getLocale()` method was renamed to `getRequestLocale()` and returns the locale of the current request
    - i.e., in administration, it returns the admin selected locale that can be different from the domain locales
    - `Localization::getAdminLocale()` method was moved and renamed to `AdministratorLocalizationFacade::getCurrentAdminLocaleOrDefault()`
    - when you need to get the persisted entities translations in the current locale, use `Localization::getCurrentLocaleForTranslatableEntities()` method
        - the method has a fallback for the cases when the current admin locale differs from the domain locales (i.e. the entity translations are not available in the admin locale)
    - see [the docs](https://docs.shopsys.com/en/17.0/introduction/how-to-set-up-domains-and-locales/#36-locale-in-administration) for more information
- `Localization::checkAdminLocaleIsSupported()` method was moved and renamed to `AdministratorLocalizationFacade::isAdminLocaleSupported()` and made protected
- `Localization::getDefaultAdminLocale()` method was moved to `AdministratorLocalizationFacade`
- `Localization::getAllowedAdminLocales()` method was moved to `AdministratorLocalizationFacade`
- `CKEditorRendererDecorator::getRequestLanguage()` was removed (the same behavior is ensured by the base CK Editor Renderer)
- the following methods were removed from `Tests\FrameworkBundle\Test\Codeception\ActorInterface`:
    - `canSeeTranslationFrontend()`
    - `cantSeeTranslationFrontend()`
    - `checkOptionByLabelTranslationFrontend()`
    - `clickByTranslationFrontend()`
    - `dontSeeTranslationFrontend()`
    - `getFormattedPriceRoundedByCurrencyOnFrontend()`
    - `getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend()`
    - `getFrontendLocale()`
    - `seeTranslationFrontend()`
    - be sure to run `tests-acceptance-build` phing target to update the generated codeception classes
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/813cb9164e450477a76e1663524d4dcd35fef931) to update your project

#### add the selling price type to your application ([#3883](https://github.com/shopsys/shopsys/pull/3883))

- `PricingSetting::INPUT_PRICE_TYPE_WITH_VAT` was renamed to `PricingSetting::PRICE_TYPE_WITH_VAT`
- `PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT` was renamed to `PricingSetting::PRICE_TYPE_WITHOUT_VAT`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1c9c4480f24c8b3854e9f9206c3625c9d47337ed) to update your project

#### add the ability to easily remove entity columns from parent classes ([#2368](https://github.com/shopsys/shopsys/pull/2368))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c0baa87e1157ea335f47511db50213c46f19a953) to update your project

#### admin product detail: display main category full path ([#3889](https://github.com/shopsys/shopsys/pull/3889))

- `productMainCategories` field was removed from `displayAvailabilityGroup` in `ProductFormType`
    - instead, you can now pass `product` option to `CategoriesType` to display main categories full paths within the categories widget

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/78b67a5f6f84e4140bfd119d212977f0267bfd13) to update your project

#### merge services_frontend_api.yaml into services.yaml ([#3890](https://github.com/shopsys/shopsys/pull/3890))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c5477a1d1665e93f10145a48e2394a7e701c4965) to update your project

#### Upgrade Deployment package ([#3891](https://github.com/shopsys/shopsys/pull/3891))

- Before upgrading the deployment package, look closely at the changes in the deployment package: https://github.com/shopsys/deployment/compare/v3.3.4...v4.0.0
- Some manifests were updated and require a newer version of Kubernetes
- Upgrade the deployment package to version 4.0.0:
    - In your `app/composer.json` upgrade version of `shopsys/deployment` package:
        ```diff
        ...
        -    "shopsys/deployment": "^3.3.2",
        +    "shopsys/deployment": "^4.0.0",
        ...
        ```
    - Run `composer update shopsys/deployment`
    - Look at the changes in the deployment package and apply them to your project: https://github.com/shopsys/deployment/blob/main/UPGRADE.md

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fb55bf4e4dfbb87863f2a65b2a9aa074628b5d22) to update your project

#### Improve logging ([#3690](https://github.com/shopsys/shopsys/pull/3690))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/174cffab8830f4cdc3e4a5017dbbb57640a83f51) to update your project

#### fix search by catnum exact match ([#3900](https://github.com/shopsys/shopsys/pull/3900))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/da55608e44f46fd1819f9e7c901fb247532ee475) to update your project

#### Optimize image resizer with caching ([#3907](https://github.com/shopsys/shopsys/pull/3907))

- Method `Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader::getResizedProductImageUrl` was removed. Use `ProductUrlsBatchLoader::getProductImageUrl` method instead
- Discuss these changes with some SEO expert as they may affect SEO for images
    - If you don't want to change image behavior for robots just remove `header('X-Robots-Tag: noindex, nofollow');` on line 101 in `app/web/imageResizer.php`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/75e16258170967a72b34303c55b8149b0ef69891) to update your project

#### add button component, translations, styles ([#3908](https://github.com/shopsys/shopsys/pull/3908))

- added new GrapesJS button components for both regular content and mail templates
- new GrapesJS plugins added:
    - `grapesjs-custom-button-link-plugin.js` - button component for regular content
    - `grapesjs-mail-custom-button-link-plugin.js` - button component for mail templates
- added button-related translations to Czech and English locale files
- updated `MailTemplateBuilder.php` to support button components in mail templates
- added new CSS styles for button components in both admin GrapesJS editor and storefront user-text sections
- GrapesJS translations migrated from inline plugin definitions to centralized locale files (`cs.js` and `en.js`)
- all GrapesJS plugins updated to use centralized translation system instead of hardcoded strings
- added demo data for mail template with order detail button
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/845b311dd00076be9e7243991eaa70a0c4154b23) to update your project

#### Fix GrapesJS component Text with Image ([#3916](https://github.com/shopsys/shopsys/pull/3916))

- GrapesJS component Text with Image is now working correctly
    - added correct data type for image
    - fixed color of active property input
    - setup new class for component icon, because of new administration icon styles
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/aefb7f5b0d94a114f835e41a3139ffc2f5c8cfa4) to update your project

#### update your project to be prepared for future external payments and transports ([#3480](https://github.com/shopsys/shopsys/pull/#3480))

- FrontendApi `Payment` and `Transport` attribute `instruction` has been renamed to `instructions`
- `Payment::TYPE_BASIC` and `Payment::GOPAY` has been moved to `PaymentTypeEnum` class as `TYPE_BASIC` and `TYPE_GOPAY`
- `frontend-api` `Cart` object now has property `promoCodes (array)` instead of current `promoCode (string)` and includes additional data about promo codes
- `PromoCode::discountType` has changed the type from `int` to `string` in migration `Version20241009114907`, if you have extended these or added new types in your project, review provided migration
    - `PromoCode::DISCOUNT_TYPE_PERCENT` and `PromoCode::DISCOUNT_TYPE_NOMINAL` has been replaced by `PromoCodeTypeEnum` class
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/80266352345254e692e03a0576a55dd4cbaed161) to update your project

#### update unsupported currencies ([#3925](https://github.com/shopsys/shopsys/pull/3925))

- `Shopsys\FrameworkBundle\Model\Localization\IntlCurrencyRepository::getLegacyCurrenciesIndexedByCurrencyCodes()` method has been removed without replacement

#### fix disappearing RGB color of parameter values ([#3924](https://github.com/shopsys/shopsys/pull/3924))

- `ParameterValueData::$uuid` was removed without replacement
- `ParameterRepository::findOrCreateParameterValueByValueTextAndLocale()` was replaced by `findOrCreateParameterValueByParameterValueData()`
- `ProductParameterValuesLocalizedDataFactory::create()` was renamed to `createInstance()` and made protected
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `ProductFacade::saveParameters()` extension
    - `ParameterRepository::findOrCreateParameterValueByParameterValueData()`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/be535ad61d98f1ddcad58759d8afe8bcca6d7d5b) to update your project

#### make personal data page API contents non-mandatory ([#3882](https://github.com/shopsys/shopsys/pull/3882))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/329f1e5b8505604abc0d14a5860cfeb02ec5c824) to update your project

#### set domain icon edit page to be a standard edit page in administration ([#3942](https://github.com/shopsys/shopsys/pull/3942))

- if you have extended `DomainController::editAction()` or the `@ShopsysFramework/Admin/Content/Domain/edit.html.twig`, check the current implementation and adjust, according your needs
- the file `@shopsys/framework/js/admin/components/DomainIcon.js` was removed, you should have the edit page as a separate edit page instead of a modal window

#### code generation via custom Symfony makers ([#3808](https://github.com/shopsys/shopsys/pull/3808))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9ff1bcb425803a80448e69cab48e8686fccd0a1b) to update your project

#### fix creating a product with related products set ([#3941](https://github.com/shopsys/shopsys/pull/3941))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/486ff792afe1feb4d2cacb067778b2dfd6395193) to update your project

#### allow variants to be set as related product ([#3946](https://github.com/shopsys/shopsys/pull/3946))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6afb95f76a4273c80f98e9a7860956e01eb86eb6) to update your project

#### remove unnecessary order sent page data fixtures ([#3944](https://github.com/shopsys/shopsys/pull/3944))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5f9b5beed8c8dc59e8b141f4ecd5d5f02ef2b11b) to update your project

#### split customer translations to separate domain ([#3949](https://github.com/shopsys/shopsys/pull/3949))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f7bed309e7c3445d3186e105507dbbf9d7ae1839) to update your project
- dump translations using `php phing translations-dump`
- it is possible that some of your translations will be moved to a different translation file, check the translations and fill in the missing translations
- from now on, the translations used for the customers should use the `customerMessages` and `customerValidator` domains, so it's easier to translate only them if necessary
- the class `Shopsys\FrameworkBundle\Model\Breadcrumb\SimpleBreadcrumbGenerator` was removed as it's not used anymore

#### implement attribute-based access control system for admin routes and RBAC system ([#3933](https://github.com/shopsys/shopsys/pull/3933)) and ([#4072](https://github.com/shopsys/shopsys/pull/4072))

- **RBAC System Overhaul**: Complete refactoring of role-based access control system
    - `App\Model\Security\Roles` class was **removed** - custom roles are now defined using `RoleProviderInterface` implementations
    - roles are now managed through context-aware providers (AdminRoleProvider, CoreAdminRoleProvider, etc.)
    - see the [RBAC documentation](https://docs.shopsys.com/en/17.0/introduction/role-based-access-control/#creating-custom-roles) for the new role definition approach

- **Administrator Role Management**:
    - administrators with `ROLE_ALL` or `ROLE_ALL_VIEW` are automatically migrated to use system-managed "Role Groups" during database migration
    - administrators can no longer edit their own roles for security
    - `AdministratorFormType`, `AdministratorRoleGroupFormType`, and `CustomerUserRoleGroupFormType` now use the new `RolesType` form with permission grid instead of simple select dropdowns
    - `RolesType` form was completely rewritten and split into sections with permission checkboxes (VIEW, CREATE, EDIT, DELETE)
    - system role constants (`ROLE_ALL`, `ROLE_ALL_VIEW`, `ROLE_ADMIN`, etc.) are now located in the `SystemRole` class instead of being scattered across different files
    - you can simplify admin role permissions by setting `simple_permissions: true` in `shopsys_administration.yaml` configuration to show only VIEW and FULL permissions instead of granular ones

- **Access Control Changes**:
    - access control is now managed through PHP attributes on controller actions: `#[ForRole('ROLE_NAME')]`, `#[CanView]`, `#[CanEdit]`, etc.
    - no longer necessary `MenuItemsGrantedRolesSetting` was removed, the menu items are now removed automatically based on the access control setting

- **Development Changes**:
    - make sure to use `Symfony\Component\Routing\Attribute\Route` attribute for your routes instead of `Symfony\Component\Routing\Annotation\Route`
    - if you extend any shopsys controller action, copy the new role-based attributes from the parent class
    - `MenuItemsGrantedRolesSubscriber::removeItemFromMenu()` method was removed without replacement
    - `Shopsys\FrontendApiBundle\Controller\CustomerUserController` was moved to `Admin` subfolder (`Shopsys\FrontendApiBundle\Controller\Admin\CustomerUserController`) and uses the `#[Route]` and new role-based attributes
    - class `Shopsys\FrameworkBundle\Controller\Admin\AccessControl` was removed. Implementation of `AccessDeniedHandlerInterface` is used instead.

- **Migration Notes**:
    - no manual action is required for existing administrators - the migration handles role group assignment automatically
    - custom roles must be migrated from the old `App\Model\Security\Roles` class to the new provider system

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bbd3adb01cba4dc7e58d73f34558e94528e5b426) and [project-base diff](https://www.github.com/shopsys/project-base/commit/d185e8a894e314bd6d89ac60cf9be3d6ed566320) to update your project

#### improve ProductVisibilityFacadeTest ([#3961](https://github.com/shopsys/shopsys/pull/3961))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3c58ceef7e1ba4f8ae35357f1a7b4d7e1be4a3a7) to update your project

#### yaml standards: ignore kubernetes config files ([#3964](https://github.com/shopsys/shopsys/pull/3964))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/55dbcf15c91d3f17d8d0cd4528ce6b11f382120d) to update your project

#### move creating flag demo data from migrations to data fixtures ([#3965](https://github.com/shopsys/shopsys/pull/3965))

- db migrations `App\Migrations\Version20200221155940` and `App\Migrations\Version20200714071640` were removed
    - if you plan to keep them, skip the `Shopsys\FrameworkBundle\Migrations\Version20250506081245` migration, where the record about migrations above is removed (to make sure the migrations will not be installed again)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/76716774b09d286f3c0a0e61a8da22c1ff17a595) to update your project

#### SocialNetworkController tweaks ([#3963](https://github.com/shopsys/shopsys/pull/3963))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/513790c9be6b55577801f69e11cc8084af10c9fa) to update your project

#### little environment cleanup ([#3966](https://github.com/shopsys/shopsys/pull/3966))

- if you have extended `build-demo` or `build-new` phing targets, make sure they depend on the new `environment-change-prod` target
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bdc8fc0559731f0e2a40381f850540d8971a8c52) to update your project

#### bump codeception versions ([#3967](https://github.com/shopsys/shopsys/pull/3967))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b99a6f1fbf7bf9ad2f344303f80880954e766e26) to update your project

#### navigation item route ([#3968](https://github.com/shopsys/shopsys/pull/3968))

- friendly URL routes are now defined in PHP file instead of yaml
    - `config/shopsys-routing/routing_friendly_url.yaml` was replaced with `config/shopsys-routing/routing_friendly_url.php`
    - the routes are now defined in the new `FriendlyUrlRouteEnum` enum, add your custom friendly URL routes there
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/33f0c777d37207afd639e830bf97b138ae2e21db) to update your project

#### UX icons are now imported within standards-fix(-diff) phing targets ([#3969](https://github.com/shopsys/shopsys/pull/3969))

- if you have extended `standards-fix` or/and `standards-fix-diff` phing targets, make sure they now depend on the new `ux-icons-lock` target
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9b9f72f8d6f0bb8201d75a97dbe2a1ecafa8b54c) to update your project

#### fix calling contactFormMainText setting query with empty text ([#3974](https://github.com/shopsys/shopsys/pull/3974))

- `Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade::getAllMainTextsIndexedByDomainId()` was removed as unused
    - no replacement suggested
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/745f1d12c7f72041cdbe62f6ee8256c6b3c44488) to update your project

#### improve order payments confirmation pages ([#3918](https://github.com/shopsys/shopsys/pull/3918))

- `Shopsys\FrontendApiBundle\Model\Resolver\Order`
    - `orderPaymentSuccessfulContentQuery()` and `orderPaymentFailedContentQuery()` methods were removed. Use general `orderPaymentPageContentQuery()` instead.
    - graphql queries `orderPaymentFailedContent` and `orderPaymentSuccessfulContent` were removed. Use general `orderPaymentPageContent` instead.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7c41ee9dc8407f8b28ad1a2f127c8d8b31be3465) to update your project

#### update order of PriceListDataFixture to ensure tests always pass ([#3979](https://github.com/shopsys/shopsys/pull/3979))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b5691372d59bc9029a0988d30f48920421e26038) to update your project

#### dev dependencies fixes ([#3976](https://github.com/shopsys/shopsys/pull/3976))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fa746a16ea3038d398592ed2b993530e38af1664) to update your project

#### prevent duplicit parameter values ([#3981](https://github.com/shopsys/shopsys/pull/3981))

- methods `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade::findParameterValueByValueTextAndLocale` and `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository::findParameterValueByValueTextAndLocale` renamed to `findParameterValueByValueTextNumericValueAndLocale` including new parameter `numericValue`
- methods `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade::getParameterValueByValueTextAndLocale` and `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository::getParameterValueByValueTextAndLocale` renamed to `getParameterValueByValueTextNumericValueAndLocale` including new parameter `numericValue`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f3fe375bb0b55bd3d4509d1acbb85606094be9c7) to update your project

#### replace deprecated Json::FORCE_ARRAY with boolean ([#3983](https://github.com/shopsys/shopsys/pull/3983))

- replace usages of second parameter of `Json::decode` method with booleans as it no longer uses integer constants for the second parameter, but boolean for forcing the array
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/701932dd026fcaf2dc0772acd2f7d59d320fadf4) to update your project

#### upgrade PhpStan to the 2.x version ([#3990](https://github.com/shopsys/shopsys/pull/3990))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e34535a964ac6d645f0c3a5165fe521aee05c10b) to update your project
- run `php phing phpstan` and fix newly reported errors in your projects

#### improve access control checks in development environment ([#3997](https://github.com/shopsys/shopsys/pull/3997))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3ff8eb47ff5a5e00c30d5d427b1b8973a8274115) to update your project

#### FlagDataFixture: fill flag translations for all locales ([#4005](https://github.com/shopsys/shopsys/pull/4005))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2a1961799df1354663535510d2fed1e82a5ed511) to update your project

#### 2FA email template is now created in DB migration instead of data fixture ([#4006](https://github.com/shopsys/shopsys/pull/4006))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/652dc3a6ecf32d88a4f9111478aa201e8c4931d3) to update your project

#### make available order payments filtered on the backend instead of storefront ([#3922](https://github.com/shopsys/shopsys/pull/3922))

- GraphQL field `Order#paymentTransactionsCount` was removed, as it is no longer necessary — payments are now filtered on the backend, so only applicable ones are returned.
- GraphQL field `Settings#maxAllowedPaymentTransactions` was removed, as it is no longer necessary — payments are now filtered on the backend, so only applicable ones are returned.
- GraphQL field `OrderPaymentsConfig#currentPayment` now return null if the original payment is not available anymore
- `OrderPaymentsConfigFactory::createForOrder()` now directly filters the available payments – check your code if you have extended it
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ee248f68890add79fb182ace935ee0835dae30ac) to update your project

#### fix Gitlab check for locked icons ([#4004](https://github.com/shopsys/shopsys/pull/4004))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b3073dca429901b6506f1a7f223353e6c19d8989) to update your project

#### DatabaseSearchingHelper::getFullTextLikeSearchString argument is not nullable anymore ([#4008](https://github.com/shopsys/shopsys/pull/4008))

- `Shopsys\FrameworkBundle\Model\Category\CategoryFacade::getVisibleByDomainAndSearchText()` was removed, you can use `Shopsys\FrontendApiBundle\Model\Category\CategoryFacade::getVisibleCategoriesBySearchText()` instead
- `Shopsys\FrameworkBundle\Model\Category\CategoryFacade::getSearchAutocompleteCategories()` was removed without replacement
- `Shopsys\FrameworkBundle\Model\Category\CategoryRepository::getPaginationResultForSearchVisible()` was removed without replacement
- `Shopsys\FrameworkBundle\Model\Category\CategoryRepository::getVisibleByDomainIdAndSearchText()` was removed, you can use `Shopsys\FrontendApiBundle\Model\Category\CategoryRepository::getVisibleCategoriesBySearchText()` instead
- `Shopsys\FrameworkBundle\Model\Category\CategoryRepository::getVisibleByDomainIdAndSearchTextQueryBuilder()` was removed, you can use `Shopsys\FrontendApiBundle\Model\Category\CategoryRepository::getVisibleCategoriesBySearchTextQueryBuilder()` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/77f68d88255570d1d84eb5d284ffee758b5b4925) to update your project

#### update GrapesJS to ensure that GrapesJS content is always editable ([#3998](https://github.com/shopsys/shopsys/pull/3998))

- if you are using GrapesJS for other entities than article, blog article or mail template, use `Shopsys\FrameworkBundle\Component\GrapesJs\EnsureCorrectGrapesJsFormatHelper::ensureStringIsInCorrectGrapesJsFormat` on GrapesJS variable
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6caceb9140085bc3666833b0f3d13a83a886fb51) to update your project

#### robots.txt: tweak the Crawl-delay value for new projects ([#4015](https://github.com/shopsys/shopsys/pull/4015))

- the setting value was modified in an existing migration. If you are satisfied with your current settings on the staging servers, you do not need to do anything
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9256451e60431d50fb3bfdb5753c61e26d531887) to update your project

#### improve administration rights in inline edit and grid actions ([#4022](https://github.com/shopsys/shopsys/pull/4022)) and ([#4072](https://github.com/shopsys/shopsys/pull/4072))

- revisit all your `*InlineEdit` classes and ensure that all your overridden methods are using correct security methods as in parent class
- provide third argument `roleConstant` in `GridFactory::create()` method to ensure, that delete action is not displayed for users without `roleConstant` permission

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d185e8a894e314bd6d89ac60cf9be3d6ed566320) to update your project

#### resolve TODO in your code ([#4023](https://github.com/shopsys/shopsys/pull/4023))

- comments with `TODO` in your code are now reported during the `standards` checks
    - either deal with them or skip the `PHP_CodeSniffer\Standards\Generic\Sniffs\Commenting\TodoSniff` in your ecs config
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ed011badc6a6cb7f5bd864260604d00ae04158b9) to update your project

#### updated accessibility follow-up ([#4016](https://github.com/shopsys/shopsys/pull/4016))

- we have added `tabindex` to all the links within the texts that are created in data fixtures and DB migrations, e.g., in order status mail templates, order submitted text setting, etc.
    - no additional DB migrations were created to update the current texts so if you want to improve accessibility of your project, add `tabindex` to the links in your custom texts manually
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/501a970ea9288a5f549ce138c713a8a4f3d70349) to update your project

#### add the ability to enable / disable PromoCodes ([#4037](https://github.com/shopsys/shopsys/pull/4037))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cde28a01d4558799ab95b97c39d2bbe3509b90bc) to update your project

#### improve demo data of parameters ([#4041](https://github.com/shopsys/shopsys/pull/4041))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/26d5cc30625b592d7fc4ca1bcdb22a339844a0cc) to update your project

#### fix visual appearance of few email templates ([#4042](https://github.com/shopsys/shopsys/pull/4042))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0471418e2d80f54d018291db73eda26ef5935460) to update your project

#### partially removed the jQuery UI dependency and replaced it with modern alternatives ([#4047](https://github.com/shopsys/shopsys/pull/4047))

- most jQuery UI components have been replaced with modern, lightweight alternatives:
- `jquery-ui/datepicker` → `flatpickr`
    - datepicker is automatically initialized on the appropriate form type fields (DatePickerType)
    - if you need to initialize flatpickr manually in your custom code, refer to the [complete implementation in `datePicker.js`](https://github.com/shopsys/shopsys/blob/17.0/packages/framework/assets/js/common/components/datePicker.js)
- `jquery-ui/slider` → `nouislider`
    - replace initialization of jQuery UI slider with noUiSlider in your code, for example:

```diff
-   $element.slider({
-       min: 0,
-       max: 100,
-       value: initialValue,
-       slide: (event, ui) => {
-           // handle value change
-       }
-   });
+   noUiSlider.create(element, {
+       start: [initialValue],
+       range: { 'min': 0, 'max': 100 },
+   });
+
+   element.noUiSlider.on('update', (values) => {
+       // handle value change
+   });
```

- `jquery-ui/sortable` → `sortablejs` (except for category tree sorting)
    - replace initialization of jQuery UI sortable with SortableJS in your code, for example (check the [SortableJS documentation](https://sortablejs.github.io/Sortable/) for more options):

```diff
-   $container.filterAllNodes('.js-sortable-values-items').sortable({
-       items: '.js-sortable-values-item',
-       handle: '.js-sortable-values-item-handle'
+   $container.filterAllNodes('.js-sortable-values-items').each((index, element) => {
+       Sortable.create(element, {
+           handle: '.js-sortable-values-item-handle',
+           draggable: '.js-sortable-values-item',
+           animation: 150
+       });
    });
```

- jQuery UI is still used for category tree sorting (`CategoryTreeSorting.js`) which uses `nestedSortable` library
- if you have customized any of the following framework components, you need to update your code:
    - `datePicker.js` - now uses Flatpickr API instead of jQuery UI datepicker
    - `NumberSlider.js` - now uses noUiSlider API instead of jQuery UI slider
    - `SortableValues.js`, `GridDragAndDrop.js`, `GridMultipleDragAndDrop.js` - now use SortableJS
    - Grid sorting functionality in `Grid.html.twig`
- if you use jQuery UI datepicker, slider, or sortable in your custom code, you should migrate to the new libraries following the patterns in the updated framework components
- ESLint has been updated to version 8.57.1 with `eslint-config-standard` 17.1.0
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bdece1dbdbc795923200b60dbbaafee3ef7db051) to update your project

#### GrapesJS wrap all content after save ([#4050](https://github.com/shopsys/shopsys/pull/4050))

- fixed an issue where GrapesJS was wrapping all content in additional HTML elements after saving
- this change ensures that the content structure remains intact and prevents unwanted wrapper elements from being added to the saved content
- added custom `mail-icon` class for mail template block icons to improve styling consistency and visual identification

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/28b4d58bca39d93c418aa01b81bb7c5a3385d900) to update your project

#### replace eslint with biome for javascript linting ([#4056](https://github.com/shopsys/shopsys/pull/4056))

- ESLint configuration was replaced with Biome for JavaScript linting and formatting
- phing targets have been renamed:
    - `eslint-check` → `js-standards-check`
    - `eslint-fix` → `js-standards-fix`
- npm scripts have been updated:
    - `npm run eslint` → `npm run standards-check`
    - `npm run eslint-fix` → `npm run standards-fix`
- if you have custom ESLint configurations or rules, migrate them to Biome configuration in `biome.json`
- run `js-standards-fix` phing targets to fix formatting of your JavaScript files, fix any remaining issues manually
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d15b64c435f4c3a1176bcad842e89cb35cc0f37e) to update your project

#### added ability to easily add new item to OrderData that will be persisted after OrderFacade::edit ([#4057](https://github.com/shopsys/shopsys/pull/4057))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4b23d79b8ff96f30b62629c7e2078c065662a773) to update your project

#### Add `max-height` to product images in email templates ([#4062](https://github.com/shopsys/shopsys/pull/4062))

- check the email templates you have customized and ensure that product images have appropriate `max-height` styles applied to prevent layout issues

#### Implement Context system ([#4064](https://github.com/shopsys/shopsys/pull/4064))

- `Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade` was deleted. Instead of `isInAdmin` method use `isCurrentContext()` method with `Shopsys\FrameworkBundle\Component\Context\AdminContext::class` parameter from `Shopsys\FrameworkBundle\Component\ContextContextResolverInterface` service.
- You can find more information in the [Context system documentation](https://docs.shopsys.com/en/17.0/introduction/context-system/)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1074dfd9559a7f79c7d9b1e0a9a016ab1c597e3b) to update your project

#### OrderItem::hasProduct replaced with OrderItem::isTypeProductAndHasProduct ([#4054](https://github.com/shopsys/shopsys/pull/4054))

- `OrderItem::hasProduct` method has been renamed to `OrderItem::isTypeProductAndHasProduct` and now always returns bool instead of throwing an exception when the order item is type of `product` and has no product assigned.

#### Unified Biome configuration ([#4120](https://github.com/shopsys/shopsys/pull/4120))

- run `npm install` to install the new `@shopsys/biome-config` package
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/07dd4ebf4551ae46744203e874f1806b814fce55) to update your project

#### refactor administrator login as user ([#4124](https://github.com/shopsys/shopsys/pull/4124))

- administration single sign-on is no longer supported
- `Shopsys\FrameworkBundle\Controller\Admin\LoginController` class was changed:
    - `MULTIDOMAIN_LOGIN_TOKEN_PARAMETER_NAME` constant was removed
    - `ORIGINAL_DOMAIN_ID_PARAMETER_NAME` constant was removed
    - `ORIGINAL_REFERER_PARAMETER_NAME` constant was removed
    - `ssoAction()` method was removed
    - `authorizationAction()` method was removed
- `Shopsys\FrameworkBundle\Model\Administrator\Administrator` class was changed:
    - `$multidomainLogin` property was removed
    - `$multidomainLoginToken` property was removed
    - `$multidomainLoginTokenExpiration` property was removed
    - `setMultidomainLoginTokenWithExpiration()` method was removed
    - `isMultidomainLogin()` method was removed
    - `setMultidomainLogin()` method was removed
- `Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository` class was changed:
    - `getByValidMultidomainLoginToken()` method was removed
- `Shopsys\FrameworkBundle\Model\Security\AdministratorLoginFacade` class was changed:
    - `MULTIDOMAIN_LOGIN_TOKEN_LENGTH` constant was removed
    - `MULTIDOMAIN_LOGIN_TOKEN_VALID_SECONDS` constant was removed
    - `generateMultidomainLoginTokenWithExpiration()` method was removed
    - `loginByMultidomainToken()` method was removed
- `Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface` interface was changed:
    - `isMultidomainLogin()` method was removed
    - `setMultidomainLogin()` method was removed
- `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser\LoginAdministratorAsUserUrlProvider::getSsoLoginAsCustomerUserUrl()` method was renamed to `getLoginAsCustomerUserUrl()`
- `Shopsys\FrontendApiBundle\Model\Security\LoginAdministratorAsUserUrlProvider::getSsoLoginAsCustomerUserUrl()` method was renamed to `getLoginAsCustomerUserUrl()`
- `Shopsys\FrameworkBundle\Twig\CustomerExtensionTwig::getSsoLoginAsUserUrl()` method was renamed to `getLoginAsUserUrl()`
- `loginAsCustomerUser.html.twig` twig template was moved from `@ShopsysFrontendApi/Admin/Content/Login` to `@ShopsysFrontendApi/SocialLogin` folder
- `Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade::loginAdministratorAsCustomerUserAndGetAccessAndRefreshToken()` now requires `string $exchangeToken` parameter instead of `int $customerUserId` parameter
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8f42b6ba9318d1cac5165aafc0b4b69d426e5646) to update your project

#### AdminContext now includes elfinder routes ([#4134](https://github.com/shopsys/shopsys/pull/4134))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/962c08579397707386fc7355afb600d3d332c47b) to update your project

#### Added support for IDE URLs to the profiler and blue screen error page ([#4138](https://github.com/shopsys/shopsys/pull/4138))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b1805b75b473c585b341b3651adfcdfe9889c04a) to update your project

#### Update your makefile to be self documented([#4032](https://github.com/shopsys/shopsys/pull/4032))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d9c336deca3363c93d2482a472517732efe81d20) to update your project

#### add checking of correct licenses to your Makefile and CI ([#4141](https://github.com/shopsys/shopsys/pull/4141))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c0639a0843aa533800a0dacde2f41858fb7d101c) to update your project

#### upgrade Biome to version 2.2.0 ([#4143](https://github.com/shopsys/shopsys/pull/4143))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a1573c3c09c34aa73e0fe89cc5bb0d50aac0ec17) to update your project

#### Update GoPay to correctly change payment method in order if user switched its payment method in GoPay ([#4163](https://github.com/shopsys/shopsys/pull/4163))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f8abbe1a59b63926450ccdae8207a56f6d4fe265) to update your project

#### codeception acceptance tests now use pg_restore to dump DB from an SQL file ([#4150](https://github.com/shopsys/shopsys/pull/4150))

- `Shopsys\FrameworkBundle\Component\Doctrine\DatabaseConnectionCredentialsProvider::getConnectionDsn()` method was removed without replacement
    - if needed, compose the DSN manually using the provided credentials
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b66fbb3faa97f0e0388d30db81a1dbac094288e4) to update your project

#### fix recalculation of deleted regular product ([#4151](https://github.com/shopsys/shopsys/pull/4151))

- method `\Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationRepository::getIdsToRecalculateByMainVariantIds()` was renamed to `getRelevantIdsToRecalculate()`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/78a5fd5ecf5c84a832f82be94e0784e7ae9ee089) to update your project

#### fix hreflang links for SEO pages ([#4157](https://github.com/shopsys/shopsys/pull/4157))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6dd9a2ef363332e1a9b0a151785f5cc18e9ffd38) to update your project

#### fixed order of registering extended routes ([#4164](https://github.com/shopsys/shopsys/pull/4164))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7f0a2f30862da5a9ebb7b9d94e569738a80cf2ff) to update your project

#### fix makefile tasks ([#4183](https://github.com/shopsys/shopsys/pull/4183))

- see [project-base-diff](https://www.github.com/shopsys/project-base/commit/7e19bc679917f903c903131837ceeb860e232feb) to update your project

#### upgrade rabbitMQ version ([#4167](https://github.com/shopsys/shopsys/pull/4167))

- see [project-base-diff](https://www.github.com/shopsys/project-base/commit/a2449e6ec1d9214dac00af990bfb187bebf1bc82) to update your project

<!-- backendNotes -->

### Storefront

#### Implement store features ([#3413](https://github.com/shopsys/shopsys/pull/3413))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/133922d6802732a8e87c73c27b27b60ae0efc83c) to update your project

#### Prepare storefront for external payment gates ([#3499](https://github.com/shopsys/shopsys/pull/3499))

- extend `promoCode` to `promoCodes` array to handle multiple promo codes
- use `hasExternalPayment` to differenciate external payment gates and handle them correctly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/80266352345254e692e03a0576a55dd4cbaed161) to update your project

#### no log sentry error on maintenance page ([#3675](https://github.com/shopsys/shopsys/pull/3675))

- in urql fetcher checking if response is not json then return empty object to avoid sentry error
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c729198ffc54e38125adcca317965f6adce50fe) to update your project

#### console error after failed payment redirect ([#3685](https://github.com/shopsys/shopsys/pull/3685))

- removed double slash in redirect url after failed payment
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6d198ce1b500c4bd1bf68b182815bf942eb452fe) to update your project

#### category automated filters ([#3672](https://github.com/shopsys/shopsys/pull/3672))

- FE API: `category` now returns `categoryAutomatedFilters` array
    - when the category contains `TypeCategoryAutomatedFilterEnum.OnStock` within the automated filters, the `FilterGroupInStock` is not displayed in the category product list.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6771b44361b11f19f6929d82d6238c1b7ff52fc8) to update your project

#### add special price ([#3628](https://github.com/shopsys/shopsys/pull/3628))

- added special price to product detail page, category page and products slider, autocomplete search, compare page and wishlist page
- added special price countdown to product detail page
- added conditons for special price levels to show correct price
- refactored product price component and product list item component
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d49d1ae31ce4da99684026d206ae24854d08e8ad) to update your project

#### banner without description ([#3707](https://github.com/shopsys/shopsys/pull/3707))

- Removed banner text with background when there is no description
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2df2e7a0fef42a391b61d6106a8aec34d0198894) to update your project

#### Simplify cypress snapshot names ([#3709](https://github.com/shopsys/shopsys/pull/3709))

- introduced `SNAPSHOT_GROUP` enum, `SUBGROUP_INDEX` for each cypress e2e subgroup and snapshot indexes
- each snapshot has it's own id based on the group, subgroup and it's position index (for example `2.2.0` for cartPage test increasing and decreasing quantity)
- this id can be searched in the `snapshots-info-table.md` where more information about the snapshots are given
- to update the table run `generateSnapshotsInfoTable.js` script to automatically parse all tests and compile a new table
- there are some limitations for importing/requiring dependencies for script outside of the module, therefore we need to reconstruct them from files manually (for example `SNAPSHOT_GROUP` enum)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f8c6ccf2fe81dbb42ba43e06eb24d20330791283) to update your project

#### cypress tests: add blackout for copyright in footer ([#3708](https://github.com/shopsys/shopsys/pull/3708))

- `FooterCopyright.tsx`: wrap the current year in a new span with tid `TIDs.footer_copyright` and rewrite the usage of `t` function into the `<Trans>` component
- add the blackout for `TIDs.footer_copyright` in all your cypress screenshots that include the footer
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0f9b8f59016b632b6c162558241443fe7e019c7d) to update your project

#### redesign product detail parameters and tabs ([#3714](https://github.com/shopsys/shopsys/pull/3714))

- redesigned product detail parameters and tabs
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b5d6fc420ee18bb21544bc8413128d908dca222c) to update your project

#### add user email to complaint form and complaint detail ([#3716](https://github.com/shopsys/shopsys/pull/3716))

- add user email to complaint form and complaint detail
- refactor complaint list
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c804ce74b779f1c09d9a794dfc6a8e1cc01a3903) to update your project

#### wrapping required checkbox indicator ([#3728](https://github.com/shopsys/shopsys/pull/3728))

- update wrapping of required checkbox indicator in responsive link component
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b0329f606b9b4fd64dc802ccb50fad1e697ac5a8) to update your project

#### add register form to payment confirmation page ([#3670](https://github.com/shopsys/shopsys/pull/3670))

- `RegistrationAfterOrder` component is now shown also on `order-payment-confirmation` page if payment was successful
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b1618ea7a828ca8bbcc294d3296f6c3652eb7ef3) to update your project

#### incorrect labels in new complaint and search complaint input ([#3739](https://github.com/shopsys/shopsys/pull/3739))

- new complaint and search complaint input has correct labels now
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b114ed765a17753c56efc4a47a191528ac6ca898) to update your project

#### missing skeleton loader for stores breadcrumbs ([#3740](https://github.com/shopsys/shopsys/pull/3740))

- stores breadcrumbs have the correct skeleton loader now
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1ba1e6272c1c4deff508c4a29b605d536bd29055) to update your project

#### remove pagination title from first page ([#3741](https://github.com/shopsys/shopsys/pull/3741))

- removed pagination title from first page
- changed default number of article blog based on figma design
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bdc1b5610f91b57118bb172c304d3e1753e9570a) to update your project

#### Extend copyright year blackout to whole Webline width #3743 ([#3743](https://github.com/shopsys/shopsys/pull/3743))

- to ensure cypress screenshots will not all start failing on next copyright year iteration, blackout was extended to whole Webline width to fix the problem with changing width of blackout dimensions for different years.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/43d060433d8fa632fb066de3c1e1ee49b9960f1b) to update your project

#### set footer stick to bottom ([#3744](https://github.com/shopsys/shopsys/pull/3744))

- when content had smaller height than screen, the footer would not stick to bottom and this looked bad for big screens
- each page has set `min-h-screen` and `h-full` and the footer sticks to the bottom
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f72962cce1a3b84a5f763bb8f3580c588cb778ef) to update your project

#### new B2B customer user roles for cart and order access and manipulation ([#3756](https://github.com/shopsys/shopsys/pull/3756))

- there are new customer roles for B2B domain - `ROLE_API_CART_AND_ORDER_CREATION` and `ROLE_API_COMPANY_ORDERS_VIEW`
    - the roles are used to restrict access to the appropriate pages and actions in the B2B domain using `authenticationConfig` in `initServerSideProps`
    - check your code whether the restrictions are relevant for your project and cover all your corresponding custom pages
- FE API: `CurrentCustomerUserDecorator.roles` field is now an array of new `CustomerUserRoleEnum`
    - hardcoded `CustomerUserRoleEnum` was removed, use the generated `TypeCustomerUserRoleEnum` instead
- created `AuthorizationProvider` with `useAuthorization` hook to replace `useUserPermissions`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2306f2fa0435e6ae8f7342fd11bbdc832ffeaac9) to update your project

#### enhanded cart navigation stepper ([#3767](https://github.com/shopsys/shopsys/pull/3767))

- redesigned cart navigation stepper
- added new logic for switching steps
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/087f8b084aaddcfe4ebd45a8b6bae6e20892f80a) to update your project

#### Add resolution select and bank account input to complaint ([#3759](https://github.com/shopsys/shopsys/pull/3759))

- updated complaint with resolution and situational bank account number input, that is required only when `money_return` resolution is selected
- since resolution might be modified/deleted, there is a helper function `isResolutionMoneyReturn` in complaint utils that could be replaced with updated string for `money_return` option, so you can swap values flawlessly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4259cf1ef76d24fb9e449a73dc79549a536fc0ab) to update your project

#### new B2B customer user roles for complaints ([#3768](https://github.com/shopsys/shopsys/pull/3768)

- there are new customer roles for B2B domain - `ROLE_API_COMPLAINT_CREATION` and `ROLE_API_COMPANY_COMPLAINTS_VIEW`
    - the roles are used to restrict access to the appropriate pages and actions in the B2B domain using `authenticationConfig` in `initServerSideProps`
    - check your code whether the restrictions are relevant for your project and cover all your corresponding custom pages
- the customer users structure was changed on FE API so it is possible to distinguish among logged/unlogged and company/regular customers
    - check your queries and mutations as they might need to be updated to reflect the new structure
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e1f8ea4fd7b90ed59a6699773b9fc54ec5926f99) to update your project

#### add grapesjs three columns component ([#3769](https://github.com/shopsys/shopsys/pull/3769))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/512a21e6b65d247cb427973b2c376898b4249ac4) to update your project

#### Order confirmation and order payment status page redesign ([#3774](https://github.com/shopsys/shopsys/pull/3774))

- redesigned order confirmation and order payment status pages
- redesigned order customer info block
- added order confirmation stepper
- created new query for order data by orderUuid or orderUrlHash
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ec619a2c86ac868bdf641843dc75ba764ac6b339) to update your project

#### Add margin to info message on comparison page ([#3775](https://github.com/shopsys/shopsys/pull/3775))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d428f53baf3795b96d1017f474f8e386dd86d8e1) to update your project

#### rename contact page to contact-form page([#3776](https://github.com/shopsys/shopsys/pull/3776))

- renamed the contact page to a contact-form page to avoid conflict when the admin creates a contact page from the admin
- if you have any links pointing to this page (e.g., links in articles/menu), consider updating them in your project or evaluate if this change is needed from an SEO perspective
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/07cf474599f16c7b858eef64a2cb8803b04136d6) to update your project

#### cart page redesign ([#3780](https://github.com/shopsys/shopsys/pull/3780))

- the cart page has been redesigned to be more user-friendly
- updated spinbox component to be more user-friendly and aligned with the design system
- polished free delivery range slider
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3934ef094bc8a68c6be6beaa2c175ff1961cc70a) to update your project

#### manual complaint creation (without order) ([#3784](https://github.com/shopsys/shopsys/pull/3784))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b9402a9b4507fa7adb0b41c4e188f3f0cf7efcd2) to update your project

#### fix param value with slash ([#3786](https://github.com/shopsys/shopsys/pull/3786))

- param value with slash is parsed correctly now
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9c54f4636d7cbf2bf609c6783931cd4f8f16f0b3) to update your project

#### add missing translations ([#3787](https://github.com/shopsys/shopsys/pull/3787))

- added missing translations for collapsible component
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/23db24b033e4e0b93258410029ac4101f2861b11) to update your project

#### Set narrow layout for common articles. ([#3791](https://github.com/shopsys/shopsys/pull/3791))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/066444e6330b92a6917bc0278b057c9c367e2319) to update your project

#### add missing scroll to product availability popup ([#3793](https://github.com/shopsys/shopsys/pull/3793))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/dcee566ccf1eb15f19440223f80d887a5d5c6453) to update your project

#### fix mobile banners slider ([#3794](https://github.com/shopsys/shopsys/pull/3794))

- swipe handlers are now on the div instead of the ExtendedNextLink
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/25230210a77ef8c93a8274aabd5d48432c2ba7e1) to update your project

#### missing smooth scrolling to the error field ([#3795](https://github.com/shopsys/shopsys/pull/3795))

- smooth scrolling was added to the error field in the complaint form popup
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ff415bd6c40af735341183f145ecf6b26b3beb62) to update your project

#### fix complaint detail customer info ([#3799](https://github.com/shopsys/shopsys/pull/3799))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b62d6940629bf00639db06e6c7fcfd0bdaa8f987) to update your project

#### ROLE_API_CUSTOMER_SELF_MANAGE tweaks ([#3788](https://github.com/shopsys/shopsys/pull/3788))

- `useAuthorization()` now provides new properties `canSeePrices`, `canManageCompanyData`, and `canManagePersonalData` to determine the corresponding permissions
    - `canManageCompanyData` replaced the former `canManageProfile` property
    - `canSeePrices` replaced the former `CurrentCustomer.arePricesHidden` type property provided by `useCurrentCustomerData()` hook (beware the reverted value)
- introduced new `useUserProfileSectionLabel()` hook to provide a label for the user profile section
    - the label depends on the customer user permissions
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f8d9bc067277665b4139194451dc9859158425c7) to update your project

#### Fix autocomplete gtm & add list view gtm event ([#3790](https://github.com/shopsys/shopsys/pull/3790))

- autocomplete search result count is now calculated properly
- all `ProductsSlider` components now send `product_list_view` gtm event, including LB recommended products
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4dd466a4de1ddd20067aefe4281d68519a5cfb43) to update your project

#### Add gtm payment event & store it in localStorage ([#3814](https://github.com/shopsys/shopsys/pull/3814))

- `payment` event is fired after each payment attempt
- info about the order payment (namely `orderNumber` and `paymentRetryCount`) is stored to `localStorage` to preserve it for multiple future steps
- `paymentRetryCount` needs to be manually incremented after each payment try and saved again to `localStorage`
- be aware that `paymentTransactionsCount` is not valid for this use-case as it only carries info about the retry count for the specific payment type, whereas we need the retry count of payments for the **whole** order (which is not implemented on the BE)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0b33b6c99d7a61ba51d65bc067ce9cd9b35977df) to update your project

#### Add smoke test for Storefront ([#3816](https://github.com/shopsys/shopsys/pull/3816))

-
- for running smoke test locally
    - update volumes in `docker-compose.yml` in root directory with `./project-base/storefront/config:/app/config` and `./project-base/storefront/pages:/app/pages`
    - in terminal run `make run-smoke-tests`
- during the test, the demo data are created and the test performs the following workflow:
    - the Cypress container has mounted volumes for `config` and `pages` folders to access these files
    - a script scans all files in the `pages` folder
    - file extensions like `.tsx` are stripped to create route paths
    - these paths are used for testing route accessibility
    - for static routes, a special configuration file `routes.ts` contains route translations
    - each route is visited and checked for proper rendering and absence of error pages
    - for most pages, the test also verifies that the correct headline text is displayed (e.g. "About us" on /about-us page)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f03759e356d9103f1b33a5fdcd29a106af02a6b0) to update your project

#### Upgrade Tailwindd CSS to v4 ([#3820](https://github.com/shopsys/shopsys/pull/3820))

- follow the guide for manual upgrade `https://tailwindcss.com/docs/upgrade-guide` (automatic does not work)
- update `tailwindcss@latest` (v4.0.7), `tailwind-merge@latest` (v3.0.2) and `postcss@latest` (v8.5.3)
- install new package `@tailwindcss/postcss` (v4.0.8)
- uninstall `autoprefixer`
- update `postcss.config.js`
- remove `tailwind.config.js` file as configuration is now handled directly in `theme.css` using `@theme` directives
- theme variables are now defined using CSS custom properties with the `--` prefix (see https://tailwindcss.com/docs/theme#theme-variable-namespaces)
- fonts using `@theme inline` directives now
- `z-index` is handled with `@utility` directives
- be aware that in Tailwind v4, `button` elements no longer have `cursor: pointer;` set by default - you may need to add the `cursor-pointer` class to your buttons
- remove decpecated utilities (see https://tailwindcss.com/docs/upgrade-guide#removed-deprecated-utilities)
- rename utilities (see https://tailwindcss.com/docs/upgrade-guide#renamed-utilities)
- updated styleguide function for getting new css variables
- updated twMerge
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9a8f0de18c0b3c876c7999030588b277f22d7706) to update your project

#### Fix responsive filter design ([#3821](https://github.com/shopsys/shopsys/pull/3821))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d9ef5b564b729a68f6f58b9fc9de922c9288be30) to update your project

#### Investigation Sentry errors ([#3825](https://github.com/shopsys/shopsys/pull/3825))

- added `no-log` exception several errors as it is expected behavior and was causing the majority of errors in Sentry
- expected user validation errors are no longer being logged to Sentry
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/627ae48d10c469a82100e6ed19fb60d0576c36e3) to update your project

#### Add notification bars revalidation ([#3805](https://github.com/shopsys/shopsys/pull/3805))

- `validityFrom` and `validityTo` was added to `NotificationBarsFragment`
- storefront can take advantage of these changes as follows:
    - we can calculate earliest time at which we should revalidate the state (hide expired bars)
    - using `useCountdown` we can refetch during these transformations to get fresh bars data
- extended `useCountdown` props to support custom `interval` and `callback`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/865ff3e2ee89bfe617459c2e6578d7e13fa6345e) to update your project

#### Fix search input popup on search page ([#3828](https://github.com/shopsys/shopsys/pull/3828))

- search input is cleared after submit to be able trigger search popup again
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/519f8080715e77974d84885dcf162fef782a2f6a) to update your project

#### Fix product slider responsiveness ([#3829](https://github.com/shopsys/shopsys/pull/3829))

- product slider is now responsive across all device sizes with improved styling
- added `minimumVisibleItemsOnSmallDesktop` constant to ensure proper display on smaller desktop screens
- updated slider column width classes for better responsiveness on various breakpoints
- improved scroll behavior for better user experience
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/38b03eff026b4d0c7c98078317efb18963ece9ee) to update your project

#### Refactored web layout ([#3832](https://github.com/shopsys/shopsys/pull/3832))

- implemented a new approach to unified spacing between page elements:
    - Created a new `VerticalStack` component that accepts a `gap` prop to control spacing between child elements
    - Refined the `Webline` component to focus solely on setting the maximum width of the container
    - This separation follows the Single Responsibility Principle for better maintainability
- enhanced SEO by properly implementing semantic HTML tags for Header, Footer, Nav, and Section elements, resulting in more structured code
- refactored the `ProductDetailContent` and `ProductDetailMainVariantContent` components for improved clarity and readability
- created a new `ArticleDate` component to standardize the display of dates across all article-related components
- removed adverts from cart
- removed unused components to reduce codebase size
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4aed3cff6b50d4c56f6b6f2caf75366e0e611218) to update your project

#### Sentry errors ([#3834](https://github.com/shopsys/shopsys/pull/3834))

- added condition for skipping expected user errors in development
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3a128b11eb0f358092fe12728a70061acd11ad0c) to update your project

#### Update dependencies ([#3835](https://github.com/shopsys/shopsys/pull/3835))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c249b7c10375e300ef1cb9cc13edd69563bfeb94) to update your project

#### Fix product detail skeleton loader ([#3844](https://github.com/shopsys/shopsys/pull/3844))

- text selection logic needs to be in the `ExtendedNextLink` to keep skeletons working properly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/294e79e7f38d7c6062158383343593a33c3f117c) to update your project

#### Price filters for limited users ([#3850](https://github.com/shopsys/shopsys/pull/3850))

- when a user tries to reach the `brand`, `flag`, `category` or `search page` without the needed rights with applied price filters and sorting, it will remove filter and sort query and continue to original page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/145b1b091a0cab3aa561a24f37fcf5c3803f1d09) to update your project

#### Migrate eslint from deprecated v8 to v9 ([#3851](https://github.com/shopsys/shopsys/pull/3851))

- run config file migration script `pnpm dlx  @eslint/migrate-config .eslintrc.json`. Note that it does not fully support `.eslintrc.js` so check carefully new generated config file (for example add `__dirname` to `tsconfigRootDir`, etc.)
- upgrade elsint to latest version `mces pnpm i eslint@latest --save-dev` (at the time of writing v9.22)
- fix all issues with peer dependencies (eslint plugins)
- replace all deprecated references of `.eslintrc.js` with new `eslint.config.mjs` (`.prettierignore`, `tsconfig.json`)
- for more information follow [the official migration guide](https://eslint.org/docs/latest/use/configure/migration-guide), mainly new configuration file structure ([useful migration script](https://eslint.org/docs/latest/use/configure/configuration-files), [general v9 migration reference](https://eslint.org/docs/latest/use/migrate-to-9.0.0))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f6697c05b53c3967bd086a6e58c93de06eaa4ade) to update your project

#### fix multiple rerenders of product comparison ([#3852](https://github.com/shopsys/shopsys/pull/3852))

- decouple sticky head from child components = make a sticky head wrapper `ProductComparisonHeadStickyWrapper`
- decouple constant (on-scroll) synchronization of Y position with the react state - change the state only when Y reached a desired Y-offset (in `useScrollTop.ts`)
- make only the newly created `ProductComparisonHeadStickyWrapper` dependant on that Y-offset state
- other tiny fixes in the logic
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e1f96e11552cb322bd3871ed9b485c607d3b4810) to update your project

#### update your application to reflect input price settings correctly ([#3836](https://github.com/shopsys/shopsys/pull/3836))

- Frontend API type `Cart::remainingAmountWithVatForFreeTransport` has been renamed to `remainingAmountForFreeTransport`
    - the amount now reflects application setting for the input price type, so if the input price type is set to price without VAT, this amount will be without VAT
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d7ec161e9233cd2b9231ef8cf940b42610d29dc6) to update your project

#### Add basic style to product detail description ([#3864](https://github.com/shopsys/shopsys/pull/3864))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8384c3038003d54fc33de9e46f6eb49207f9406e) to update your project

#### improve CLS ([#3830](https://github.com/shopsys/shopsys/pull/3830))

- fix layout shift of main image in category detail and product detail by setting a static height
- optimize image flicker by memoizing `Image`, `onError` and `loader` functions
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1b9054268779fffd1c4bd655c9e68979d2f1770f) to update your project

#### Free transport promo code fix & corrected usage of order transport and payment ([#3878](https://github.com/shopsys/shopsys/pull/3878))

- changed condition on which we show applied promo codes
    - the value of discount for `free_transport_type` is `0`, thus wasn't shown in `CartPreview` and user wasn't able to remove it
- fix order transport and payment in `order-confirmation`, `order-payment-confirmation` and `personal-data-overview`
    - instead of using `transport` and `payment` from `order`, use `items` to get transport and payment
    - this ensures that we show correct transport and payment data (at the time of order creation)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a9b3419da4e439eb064a91e46beef6eb5350a579) to update your project

#### Fix long product name word break ([#3880](https://github.com/shopsys/shopsys/pull/3880))

- adjusted styles to fix UI for product names with long words (more than 25 chars)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/53f7d6c9f096458128da94492d1ba206276b1692) to update your project

#### Fix changed user information in cart ([#3892](https://github.com/shopsys/shopsys/pull/3892))

- changed user informations in cart will remain changed now
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/71f1f2e1c8b665f4808adfd233e4b118a701d583) to update your project

#### Fix notifivation bar unlimited validTo ([#3894](https://github.com/shopsys/shopsys/pull/3894))

- notification bar is now showing correctly even there is unlimited validTo date
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/30b74d5cf18ecfa8defb69e8d828a53e29f44fb6) to update your project

#### redesign order list ([#3897](https://github.com/shopsys/shopsys/pull/3897))

- redesigned order list and order detail based on figma design
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ea88027553143cd0c6e2028f2ac53d64434762a6) to update your project

#### Store detail button ([#3904](https://github.com/shopsys/shopsys/pull/3904))

- store detail button is now more visible due to set to primary
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b0391c65c95e0a86ccb0aadb1393d8ecc86a0583) to update your project

#### Optimize image resizer with caching ([#3907](https://github.com/shopsys/shopsys/pull/3907))

- Discuss these changes with some SEO expert as they may affect SEO for images. If you don't want to change image behavior for robots follow these steps:
    - Do not apply changes in `robots.txt.tsx`
    - In `Image.tsx` remove `overrideSrc` attrbite from `NextImage` component
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/75e16258170967a72b34303c55b8149b0ef69891) to update your project

#### Fix Load more and Show more pluralization ([#3884](https://github.com/shopsys/shopsys/pull/3884))

- `Load more` and `Show more` translations now contain pluralization, `{{ count }}` & `{{ items }}` variables so that each word could be placed in custom order (better customizability for different languages)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8aa2353051a0625a8612197db02987e166800207) to update your project

#### Update Tailwind to latest version ([#3926](https://github.com/shopsys/shopsys/pull/3926))

- updated Tailwind to latest version 4.1.3
- updated Tailwindcss/postcss to latest version 4.1.3
- there are new classes which can fix some issues with word wrapping
    - new class `wrap-anywhere` which can replace class `wrap-break-word`
    - fixed this issue in product detail name and banner
    - more about this update https://tailwindcss.com/blog/tailwindcss-v4-1
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4cfb20b1673bd2109849afc04c3e33a91ce16ab6) to update your project

#### Fix limited user promo code price ([#3927](https://github.com/shopsys/shopsys/pull/3927))

- hidden promo code prices in order confirmation for limited user
- fixed cart skeletons
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e8e53ed6f667c13b1eb438b8f03d59f2db641df8) to update your project

#### Fix product detail gallery ([#3928](https://github.com/shopsys/shopsys/pull/3928))

- polished product detail gallery image sizes
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/07dd2e91300a0fc558bfdcad0ef53809e065b4a7) to update your project

#### Skeleton loaders ([#3935](https://github.com/shopsys/shopsys/pull/3935))

- new simple way to create a skeleton loader with straightforward settings for sizes
- created custom component `<Skeleton />`
- removed library `react-loading-skeleton`
- skeletons are now much nicer and better positioned in layout
- just use `<Skeleton className="w-10 h-5" />
- optionaly you can add `rounded` class name for specific elements, default is `md`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ee082fd73cc60ae9d89642df30f4df1c43aa9850) to update your project

#### Unify user menu drawer ([#3936](https://github.com/shopsys/shopsys/pull/3936))

- user menu drawer is now unified and can be open from header or user menu button in account page on mobile devices
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1b6da251b433ad3e49e92d2d79766205d5673b22) to update your project

#### Icon button in responsive product list ([#3937](https://github.com/shopsys/shopsys/pull/3937))

- add to cart button is now showing just icon in responsive product list
- updated spinbox to match button sizes
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/aa6f716fdd5bd729072da7d4c3cfd9a25f292a86) to update your project

#### Fix Luigis box size in cart page ([#3943](https://github.com/shopsys/shopsys/pull/3943))

- Luigis box size is now correct in cart page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a8056ab4ea610932b47dc1db7403e4376dd73c0f) to update your project

#### Change pagination scroll location ([#3954](https://github.com/shopsys/shopsys/pull/3954))

- changed focus point after user uses pagination for better UX
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f1f67c9406d7cb282624b15d780c40f42e8283d2) to update your project

#### Refactor of Image sizes prop ([#3938](https://github.com/shopsys/shopsys/pull/3938))

- based on the images resposivity, the `sizes` prop was adjusted accordingly to optimize image selection from srcset based on the actual image size for given viewport
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ca791256343a8cb47a03586603ae2c72e0103e4d) to update your project

#### Change defer location and z-index ([#3955](https://github.com/shopsys/shopsys/pull/3955))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/05437d13762760d64f7c2386b952393ed978ce25) to update your project

#### Numeric keyboard for postcode input ([#3956](https://github.com/shopsys/shopsys/pull/3956))

- added numeric keyboard in postcode input for better UX on mobile phones
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b2edefd09516ee6912839315636bca8f431e329f) to update your project

#### Long banner name ([#3957](https://github.com/shopsys/shopsys/pull/3957))

- fixed long banner button name with class name `wrap-aywhere`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/83ba842458d3ebcbe45d91178826be2bd90e3019) to update your project

#### Redesign Transport and Payment ([#3958](https://github.com/shopsys/shopsys/pull/3958))

- redesigned transport and payment in order process based on figma design and consultations
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/64371a5f547f4b1d644e3479794495361352f626) to update your project

#### Fix GrapesJS header in template ([#3959](https://github.com/shopsys/shopsys/pull/3959))

- the upgrade to Tailwind v4 introduced changes to class naming conventions that caused rendering issues in GrapesJS templates
    - a specific issue occurred with the slash (/) character in class names like `bg-linear-to-tr/srgb`. Since HTML class names cannot contain slashes, they are automatically converted to dashes when rendered (becoming `bg-linear-to-tr-srgb`), breaking the intended styling
    - this has been resolved by creating a dedicated `grapesjs.css` file with custom component classes that work properly in the GrapesJS environment
- as part of this fix, we've improved the organization of Tailwind-related files for better maintainability and developer experience
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/31b626e644419f9c56b50c10af7684a103e7a9a8) to update your project

#### navigation item route ([#3968](https://github.com/shopsys/shopsys/pull/3968))

- `NavigationItem` FE API type now provides new `routeName` field of new `FriendlyUrlRouteEnum` type which is used for resolution of the item link skeleton
- added fallback for null `routeName` by comparing link with `FriendlyPagesDestinations` and selecting correct skeleton type
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/33f0c777d37207afd639e830bf97b138ae2e21db) to update your project

#### Improve middleware security ([#3971](https://github.com/shopsys/shopsys/pull/3971))

- compare `slugType` query value (possible user input) with predefined friendly url types before using it
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0ceb4b6c202af1cc408bd17695b15e97a559e43b) to update your project

#### fix calling contactFormMainText setting query with empty text ([#3974](https://github.com/shopsys/shopsys/pull/3974))

- graphql query `settings::contactFormMainText` now may return null, fix your code accordingly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/745f1d12c7f72041cdbe62f6ee8256c6b3c44488) to update your project

#### Accessibility part 1 ([#3975](https://github.com/shopsys/shopsys/pull/3975))

- improved accessibility
    - replaced non-interactive elements with buttons
    - added labels, aria-labels and titles for interactive elements
    - Safari requires `tabIndex` on every interactive element for keyboard navigation
- updated unit tests for links
- added focus trap for modals (keeps user's focus within the modal)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/787d9ca53796128862676194693c92e5b5fc5f14) to update your project

#### Fix product list limit ([#3977](https://github.com/shopsys/shopsys/pull/3977))

- fix load more causing API error
- fix ssr error handeling in case of API limit error response (defaults to `404` now) for `category`, `brand` and `flag` detail pages
- fix tests
- note that changing `PRODUCT_LIST_LIMIT` needs to be aligned with `maxAllowedItems` prop in `PageSizeValidator.php` to prevent API rejecting the request due to exceeding maximum allowed limit
- refactored `paginationScrollRef` from prop drilling to `PaginationProvider` and accessing it via `usePaginationContext`
- refactored `getRedirectWithOffsetPage` to use `getOffsetPage`
    - this function just cuts off any loadmore and add it to current page
    - that means we just redirect user to the last loaded page
- improved UX of scroll restoration in case of offsetting page
    - custom hook `useScrollRestoration` was implemented
    - developer can specify `scrollTargetRef` or `scrollY` position to scroll into
    - because of the page offset, the next router had no state for scroll position restoration
    - in this case we simply scroll under the sort bar

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d83853249707dcd5c8941bce1cab5dfddb711299) to update your project

#### improve order payments confirmation pages ([#3918](https://github.com/shopsys/shopsys/pull/3918))

- the order payment page now uses a general query to get proper content
- a new button to show again the payment instructions was added
- a new stepper flow `paymentInProcess` was added to `OrderConfirmationStepperFlows.ts`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7c41ee9dc8407f8b28ad1a2f127c8d8b31be3465) to update your project

#### Undefined in page title ([#3988](https://github.com/shopsys/shopsys/pull/3988))

- page title is not showing undefined when there is no page title suffix in administration
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a0c518f525e127ad0cd73f16ff667152f89b7705) to update your project

#### Fix main variant detail accessories ([#3992](https://github.com/shopsys/shopsys/pull/3992))

- added deferred accessories to main variant detail
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f3bf8cefc67b858ad4dd7fe708eeeeda3434b4de) to update your project

#### Fix order confirmation skeleton responsive ([#3993](https://github.com/shopsys/shopsys/pull/3993))

- order confirmation responsive skeleton is now showing correctly and not overflowing
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/50ef841b40e2a29aadef5f523e240fbb72427a89) to update your project

#### Order confirmation product image overflowing ([#3994](https://github.com/shopsys/shopsys/pull/3994))

- product image has max size now
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bc41e3bc0951716fb3f1c38cec7f4004819b110e) to update your project

#### Fix related products slider in product detail page ([#3995](https://github.com/shopsys/shopsys/pull/3995))

- slider arrows are now visible in related products slider
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3a09acc6ed5809351cc382b4c58be49069865da3) to update your project

#### Fix contact informations animation ([#3996](https://github.com/shopsys/shopsys/pull/3996))

- contact informations address animation is now showin correctly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/44fbd9a3c59f9d83a3d54572e6f04fdca1dd806e) to update your project

#### make available order payments filtered on the backend instead of storefront ([#3922](https://github.com/shopsys/shopsys/pull/3922))

- component `PaymentsInOrderSelect.tsx` now already obtain available payments from API, so it's not necessary to filter them manually
- GraphQL field `OrderPaymentsConfig#currentPayment` now return null if the original payment is not available anymore
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ee248f68890add79fb182ace935ee0835dae30ac) to update your project

#### Add useMemo, useCallback, memo for better optimalization ([#4001](https://github.com/shopsys/shopsys/pull/4001))

- wrapped pure functional components with `memo()` to prevent unnecessary re-renders
    - with pattern: `export const Component = memo(ComponentFunction);`
- wrapped data transformations in `useMemo()` to prevent recalculation on each render
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a86c8a8e5714aed8427bf2c1dfaf3bec41967c75) to update your project

#### Fix CK editor grid layout ([#4014](https://github.com/shopsys/shopsys/pull/4014))

- added new css styles for grid in `user-text` section
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/601d7ee5881731318d95616178d56f333fe7be4a) to update your project

#### Enable dev-debug mode for storefront ([#4011](https://github.com/shopsys/shopsys/pull/4011))

- storefront now runs in dev mode with Node.js inspector enabled by default, allowing Chrome DevTools debugging
- docker and npm scripts updated; see new documentation at `docs/storefront/debugging.md` for setup and screenshots
- More Details About Why Both Ports Are Opened
    - The Docker configuration exposes both ports 9229 and 9230 for debugging purposes. Here's the technical reason:
    - When Node.js is started with the `--inspect=0.0.0.0:9229` option, Next.js internally spawns a second Node.js instance and automatically increments the port number by 1, resulting in port 9230 being used for the actual debugging session where you can inspect your Next.js backend application files.
    - Both ports are opened proactively because:
        - **Port 9229**: The initial inspector port that may be used directly if Next.js changes its internal behavior in future versions
        - **Port 9230**: The current port where Next.js actually runs the debugger for backend code inspection

    - This approach ensures forward compatibility - if Next.js modifies its port allocation logic in the future and stops spawning the additional instance, debugging will still work on port 9229 without requiring code changes.
    - for forward compatibility, both `9229` and `9230` ports are opened proactively - if Next.js modifies its port allocation logic in the future and stops spawning the additional instance, debugging will still work on port `9229` without requiring code changes.

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/113d5a4914b863f19ed9af6344942149303288ba) to update your project

#### performance improvements for TBT metric ([#4012](https://github.com/shopsys/shopsys/pull/4012))

- turned off Sentry replay to improve performance
- added initial delay to banners slider
- moved GTM head script to the bottom of app page content
- fixed possible undefined in url(...)
- fixed various warnings and QOL improvements
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/96dfa5d3e429fc448c759e624d8fa09e4bebfcf0) to update your project

#### fix disappearing content after clicking to some links ([#4024](https://github.com/shopsys/shopsys/pull/4024))

- when using `target="_blank"` in the `ExtendedLink` component, the content of page would disappear (it is stuck in a loading state without skeleton)
- it is now reflected in the `isWithoutOpeningInNewTab` condition as well
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d22afcd5de78a06c2cdc510967216587f16ec47f) to update your project

#### Swap cypress visit test for category detail ([#4028](https://github.com/shopsys/shopsys/pull/4028))

- swapped the category detail url from `/electronics` to `/personal-computers-accessories` in `demodata.ts` file
- this category should have an image that does not cause false-positives in Cypress acceptance (screenshot) tests
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9506da2987f5d8b5d66f2423cdbd8f72f5a5ee83) to update your project

#### Fix price range slider ([#4040](https://github.com/shopsys/shopsys/pull/4040))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7e79264bfce168926df6c248a44db9d7ffad1339) to update your project

#### Fix duplicate error messages and improve Spinbox validation for cart quantity ([#4039](https://github.com/shopsys/shopsys/pull/4039))

- fixed duplicate error messages when adding excessive quantities to cart (GraphQL integer overflow)
- refactored Spinbox component for better readability and maintainability:
    - extracted validation utilities (`isValidNumber`, `isWithinMaxLimit`)
    - renamed functions for clarity (`onChangeValueHandler` → `handleValueChange`, `validateNaNSpinboxValue` → `restoreValueOnEmpty`)
    - improved last valid value tracking to properly restore user input on blur when field is empty
    - added client-side validation to prevent 32-bit signed integer overflow (MAX_CART_QUANTITY = 2147483647)
    - restricted input to integers only (prevents decimal point entry, rounds any decimal values to nearest integer)
- added comprehensive test coverage for Spinbox component with 42 test cases covering all edge cases:
    - basic rendering, button interactions, input validation, callback functionality
    - button states, edge cases, accessibility features
    - extensive keyboard interactions (Tab/Shift+Tab navigation, Enter/Space key activation, decimal input handling, disabled state behavior)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d52aa10879c0a4615c335e9569b63e41d403192c) to update your project

#### Fix blog signpost z-index ([#4045](https://github.com/shopsys/shopsys/pull/4045))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/31fb414d7264716927a37a693d4184926918c6c3) to update your project

#### Fix blur background in overlay ([#4046](https://github.com/shopsys/shopsys/pull/4046))

- Fixed conflict between `mix-blend-multiply` and blur overlay background by adding CSS `isolation` property
- Added possibility to configure fullscreen overlay setup for better loader positioning
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d38119d75d5d06e25f8966bb0cb8a9d418ee2dd7) to update your project

#### finalize Sentry integrations ([#4038](https://github.com/shopsys/shopsys/pull/4038))

- upgraded `@sentry/nextjs` from version 9.12.0 to 9.38.0 in `project-base/storefront/package.json`
- added new Sentry Replays and Feedback integrations with conditional lazy loading in `project-base/storefront/instrumentation-client.ts`
- added new environment variables `SENTRY_FEEDBACK_ENABLE` and `SENTRY_REPLAYS_ENABLE` in `project-base/storefront/.env` for feature flags
- updated `project-base/storefront/next.config.js` to conditionally exclude replay-related code from bundle when replays are disabled
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a26904e2d63db43dfcde808a9a9854b5d4e459e7) to update your project

#### Add comprehensive unit tests for order process utility functions ([#4060](https://github.com/shopsys/shopsys/pull/4060))

- extracted pickup place functions from internal locations and moved them to `utils/cart/pickupPlaceCalculations.ts`
- added 7 comprehensive test files significantly increasing test coverage for order process utilities
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/91042b4559fbdd0f058ab113dcd1fcb74a625f15) to update your project

#### Footer redesign ([#4061](https://github.com/shopsys/shopsys/pull/4061))

- redesigned footer is now based on figma design
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7b40d761ee08ed0b82d1bc1473dbb3b2a5f5bd06) to update your project

#### Fix storefront offline mode with font fallbacks and hydration error ([#4063](https://github.com/shopsys/shopsys/pull/4063))

- enabled storefront offline mode to improve development experience
- added comprehensive font fallbacks to Google Fonts (Inter and Raleway) configuration in `Fonts.tsx`
    - configured `fallback` arrays with system fonts: `-apple-system`, `BlinkMacSystemFont`, `"Segoe UI"`, `Roboto`, `"Helvetica Neue"`, `Arial`, `system-ui`, `sans-serif`
    - added CSS variables `--font-inter-fallback` and `--font-raleway-fallback` for consistent fallback reference
    - ensures fonts display properly even when Google Fonts are unavailable
- fixed hydration warning in `DeferredRecommendedProducts` component caused by server-client mismatch
    - the issue occurred when GraphQL query state differed between server (not fetching) and client (briefly fetching), causing skeleton to appear only on client
    - added `useEffect` hook to track client mount state and only show loading skeleton after client hydration
    - ensures server and client render identical content initially, preventing React hydration mismatches
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e44b2ff78688c9d9576db5b5e0f2bbe9d1616295) to update your project

#### Fix complaint image size ([#4066](https://github.com/shopsys/shopsys/pull/4066))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4fed2334b60dd463a3a0fa4d027e4b36a5c3cfe8) to update your project

#### Polished GrapesJS ([#4067](https://github.com/shopsys/shopsys/pull/4067))

- removed `Magic lines` from CKEditor (red lines which causing weird behaviour with GrapesJS)
- removed `Heading 1` from CKEditor to prevent invalid HTML in articles and blog articles
- updated styles for `ul` and `ol` for multiple levels
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/984c61c8bea136dce24278a3690ce576561e9a21) to update your project

#### Voice Over opening hours ([#4080](https://github.com/shopsys/shopsys/pull/4080))

- added accessibility improvements for Voice Over users when reading store opening hours
- implemented `formatAccessibleTime` utility function to format time strings in a more accessible way for screen readers
- enhanced `OpeningStatus` component with proper ARIA labels for current store status
- updated `StoreListItem` component with better keyboard navigation and screen reader support
- improved opening hours display with accessible time formatting that adapts to locale (24-hour vs 12-hour format)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cb264cd65574426b9f6a4c1bec2545e268cc0755) to update your project

#### Add test coverage for filter and sort ([#4069](https://github.com/shopsys/shopsys/pull/4069))

- added comprehensive unit test coverage for product filtering and sorting components
- added reusable accessibility testing utilities for keyboard navigation and focus management
- added specialized filter testing utilities for price ranges, parameters, brands, and flags
- added cross-component integration e2e tests for filter and sort workflows
- added make commands `run-specific-test-base` and `run-specific-test-actual` for running individual test suites
- updated Cypress entrypoint with improved debugging and specific test execution support
- enhanced unit testing documentation with detailed vitest configuration and best practices
- added comprehensive Cypress e2e testing documentation covering setup, writing tests, and debugging
- added meta-tests to validate testing utility functions and ensure testing infrastructure reliability
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ce0e67194c0351000a3b0d14a20cc5a5632d002b) to update your project

#### Fix customer info in order detail ([#4116](https://github.com/shopsys/shopsys/pull/4116))

- customer info is now showing correct address in order detail
- billing address in order process now showing correct country
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/91ba93d119b2ca1457b54a2cd89c5b892f6b5356) to update your project

#### Fix sales representatives ([#4117](https://github.com/shopsys/shopsys/pull/4117))

- sales represenatives is now showing correctly without image
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0b22633ebf844c7fa8ed198859ede9b91a08090e) to update your project

#### Fix popup scrollbars ([#4118](https://github.com/shopsys/shopsys/pull/4118))

- fixed product availability popup scroll bars
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b9735cb577863654312cc7307e5825dbd8662f2c) to update your project

#### Refactored GrapesJS for better DX ([#4115](https://github.com/shopsys/shopsys/pull/4115))

- added improved code organization by consolidating common GrapesJS configuration and reducing code duplication between `GrapesWebEditor` and `GrapesMailEditor`
- added better plugin management system for easier maintenance and extensibility of GrapesJS components
- added default user-text styles to improve the appearance of default articles
- added Makefile command (`make generate-tailwind-for-admin`) to generate Tailwind classes that are copied to `project-base/storefront/public/tailwind-for-admin/style.css` and imported into the GrapesJS canvas for better styling capabilities, ensuring GrapesJS works correctly in development mode
- improved CKEditor behavior so that pressing Enter now automatically creates new paragraph tags (`<p>`) in HTML to ensure correct styling and provide an easier experience for admins
- fixed text-with-image component bellow drop zone detection - because we switched to a css `display:grid` layout (instead of flexbox), the natural grapesjs detection of drop zone was broken, it does not correctly detects the bellow zone for grids, the solution is to wrap these kind of components with a div with `display:block`
- button link has completely turned off the ckeditor activation, all the properties are now possible to change only by grapesjs traits
- components with images have now possibility to change the size of the image by dragging in the corners
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5800983ce609169ae65262bb61a969a979e42433) to update your project

#### Fix range slider min max values ([#4121](https://github.com/shopsys/shopsys/pull/4121))

- range slider is now correctly set min/max value when user try value which is not in range
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/554c09f9b4c769343d9f469287c3e25b8ca1b9c5) to update your project

#### refactor administrator login as user ([#4124](https://github.com/shopsys/shopsys/pull/4124))

- the administrator now can log in as a customer user in the storefront using exchange tokens
- after the homepage is accessed with "exchangeToken" query parameter, new mutation `loginViaExchangeToken` is called
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8f42b6ba9318d1cac5165aafc0b4b69d426e5646) to update your project

#### GitLab Cypress screenshots ([#4148](https://github.com/shopsys/shopsys/pull/4148))

- fixed GitLab Cypress screenshots regeneration
- added Cypress smoke tests
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7d05a6896180d5c5b4f64dc2575cba75733a7085) to update your project

#### Hide scrollbars in Firefox ([#4156](https://github.com/shopsys/shopsys/pull/4156))

- added utility for hiding scrollbars for better readability
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a4ddbd3ceb47feda0f580ff06b6b5a301873c4a0) to update your project

#### HTML lang is now properly set for all domains ([#4160](https://github.com/shopsys/shopsys/pull/4160))

- HTML lang attribute takes its value from the domain configuration instead of using the default locale from i18n configuration
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7a90d12fcaad68e96d45aa535fc5461318525524) to update your project

#### Fix popup ([#4168](https://github.com/shopsys/shopsys/pull/4168))

- fixed styles for popup and flash message
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/dcb7bff389f7e1a704129ef8e4a7842f894224ef) to update your project

#### Fix URQL dependency conflicts and security vulnerabilities ([#4170](https://github.com/shopsys/shopsys/pull/4170))

- resolved critical security vulnerabilities in Next.js and transitive dependencies
- added pnpm override to force @urql/core@6.0.1 across all packages
    - fixed issue where URQL core v6+ includes directives in GET request URLs that need sanitization before native fetch
    - extended `fetcher.ts` with `createCleanedInput()` function to properly clean GraphQL directives (`@redisCache`, `@friendlyUrl`) from GET request URLs, handling both encoded and unencoded formats
- upgraded Next.js to 15.4.7 with authorization bypass and cache poisoning fixes
    - introduced `getPublicConfigProperty()` and `getServerConfigProperty()` helpers with proper TypeScript typing
    - fixed Cypress test compatibility issues by handling cases where `getConfig()` returns undefined in non-Next.js environments
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ef116819352cb195a733035bb83e9cb7508f427a) to update your project

#### Fix text selection ([#4179](https://github.com/shopsys/shopsys/pull/4179))

- fixed text selection for most of the links. Only banners, product box and bestsellers are disabled
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8971144ba4789676e17bfd8058d109ee500bfc9b) to update your project

<!-- storefrontNotes -->

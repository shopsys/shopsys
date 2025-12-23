# UPGRADING FROM 17.x to 18.0

The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading

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
    - _(macOS only)_ run `mutagen project terminate` first, then `docker compose down --volumes`
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

### Movement of features from project-base to packages

- in this version, there are some features that have been moved from `project-base` to the packages, mostly to the `framework` and the `frontend-api` package
- each section in the upgrade guide contains a link to the `project-base` diff and besides the particular upgrade instructions, there is also a list of the moved features you should be aware of (if there are any)
- if your project was originally not developed from the Commerce Cloud version, or it was developed on a version lower than `v13.0.0`, these feature movements should not affect you during the upgrade
- otherwise, you might need to adjust your project to the changes:
- if you had no custom changes in the moved features, you should be fine, you can safely remove the features from your project and use the ones from the packages (project-base diff in each section will help you with that)
- if you had custom changes in the moved features, you will need to adjust your project to the changes
- you should remove everything that was not modified in your project and keep just the custom changes using the recommended ways of the [framework extensibility](https://docs.shopsys.com/en/latest/extensibility/)
- one way or another, you should pay a special attention to the database migrations that were added with the feature movement. If they suit your needs, you should keep them and remove the original migrations from your project, otherwise, you should skip the newly added migrations.

### Introduction of strict types

- with each change, we are updating most classes that have been altered by that change to use strict types
- this means that you will need to update your project to use strict types as well
- we do not see writing upgrade notes for such changes as beneficial, as it would mean for you to check every single change manually even if only a few occurrences would apply to your project
- we are currently not aware of easy way to automate this process, so you will need to do it manually
- probably the easiest way is to run `composer install`, `php phing standards-fix` and `php phing phpstan` commands, which will fail on errors caused by incompatibility strict types and fix those errors manually

### Backend

#### Grids ordering by administrator language ([#4135](https://github.com/shopsys/shopsys/pull/4135))

- added new factories for all DataSource types to ensure consistent instantiation
    - `\Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory`
    - `\Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory`
    - `\Shopsys\FrameworkBundle\Component\Grid\ArrayDataSourceFactory`
    - `\Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory`
    - `\Shopsys\FrameworkBundle\Component\Grid\MoneyConvertingDataSourceDecoratorFactory`
- added proper collation support for textual columns ordering by administrator language
- `\Shopsys\FrameworkBundle\Component\Doctrine\CollationOrderByWalker`
    - new Doctrine walker that applies collation to ORDER BY clauses for textual fields (ASCII_STRING, STRING, TEXT)
- all grid factory classes now use the appropriate DataSource factories instead of direct instantiation
- if you are using custom grid implementations with direct DataSource instantiation, use the new factory classes for consistent collation behavior
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0b84f41b132459bc6246d9f38fd3dd11f8acd4eb) to update your project

#### Translation system extended with namespace support ([#4153](https://github.com/shopsys/shopsys/pull/4153))

- translation system now supports multiple namespaces (e.g., `common.json`, `accessibility.json`) alongside the existing single-file approach
- **BREAKING CHANGE**: `LANGUAGE_CONSTANTS_URL_PATTERN` environment variable has been replaced with `TRANSLATION_NAMESPACES` JSON configuration
- **Action required**: Update your `.env` file to replace:
    ```
    LANGUAGE_CONSTANTS_URL_PATTERN="http://webserver:8080/locales/%s/common.json"
    ```
    with:
    ```
    TRANSLATION_NAMESPACES='{"common": "http://webserver:8080/locales/%s/common.json", "accessibility": "http://webserver:8080/locales/%s/accessibility.json"}'
    ```
- new method `LanguageConstantFacade::generateAllNamespaceFiles()` available for namespace language files generation
- **BREAKING CHANGE**: `LanguageConstantFacade::generateLanguageConstantFile()` method was deleted in favor of `LanguageConstantFacade::generateAllNamespaceFiles()`
- **BREAKING CHANGE**: `LanguageConstantRepository::getTranslationsByLocaleIndexedByKey()` method now requires `$namespace` parameter
- **BREAKING CHANGE**: `LanguageConstantFacade::getOriginalTranslationsByLocaleIndexedByKey()` method now requires `$namespace` parameter
- **BREAKING CHANGE**: `LanguageConstantFacade::getUserTranslationsByLocaleIndexedByKey()` method now requires `$namespace` parameter
- namespace configuration is automatically injected into services via `LanguageConstantFacade` constructor
- existing translations remain in 'common' namespace and continue working without changes
- **REMOVED**: `LanguageConstantQuery` GraphQL resolver and related GraphQL types have been removed as language constants are no longer exposed via GraphQL API
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9b7f400ee68569592fb1950c282755708f3d42b9) to update your project

> Important: DB overrides for moved accessibility strings

- If you have customized translations stored in the database (Admin > Content > Language constants) for strings that are now requested with `ns: 'accessibility'`, those overrides remain under the `common` namespace after the migration and will no longer be applied.
- Action required: Identify your affected keys and migrate their entries from `common` to `accessibility` namespace so the overrides take effect again.
    - How to find affected keys: search the Storefront for usages of `ns: 'accessibility'` to get the list of keys that moved to the `accessibility` namespace.
      For example on host: `rg -n "t\('([^']+)',\s*\{[^}]*ns:\s*'accessibility'" project-base/storefront`
    - How to migrate: either re‑create the overrides via Admin UI under the `accessibility` namespace, or update the DB records for those keys by changing `language_constants.namespace` from `common` to `accessibility` (mind the unique `key+namespace` constraint and copy/merge translations if an `accessibility` row already exists for a given key).
    - After migration, regenerate namespace files per locale from Admin > Content > Language constants > Generate files, or programmatically via `LanguageConstantFacade::generateAllNamespaceFiles()`.

#### prevent exceeding available stock for products with negative stock disabled ([#4173](https://github.com/shopsys/shopsys/pull/4173))

- review and configure "Allow negative stock" setting for your products in administration if needed
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3620b8199d29aa7afc891bcad06c5d36bc6dbcd7) to update your project

#### fix makefile tasks ([#4183](https://github.com/shopsys/shopsys/pull/4183))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/739fa93fc98227b980e14649e96b3e02b0c55d18) to update your project

#### fix warnings in storefront Dockerfiles ([#4188](https://github.com/shopsys/shopsys/pull/4188))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5c506c999d9c6a631704f748059d1abe087170a9) to update your project

#### enable domain configuration with path fragment ([#4113](https://github.com/shopsys/shopsys/pull/4113))

- `Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig` constructor now has new required `$baseUrl` and optional `$postfix` parameters
    - `DomainConfig::getUrl()` method returns the whole URL including the postfix, if you need just the host part, use `DomainConfig::getBaseUrl()` method instead
- `Shopsys\FrameworkBundle\Component\Domain\Domain::MAIN_ADMIN_DOMAIN_ID` constant has been removed as the administration domain is no longer tied to a specific domain ID
- there is new `Shopsys\FrameworkBundle\Component\Router\AdministrationRouter` that needs to be used in the administration context instead of `CurrentDomainRouter` when generating/matching the admin routes
    - if you need to generate a URL for a specific domain in the administration context, use `DomainRouterFactory::getRouter()` method with the specific domain ID to get a `DomainRouter` instance for that domain
- using `Shopsys\FrameworkBundle\Component\Domain\Domain::getCurrentDomainConfig()` in the administration context now throws `NoDomainSelectedException` exception, use `Shopsys\FrameworkBundle\Component\Domain\Domain::getDomainConfigById()` instead
- check translations of your email templates that are used for sending the emails for the administrators (e.g., 2FA, reset password)
    - translation based on the current administrator locale is now used instead of the current domain locale
    - `Domain::getFirstDomainIdMatchingAdminSelectedLocale()` method can be used to get a domain ID that matches the current administrator locale
- `Shopsys\FrameworkBundle\Twig\ImageExtension::getImageUrl()` method now requires `int $domainId` parameter
- `Shopsys\FrameworkBundle\Component\Context\AdminContext` constructor parameter `$adminRoutePrefixes` has been renamed to `$additionalAdminPathPrefixes`
    - the meaning of the parameter has changed, it now represents additional path prefixes instead of the original route names for the administration context
    - the configuration parameter name has changed from `admin_context_route_prefixes` to `admin_context_additional_path_prefixes`
- `Shopsys\FrameworkBundle\Component\Router\NormalizeUrlTrailingSlashSubscriber` has been renamed to `NormalizeAdminUrlTrailingSlashSubscriber`
    - the subscriber now uses `Shopsys\FrameworkBundle\Component\Router\AdministrationRouter $administrationRouter` instead of the original `CurrentDomainRouter $currentDomainRouter`
- `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\NormalizeUrlTrailingSlashListener` class has been removed as redundant
    - the functionality is covered by `NormalizeAdminUrlTrailingSlashSubscriber`
- `Shopsys\FrameworkBundle\Model\Sitemap\SitemapListener::addUrlForHomepage()` method no longer accepts `string $section` parameter
    - the section name is now resolved using `SitemapFacade::getSectionNameForDomainConfig()` method
- in your `composer.json`, update the dependency on `shopsys/deployment` package to `^4.1.0` version and check [the upgrade notes](https://github.com/shopsys/deployment/blob/v4.1.0/UPGRADE.md) for this package
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/be5b265cd9a2988a64be8d12a2c26056979ad2f9) to update your project
- see also [project-base diff](https://github.com/shopsys/project-base/commit/160cc7c59) of [#4265](https://github.com/shopsys/shopsys/pull/4265) with additional fix

#### QR code payment ([#4195](https://github.com/shopsys/shopsys/pull/4195))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c83f6802239c44c4b3fbaf363bd0360ed4a17844) to update your project

#### Product gifts ([#4193](https://github.com/shopsys/shopsys/pull/4193))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d039c7996315509402ed5f9bf7110fc513bb4cd2) to update your project
- Main improvement was adding types to `CartItem` entity. Check usages of `Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum` enum and consequences.
- In `Shopsys\FrameworkBundle\Model\Cart\Cart` entity are new functions getProductCartItems and getProductGiftCartItems.
    - Check your usages getCartItems and change it to getProductCartItems or getProductGiftCartItems if necessary.
    - eg. see `addProductToExistingCart` in `Shopsys\FrameworkBundle\Model\Cart\CartFacade`
    - eg. see `getModifiedPriceItemsAndUpdatePrices` in `Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher`
- `Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory::create()` function now has a new required `string $type` parameter that has to be one of the values defined in `Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum`.
- `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddProductMiddleware::createProductItemData()` was removed, use `addProductOrderItemData` instead

#### Promotion X + Y free ([#4194](https://github.com/shopsys/shopsys/pull/4194))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package
    - `\Shopsys\FrameworkBundle\Model\Product\ProductData::$saleExclusion` property has been renamed to `$domainSellingDenied`
    - `\Shopsys\FrameworkBundle\Model\Product\Product` methods renamed:
        - `getCalculatedSellingDenied()` has been renamed to `isCalculatedSellingDenied()`
        - `getSaleExclusion()` has been renamed to `isSellingDeniedOnDomain()`
        - `getCalculatedSaleExclusion()` has been renamed to `isCalculatedSellingDenied()`
        - `getSellingDenied()` has been renamed to `isSellingDenied()`
    - `\Shopsys\FrameworkBundle\Model\Product\ProductDomain` methods renamed:
        - `getSaleExclusion()` has been renamed to `isSellingDenied()`
        - `setSaleExclusion(bool $saleExclusion)` has been renamed to `setSellingDenied()`
        - `getCalculatedSaleExclusion()` has been renamed to `isCalculatedSellingDenied()`
    - `\Shopsys\FrameworkBundle\Model\Product\ProductDomain::$calculatedSaleExclusion` property has been renamed to `$calculatedSellingDenied`
    - `\Shopsys\FrameworkBundle\Model\Product\ProductDomain::$saleExclusion` property has been renamed to `$sellingDenied`
    - `is_sale_exclusion` and `calculated_selling_denied` has been removed from both `Elasticsearch` and `Frontend API`, use `selling_denied` instead
    - `\Shopsys\FrameworkBundle\Model\Product\Product::$calculatedSellingDenied` property has been removed

- `\Shopsys\FrameworkBundle\Model\Flag\FlagDependencies` constructor now requires third parameter `bool $hasPromotionXyDependency`

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6c93f69d80efb9749a9dbee225e60a35ea19a29e) to update your project

#### Nginx configuration improvements ([#4204](https://github.com/shopsys/shopsys/pull/4204))

- if you have any custom customer-facing controller, make sure you throw `Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NotFoundRedirectToStorefrontException` to render proper storefront 404 page
- if you have a custom nginx configuration, review and apply optimizations from the updated configuration files
- check if you still need the redirect rule for old image URLs, and if so, skip removing the following location (it may slightly differ in your custom configuration):
    ```
        location ~ ^/content(-test)?/images/(?<entity_name>\w+)(?<image_type>/\w+)?/(?<image_size>(default|original|galleryThumbnail|modal|list|thumbnail|thumbnailSmall|thumbnailExtraSmall|thumbnailMedium|header|footer|productList|productListSecondRow|cartPreview|productListMiddle|productListMiddleRetina|listAside|listGrid|searchThumbnail|listBig)/)(?<add_image_id>\d+--)?(?<image_name>([\w\-]+_)?(?<image_id>\d+))\.(?<image_extension>jpg|jpeg|png|gif) {
    ```
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e71bf42412a0689fcbf04d794e00a168530cb9b6) to update your project
- see also [project-base diff](https://github.com/shopsys/project-base/commit/b013cb8f4) to fix GitLab CI builds

#### Autocomplete favorites and improved UX ([#4215](https://github.com/shopsys/shopsys/pull/4215))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - product filter query search logic (`Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery::search()`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1d5581d6680e2dd3f6df281e582dd22da47547ce) to update your project

#### Name is no longer mandatory for Category, Blog Category and Blog Article ([#4209](https://github.com/shopsys/shopsys/pull/4209))

- Twig filter `productListDisplayNameByName` has been removed, use `nameWithFallbackOnEmpty` instead

#### Administration overhaul ([#3813](https://github.com/shopsys/shopsys/pull/3813))

- if you have custom or customized admin templates:
    - update all template extends/includes from `@ShopsysFramework/Admin/*` to `@ShopsysAdministration/*` equivalents
    - common migrations:
        - `@ShopsysFramework/Admin/Layout/layoutWithPanel.html.twig` → `@ShopsysAdministration/layout/layout_with_panel.html.twig`
        - `@ShopsysFramework/Admin/Grid/Grid.html.twig` → `@ShopsysAdministration/datagrid/grid.html.twig`
    - all admin templates have been restyled with Tabler UI - you need to review and update your templates manually (see Tabler documentation: https://tabler.io/docs/ and current admin templates for examples)
    - typical updates needed for most of the custom-made features can be inferred from https://github.com/shopsys/shopsys/pull/3813/commits/78f83f26d61fdff47d59a42194a4de1e438fbfd6

- changes regarding CSS and classes:
    - use `style="display-none"` instead of display-none class
        - beware: Bootstrap `d-none` class cannot be toggled with jQuery's `toggle/hide/show` methods
    - use `btn-*` instead of `btn--*` (notice the single dash)
    - all BEM styles removed, use Tabler classes instead
    - admin styles migrated from LESS to SCSS
    - review and update all custom admin stylesheets

- if you used removed form options:
    - remove `macro` option usage (replaced by new form theme and recommended length counters)
    - remove `icon_title` option usage (replaced by `help` option)
    - remove `display_format` option and `FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING` constant usage
    - remove `js_container` option usage (no longer needed with new form rendering)
    - remove `render_form_row` option usage (if you need similar behavior, use `form_widget()` directly instead of `form_row()`)

- if you used `\Shopsys\FrameworkBundle\Form\LocalizedFullWidthType`:
    - this form type has been removed
    - use standard `\Shopsys\FrameworkBundle\Form\Locale\LocalizedType` instead

- if you extended or used JavaScript validation:
    - `shopsys/jsformvalidator-bundle` dependency has been removed
    - `\Shopsys\FrameworkBundle\Form\JsFormValidatorFactory` class has been removed
    - `\Shopsys\FrameworkBundle\Twig\JsFormValidatorTwigExtension` class has been removed
    - JavaScript form validation is no longer supported, rely on server-side validation instead

- if you used removed Twig extensions:
    - `\Shopsys\FrameworkBundle\Twig\FormDetailExtension` - removed (functionality integrated into form theme)
    - `\Shopsys\FrameworkBundle\Twig\HoneyPotExtension` - removed (unused since JS Storefront implementation)
    - `\Shopsys\FrameworkBundle\Twig\ModuleExtension` - removed (unused since JS Storefront implementation)
- if you used `\Shopsys\FrameworkBundle\Controller\Admin\MenuController`:
    - this controller has been removed
    - menu is now rendered directly in `layout_with_panel.html.twig` template

- changes regarding JavaScript libraries and custom admin JavaScript:
    - Select2 library has been replaced with [Tom Select](https://tom-select.js.org)
    - Magnific Popup removed and replaced with [Tabler modals](https://docs.tabler.io/ui/components/modals)
    - jQuery ui color picker has been replaced with [Coloris](https://coloris.js.org)
    - new JavaScript utilities available in `packages/administration/assets/src/js/utils/`:
        - `modalWindow.js` - modern modal dialogs
        - `confirmWindow.js` - confirmation dialogs
        - `copyToClipboard.js` - clipboard functionality
        - `recommendedLength.js` - character count for inputs
    - review all custom admin JavaScript for compatibility
        - typically you need to check if the desired selectors are still present or replace them accordingly

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/12a4921645afb2de32cf16b5ce029a5cbf99ff59) to update your project

#### composer: require doctrine-bundle ^2.18 ([#4226](https://github.com/shopsys/shopsys/pull/4226))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b5772a2f0d6488d4674b427f0777b4f53a71a358) to update your project

#### GraphQL queries are sent exclusively by POST ([#4236](https://github.com/shopsys/shopsys/pull/4236))

- GraphQL endpoints now accept only POST requests – GET requests will return 405 (method not allowed) error
    - if you have custom GraphQL clients or external integrations using GET method, update them to use POST
    - see [project-base diff](https://www.github.com/shopsys/project-base/commit/28bbf7851e1ea5b64e63408ca5fc18c8abf5127d) to update your project

#### fix tag input in grapesjs product component ([#4237](https://github.com/shopsys/shopsys/pull/4237))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9cddd4d982ab63b090ec7971b64f2f8c599dbd23) to update your project

#### Allow display discounts breakdown in cart ([#4314](https://github.com/shopsys/shopsys/pull/4314))

- `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser`
    - method `calculatePriceForCurrentUser` was removed - use `calculatePricesForCurrentUser` instead, which returns `ProductPricesResult` containing both basic and selling prices
    - method `calculateBasicPriceForCurrentUser` was removed - use `calculatePricesForCurrentUser` instead, which returns `ProductPricesResult` containing both basic and selling prices
    - method `calculatePriceForCustomerUserAndDomainId` was removed - use `calculatePricesForCustomerUserAndDomainId` instead, which returns `ProductPricesResult` containing both basic and selling prices
    - method `calculateBasicPriceForCustomerUserAndDomainId` was removed - use `calculatePricesForCustomerUserAndDomainId` instead, which returns `ProductPricesResult` containing both basic and selling prices
- method `getProductSellingPrice` in `Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade` was removed as unused, use `ProductPriceCalculationForCustomerUser` class instead if necessary
- Twig function `getProductSellingPrice` from `Shopsys\FrameworkBundle\Twig\ProductExtension` has been removed as unused
- `Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation::calculateQuantifiedBasicAndSellingPrice()` method introduced to calculate both basic and selling quantified prices simultaneously, returning `QuantifiedProductPricesResult`
- `Shopsys\FrameworkBundle\Model\Order\OrderData` now has new properties for tracking discounts and basic prices:
    - `$totalProductPriceAdjustmentsDiscount` - tracks total discount from price adjustments
    - `$basicTotalItemsPrice` - tracks basic (pre-discount) total items price
    - new methods: `getTotalDiscountPrice()` returns combined discount amount, `getPromoCodeDiscountPrice()` returns promo code discount specifically
- class `Shopsys\FrontendApiBundle\Model\Cart\PromoCode\PromoCodeResolverMap` was removed, the proper object is created in `CartWithModificationResult` directly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1ae0d9abf35746211db63ff63c260f5a500fbe71) to update your project
- `Shopsys\FrameworkBundle\Model\Order\OrderData`
    - method `getTotalPriceWithoutDiscountTransportAndPayment()` was removed, use `$basicTotalItemsPrice` instead
- `Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult`
    - property `$totalPriceWithoutDiscountTransportAndPayment` was removed along with its getter and setter, use `$totalItemsPriceBeforeDiscount` instead
- updated `Cart` GraphQL type
    - `totalPriceWithoutDiscountTransportAndPayment` was removed, use `totalItemsPriceBeforeDiscount` instead
    - `totalProductPriceAdjustmentsDiscount` - total savings from product action prices and X+Y promotions
    - `totalItemsPriceBeforeDiscount` - total basic price of products in the cart before discounts
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1ae0d9abf35746211db63ff63c260f5a500fbe71) to update your project

#### fixed superadmin behavior ([#4238](https://github.com/shopsys/shopsys/pull/4238))

- method `getAllListableQueryBuilder()` in `Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository` was renamed to `getAllListableExcludingSuperadminQueryBuilder()`
    - use new method `getAllQueryBuilder()` if you need to fetch all administrators including superadmins
- method `getAllListableQueryBuilder()` in `Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade` was renamed to `getAllListableExcludingSuperadminQueryBuilder()`
    - use new method `getAllQueryBuilder()` if you need to fetch all administrators including superadmins
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/70c7861ab46f54401f605018392825a928911262) to update your project

#### Update admin styles ([#4260](https://github.com/shopsys/shopsys/pull/4260))

- updated administration template styles, grids, layouts, icons
- possibility to setup grid layout for multidomain cards
- fixed drag & drop for mobile devices
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/60b41430de226a7342d18180e8c81290f8612d4a) to update your project

#### Replace `CsrfProtection` annotation with attribute ([#4263](https://github.com/shopsys/shopsys/pull/4263))

- The `@CsrfProtection` annotation has been replaced with the `#[CsrfProtection]` attribute
- You can use **Rector** to automatically convert annotations to attributes. See the [Rector upgrade guide](https://docs.shopsys.com/en/18.0/project/upgrade-your-project-with-rector/) for instructions on how to run Rector.
    - Add the following configuration to your `rector.php` file and run Rector:

        ```php
        use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
        use Rector\Php80\ValueObject\AnnotationToAttribute;

        $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
            new AnnotationToAttribute(
                'Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection',
                'Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection'
            ),
        ]);
        ```

    - Run `php phing standards-fix` twice
        - The first run will remove the old namespace import
        - The second run will replace the fully qualified class name (FQCN) with an import

#### Customer now has properly resolved roles no matter the current context ([#4262](https://github.com/shopsys/shopsys/pull/4262))

- new role `ROLE_API_MANAGE_CUSTOMERS` introduced in `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole`
    - this role allows a user to manage other users within the company
- new role `ROLE_API_MANAGE_COMPANY_DATA` introduced in `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole`
    - this role allows a user to manage company data, such as billing address
- `ROLE_API_ALL` is now a parent role that includes all individual roles including `ROLE_API_MANAGE_CUSTOMERS` and `ROLE_API_MANAGE_COMPANY_DATA`
- if you are using `ROLE_API_ALL` in your custom Frontend API access control (GraphQL queries/mutations/voters):
    - consider replacing it with `ROLE_API_MANAGE_CUSTOMERS` if the functionality is specifically about managing customer users or `ROLE_API_MANAGE_COMPANY_DATA` for company data management
    - `ROLE_API_ALL` will continue to work as it inherits all permissions, but `ROLE_API_MANAGE_CUSTOMERS`/`ROLE_API_MANAGE_COMPANY_DATA` is more specific and preferred
- GraphQL query `customerUsers` now require `ROLE_API_MANAGE_CUSTOMERS` instead of `ROLE_API_ALL`
    - users with `ROLE_API_ALL` will still have access due to role hierarchy
- GraphQL mutation `ChangeCompanyData` now require `ROLE_API_MANAGE_COMPANY_DATA` instead of `ROLE_API_ALL`
    - users with `ROLE_API_ALL` will still have access due to role hierarchy
- method `Shopsys\FrontendApiBundle\Voter\CustomerUserVoter::isRoleApiAllGranted()` was renamed to `isEditedCustomerUserFromSameCompany()`
- method `Shopsys\FrontendApiBundle\Voter\CompanyOwnerVoter::isRoleApiAllGranted()` was renamed to `isCompanyCustomer()`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/67a451cab68f97e56f327b8b7154c8c1343bb2be) to update your project

#### Improved automatic card grouping in admin forms ([#4261](https://github.com/shopsys/shopsys/pull/4261))

- form fields without an explicit group are now automatically placed inside Bootstrap cards in the administration
- a new form option `renders_in_own_card` has been added (default: `false`):
    - when set to `true`, the form field renders in its own Bootstrap card and is excluded from automatic grouping
- built-in container form types like GroupType, ProductsType, OrderItemsType, and OrderListType now use this option by default
- if you have custom form types that render their own Bootstrap card, set `'renders_in_own_card' => true` in their configureOptions() method

#### order withdrawal from contract ([#4246](https://github.com/shopsys/shopsys/pull/4246))

- There is a new property `$deliveredAt` in the `Shopsys\FrameworkBundle\Model\Order\Order` entity representing the date and time when the order was marked as delivered.
    - the property is automatically populated once the order status is changed to `DONE`
    - the date is used to determine the deadline for the withdrawal from the contract
    - when the date is not set, the customer is allowed to withdraw from the contract, so be sure to set the date for your existing orders carefully
- `Shopsys\FrameworkBundle\Model\Mail\MessageData` constructor now accepts arrays for email recipients:
    - `$toEmail` parameter type changed from `string` to `string|array<string>`
    - `$bccEmail` parameter type changed from `string|null` to `string|array<string>|null`
    - this change is backward compatible, existing code passing single strings will continue to work
    - new methods `getToEmailAsArray()` and `getBccEmailAsArray()` added for convenient array access
- `Shopsys\FrameworkBundle\Model\Order\Mail` class was changed:
    - `getDisplayPrice()` method was moved to the new `MailDisplayPriceResolver` class
    - `DISPLAY_PRICE_WITH_VAT`, `DISPLAY_PRICE_WITHOUT_VAT`, `DISPLAY_PRICE_BOTH`, and `DISPLAY_PRICE_SELLING` constants were moved to the new `MailDisplayPriceResolver` class
- `Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade::createOrderItemQueryBuilder()` was renamed to `createOrderItemExcludingOrdersWithWithdrawalQueryBuilder()`
- `Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade::sendEmail(Order $order)` interface changed to `sendEmail(Order $order, OrderStatus $orderStatus)`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b7b8706f5f9abb4903b08861ecf80349e8ab4793) to update your project

#### moved frontend api roles to framework bundle ([#4269](https://github.com/shopsys/shopsys/pull/4269))

- classes were moved from the frontend api package to the framework package
    - `Shopsys\FrontendApiBundle\Component\Security\FrontendApiRoleHierarchyProvider` -> `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleHierarchyProvider`
    - `Shopsys\FrontendApiBundle\Component\Security\FrontendApiRoleProvider` -> `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleProvider`

#### Translated Cypress on single domain ([#4272](https://github.com/shopsys/shopsys/pull/4272))

- flag data fixture: ensure UUIDs remain consistent between builds
- `BlogArticleDataFixture` no longer sets UUID for the main blog category as this actually had no effect
    - instead, `Shopsys\FrameworkBundle\Migrations\Version20240105155555` migration was modified to ensure the main blog category has a consistent UUID that can be relied on in tests
- removed white spaces in data fixtures
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/87f5639b587e243c899cfe82e97752872f3df715) to update your project

#### order creation: email preparation is now asynchronous ([#4266](https://github.com/shopsys/shopsys/pull/4266))

- the direct call to prepare the mail (`$this->orderMailFacade->sendEmail($order)`) was removed from `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade::placeOrder()` method
    - if you have extended the `placeOrder()` method, be sure to remove the call as well
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4ebe8cabfca1497615f10fa3440ac347ad7091f4) to update your project

#### Replace direct usage of DateTime and DateTimeImmutable with Symfony/Clock ([#4297](https://github.com/shopsys/shopsys/pull/4297))

- see this [`Working with date-time values docs`](https://docs.shopsys.com/en/18.0/introduction/working-with-date-time-values/) on how to work with date-time values and update your project appropriately
- for entities
    - change `\DateTime` type to `\DateTimeImmutable` and also Doctrine type from `datetime` to `datetime_immutable`
    - replace usage of `new \DateTime` and `new \DateTimeImmutable` with `new \Symfony\Component\Clock\DatePoint`
- for services
    - autowire `\Psr\Clock\ClockInterface` and use `$this->clock->now()` for getting new `DatePoint`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1a13b363a4e583b0176818f953935cd3c48d2e79) to update your project

#### Update admin styles ([#4298](https://github.com/shopsys/shopsys/pull/4298))

- updated responsive layout for admin interface including 2FA, forms, grids, and mobile navigation
- improved UI with fixed alignments, margins, and mobile experience
- removed tom-select custom styles and enhanced image upload feedback

#### removed workaround for empty base_url ([#4308](https://github.com/shopsys/shopsys/pull/4308))

- check your custom code for usage of `CDN_DOMAIN` environment variable and verify you don't rely on the `//` as a value
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/aa86eb0f62de6096df06c11e0d3dd383caf52486) to update your project

#### Fixed GrapesJS editor content corruption ([#4313](https://github.com/shopsys/shopsys/pull/4313))

- fixed `EnsureCorrectGrapesJsFormatHelper` to properly detect existing GrapesJS components and prevent wrapping already structured content into nested `gjs-text-ckeditor` divs
- this resolves the issue where adding an image before text in articles/blog posts caused the editor to break after saving and reopening

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/25d516519765452e34de5bd46a1c5fcdab37997d) to update your project

#### add to data fixtures promo code with a 10% discount on everything ([#4318](https://github.com/shopsys/shopsys/pull/4318))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fcf79429dc63bd91f3f1a818c0ab767dc76a4855) to update your project

#### fix build with load_demo_data: false set for some domain ([#4296](https://github.com/shopsys/shopsys/pull/4296))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8c3937ed335c8e21c7fbbbce18574711cf960d38) to update your project

#### Replace jQueryUI with SortableJS ([#4326](https://github.com/shopsys/shopsys/pull/4326))

- jQuery UI has been completely removed
- SortableJS is now used for the category tree in both Categories and Blog Categories
- if you're using jquery-ui-nested-sortable or jQueryUI in your codebase, consider replacing or leave jQueryUI installed
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a816bde9fcebd7b4ffdeb48b45a1cdbcc0c91245) to update your project

#### Replace `mutagen-compose` with plain `mutagen` because `mutagen-compose` is no longer compatible with the latest Docker API ([#4331](https://github.com/shopsys/shopsys/pull/4331)) and ([#4336](https://github.com/shopsys/shopsys/pull/4336))

##### If you were using mutagen-compose, follow these steps to migrate:

1. Stop and clean up your current environment:

    ```bash
    mutagen-compose down
    docker system prune -a
    ```

2. Ensure you are running the latest version of Mutagen

    ```bash
    brew upgrade mutagen
    ```

3. Run the installation script to set up the new environment:
    ```bash
    ./project-base/scripts/install.sh
    ```

##### New Make targets for macOS with Mutagen:

| Target                           | Description                                                            |
| -------------------------------- | ---------------------------------------------------------------------- |
| `make mutagen-up`                | Starts Docker environment with Mutagen sync                            |
| `make mutagen-up-build`          | Starts environment and rebuilds images                                 |
| `make mutagen-up-build-no-cache` | Starts environment and rebuilds images without cache                   |
| `make mutagen-stop`              | Stops containers while preserving them (useful for switching projects) |
| `make mutagen-down`              | Removes containers and stops Mutagen sync                              |

##### Using Docker Compose directly:

For commands not covered by Make targets (e.g., `exec`, `logs`, `restart`), use plain `docker compose` like `docker compose exec php-fpm bash`

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/54b571f6be09c5ccd3e6ccdf0491e0281cbf157d) and [project-base diff](https://www.github.com/shopsys/project-base/commit/84b7d63d58ce369a986db68a4eba674c0d9136e8) to update your project

#### Products temporarily out of stock now show proper info message ([#4329](https://github.com/shopsys/shopsys/pull/4329))

- new GraphQL field `isCurrentlyOutOfStock: Boolean!` added to Product interface and all implementing types
    - returns `true` when product stock is unavailable (and it's not possible to buy over stock) but selling is not permanently denied
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/521314a313a79638df1291719541023bd4f79ea4) to update your project

#### improved withdrawal deadline calculation ([#4317](https://github.com/shopsys/shopsys/pull/4317))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f9ee7aed6df788a96788b43f44ba3f3191d543e7) to update your project

#### Added routeName support to SliderItem for skeleton loader selection ([#4337](https://github.com/shopsys/shopsys/pull/4337))

- added `route_name` column to `slider_items` table and `routeName` property to `SliderItem` entity
- added automatic route name resolution in `SliderItemFacade` and `routeName` field to GraphQL API
- changed `SliderItemFormType` link field from `UrlType` to `TextType` to allow relative paths
- extracted URL normalization logic to reusable `UrlNormalizer` helper class
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/39ed99bd70321a3f760d09863fb72a900c6271fe) to update your project

### Storefront

#### Translation system: accessibility namespace via `ns` option ([#4153](https://github.com/shopsys/shopsys/pull/4153))

- storefront translation system now supports multiple namespaces using i18next-parser v9.3.0+ native namespace routing
- **BREAKING CHANGE**: `aria*` translations are moved from `common.json` to the dedicated `accessibility.json` namespace.
- added custom `useTranslate` hook for the Storefront as a thin, typed wrapper around our translation system
    - returns the `t` function directly for ergonomic usage and consistent key extraction
    - works with multiple namespaces; continue using the `ns` option for the accessibility strings
- i18next loads `common` and `accessibility` namespaces by default. Use the `ns` option
- existing non-accessibility strings in `common.json` remain unchanged
- **Action required**: Update your translation usage from:

```tsx
// old import of useTranslation directly from the next-translate
import useTranslation from 'next-translate/useTranslation';

const Component = () => {
    const { t } = useTranslation();

    // This goes into default 'common' namespace (common.json file)
    return <button aria-label={t('Go to cart page')} />;
};
```

```tsx
// new import of the useTranslation wrapper
import useTranslation from 'utils/i18n/useTranslationWrapper';

const Component = () => {
    const t = useTranslate();

    // New usage (use i18next namespace option)
    return <button aria-label={t('Go to cart page', { ns: 'accessibility' })} />;
};
```

> Important: DB overrides for moved accessibility strings

- If your project has customized translations stored in the database for strings that are now called with `ns: 'accessibility'` (ARIA/screen‑reader labels), those overrides still reside under the `common` namespace after upgrade and will not apply to the new namespace.
- Action required: Migrate these overrides to the `accessibility` namespace so the storefront reads them correctly.
    - Find moved keys by searching code for `ns: 'accessibility'` (e.g., on host: `rg -n "t\('([^']+)',\s*\{[^}]*ns:\s*'accessibility'" project-base/storefront`).
    - Re‑create the overrides via Admin > Content > Language constants in the `accessibility` namespace, or update the DB rows by changing `language_constants.namespace` from `common` to `accessibility` for those keys (ensure no duplicate `key+namespace` rows; merge/copy translations if needed).
    - Regenerate translation files for your locales after updates.

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9b7f400ee68569592fb1950c282755708f3d42b9) to update your project

#### Add multiselect component ([#4152](https://github.com/shopsys/shopsys/pull/4152))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/00d9628f9319fd636a164d8e12add83f4f8782fe) to update your project

#### Update styleguide ([#4154](https://github.com/shopsys/shopsys/pull/4154))

- fixed styleguide colors based on design system
- added script `make generate-icons-for-styleguide` for generating all icons to one file
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/35d061bcc8a4ee0fbef028370fec271e7ea591a0) to update your project

#### Accessibility for banner slider ([#4158](https://github.com/shopsys/shopsys/pull/4158))

- when changing banners using navigation buttons, the focus will automatically move to the current banner after the transition
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/28fd0720d0dd0a57bab17b0b12be58de98273184) to update your project

#### Accessibility flash message and popup ([#4162](https://github.com/shopsys/shopsys/pull/4162))

- added logic for focus restoration after showing flash message or popup for voiceover
- polished translations for better understanding with voiceover
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/de1fe068760a8042c74d679281e097985e93a43f) to update your project

#### fix announcing page title for screen readers ([#4166](https://github.com/shopsys/shopsys/pull/4166))

- re-enabled `RouteAccessibilityManager` + `RouteAnnouncer` and introduced a localized “Page loading” announcement so VoiceOver no longer speaks the stale title (sometimes)
- `<RouteAnnouncer>` now waits for SeoMeta to finish, announces the loading state, then voices the final page title once; keep the new translations (`Page loading`) in sync across locales
- added `vitest/components/Layout/RouteAnnouncer.test.tsx` to lock in the behaviour
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3ee0e0679d2c15ec73878a800340b3d2b6b25153) to update your project

#### Fix pagination scroll ([#4169](https://github.com/shopsys/shopsys/pull/4169))

- fixed pagination scroll issue where page changes caused data fetching that triggered skeleton loading states outside of the pagination reference element, preventing the scroll from finding the anchor element
- added pagination to complaints
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/457ff2dc10bc4824e44d43fe4835aaea2fea099f) to update your project

#### Fix GTM newsletter ([#4171](https://github.com/shopsys/shopsys/pull/4171))

- GTM newsletter information is now correctly included in the user data
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b1efb07a308d0549fad8095f822a7042e09143d1) to update your project

#### prevent exceeding available stock for products with negative stock disabled ([#4173](https://github.com/shopsys/shopsys/pull/4173))

- re-introduced `cartItemsWithChangedQuantity` cart item modification to be able to track changes in product quantities with negative stock disabled
- the maximum value in spinbox is now limited by available stock quantity for products with negative stock disabled
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3620b8199d29aa7afc891bcad06c5d36bc6dbcd7) to update your project

#### Transport and payment single-option ([#4177](https://github.com/shopsys/shopsys/pull/4177))

- removed unnecessary reset button for transport and payment single-option
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/16724caae5fa31506da5a0e86c11706d96558a30) to update your project

#### Extended validation rules ([#4181](https://github.com/shopsys/shopsys/pull/4181))

- updated validation for street to require proper format, telephone to allow + only at beginning for country code, and fixed `validateCity` function to use correct length constant
- See [project-base diff](https://github.com/shopsys/project-base/commit/441e39ee5) to update your project

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/441e39ee567217aebee27319ba09048116db5136) to update your project

#### Add special article page (about) GTM type ([#4184](https://github.com/shopsys/shopsys/pull/4184))

- added new `about` GTM page type for special article tracking instead of generic `article_detail`
- added `SPECIAL_ARTICLE_GTM_TYPES` configuration object in `gtm/types/objects.ts` mapping article slugs to GTM types
    - configured default mappings: `/about-us` (EN) and `/o-nas` (CS) → `GtmPageType.about`
    - configuration is immutable (Object.freeze) to prevent accidental modifications
- added `getSpecialArticleGtmType()` utility function
- modified `getGtmPageInfoTypeForFriendlyUrl()` to check article slugs against configuration before falling back to default `article_detail` type
- added comprehensive test coverage and documentation for administrators and developers

**⚠️ CRITICAL: Project developers must update `SPECIAL_ARTICLE_GTM_TYPES` configuration when administrators change article slugs in the admin interface to maintain correct GTM tracking. The configuration maps exact slug matches to GTM page types.**

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/23236ffb960e64e221ff966ac17abf3b4f0843bd) to update your project

#### Fix loading overlay position in cart ([#4155](https://github.com/shopsys/shopsys/pull/4155))

- the loading overlay now correctly covers the cart content during add/remove actions
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a6120db0f21420ba44da58d9aada39a85234f8db) to update your project

#### Fix customer orders/complaints pagination scroll ([#4189](https://github.com/shopsys/shopsys/pull/4189))

- pagination scroll destination is right above heading in Customer Layout for Complaints and Orders
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/85986704d1dba13300822ee6369a113903e76dfe) to update your project

#### Update homepage banner LCP ([#3953](https://github.com/shopsys/shopsys/pull/3953))

- improved homepage banner's LCP by separating desktop and mobile versions and setting priority for the first image
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3e184d3cdc36c4c0e3b82cea9bf7f9c37005359a) to update your project

#### GrapesJS Safari Google map ([#4198](https://github.com/shopsys/shopsys/pull/4198))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0108000686eed52391ce25f5f36be8aa503f1557) to update your project

#### enable domain configuration with path fragment ([#4113](https://github.com/shopsys/shopsys/pull/4113))

This feature allows configuring domains with locale paths (e.g., `/sk/`, `/cs/`) for multi-language support.

**If your project doesn't need domains with locale paths or already has a custom implementation, you can skip these changes.**

- **`getDomainConfig()` function signature changed**
    - before: `getDomainConfig(domainUrl: string): DomainConfigType`
    - after: `getDomainConfig(context: GetServerSidePropsContext | NextPageContext): DomainConfigType`
    - the function now requires server-side context with `req.headers.host` instead of a URL string
- **`createClient()` function signature changed**
    - before: accepts `publicGraphqlEndpoint: string` parameter
    - after: accepts `domainConfig: DomainConfigType` parameter instead
- **`middleware.ts` changes**
    - `getHostAndDomainFromRequest()` replaces `getDomainIdFromHostname()`
    - the new function returns an object with `{ host, domainId, currentLocale, redirect? }` instead of just domain ID
- **Cookie and store persistence naming**
    - cookies and persist stores now include domain ID in their names for proper domain isolation
    - see `utils/cookies/cookieNaming.ts` and `components/providers/PersistStoreProvider.tsx` for references
- see [the docs](https://docs.shopsys.com/en/18.0/storefront/domain-configuration) for the comprehensive information
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/be5b265cd9a2988a64be8d12a2c26056979ad2f9) to update your project
- see also [project-base diff](https://github.com/shopsys/project-base/commit/160cc7c59) of [#4265](https://github.com/shopsys/shopsys/pull/4265) with additional fix

#### Product gift ([#4193](https://github.com/shopsys/shopsys/pull/4193))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d039c7996315509402ed5f9bf7110fc513bb4cd2) to update your project

#### Promotion X + Y free ([#4194](https://github.com/shopsys/shopsys/pull/4194))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6c93f69d80efb9749a9dbee225e60a35ea19a29e) to update your project

#### Filled email in order process ([#4199](https://github.com/shopsys/shopsys/pull/4199))

- this update fixes an issue where, during the order process, if a public user entered an email, then navigated to the homepage and logged in with a different email, the previously entered email would still appear in the disabled email input under contact information
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/587856d1db7c68f9e4bfd1ef8177645a2bb00d56) to update your project

#### Fix missing access token ([#4203](https://github.com/shopsys/shopsys/pull/4203))

- fix server-side authentication race condition by validating customer authentication before executing order queries during SSR, preventing 'You need to be logged in or provide argument urlHash' critical errors when tokens are invalid or incomplete
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/66d96823d8a79bac7b861b11f84ef0ca9390ac95) to update your project

#### WYSIWYG styles ([#4208](https://github.com/shopsys/shopsys/pull/4208))

- added default styling for CK Editor for better administrator expecience
- removed `/tailwind-for-admin/style.css` from `gitignore`
- added CI check for Tailwind for admin styles
- added generating commant to storefront setup
- fixed link size in `user-text`
- updated styles for layout component in CK Editor
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d08639c663e9376914ef207870b0100b88af578d) to update your project

#### Incorrect identifier ([#4210](https://github.com/shopsys/shopsys/pull/4210))

- fixed an incorrect recommended products identifier during SSR on the product detail page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a3c0e9ac34eeeeb80dc00ae3154a6cea0c8d41da) to update your project

#### Update cart in other tabs after submition ([#4211](https://github.com/shopsys/shopsys/pull/4211))

- added a broadcast channel after completing an order to update the cart in other tabs
- added a broadcast channel to handle the repeat button functionality
- enhanced the broadcast channel logic to properly respect registered callbacks
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e33d736aecf47f5cca69664820f58b47436111a6) to update your project

#### Order contact email validation and submit button state ([#4202](https://github.com/shopsys/shopsys/pull/4202))

- standardized disabled-button logic, see `/styleguide` for extended usage examples
- fixed & stabilized Cypress tests (helper, hydration waits, data corrections)
- cleaned up deprecated React Hook Form usages of `fieldState: { invalid }` to `fieldState: { error }`
- validates persisted contact email on load so errors and form validity reflect reality after refresh;
  submit button remains disabled until the email is valid
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/63168b95688f8c551284e7d8f68a8ea942f6e010) to update your project

#### Add to cart EAA ([#4212](https://github.com/shopsys/shopsys/pull/4212))

- improved add to cart accessibility for users with limited price visibility. The aria label now correctly omits the price when unavailable, preventing "NaN" or "undefined" from being read aloud
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a742809e49e19a0362f197d533fb407c6eafddff) to update your project

#### Add product images to order list ([#4213](https://github.com/shopsys/shopsys/pull/4213))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d9d99374216422d6dc2b104f9cecdb00aafd593e) to update your project

#### Autocomplete favorites and improved UX ([#4215](https://github.com/shopsys/shopsys/pull/4215))

- there is a new feature in the framework package that enables setting favorite products, categories, and brands in the admin interface
- the values are available via new `AutocompleteFavoritesQuery` and presented to the user once the autocomplete input is focused
- also, the BE now supports searching for shorter terms (1–2 characters) so `MINIMAL_SEARCH_QUERY_LENGTH` constant value was lowered from 3 to 1
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1d5581d6680e2dd3f6df281e582dd22da47547ce) to update your project

#### Remove masking url on router replace ([#4214](https://github.com/shopsys/shopsys/pull/4214))

- replace url with the current pathname & query params in the `GoPayGateway` to allow native browser backwards navigation
- on native browser back navigation, show `Repeat payment` button and wait for user interaction to delay GoPay gateway trigger
- more robust GoPay checkout trigger with improved error handling & order-confirmation page expiration warning instead of empty page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/abb61702c604b1c4334a50a33c91c9e0f9f2dd27) to update your project

#### Improve cypress gql error handling ([#4220](https://github.com/shopsys/shopsys/pull/4220))

- **New Cypress command `checkGQL<T>(operationName: string)` added** for GraphQL error handling
    - use this command to validate GraphQL responses and get detailed error messages
    - replaces the pattern `.its('body.data.X')` with `.checkGQL('OperationName').its('X')`
- if you have custom Cypress test helpers that directly access GraphQL responses, update them to use the new `checkGQL()` command for better error reporting
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/06d638f1100a690d5d37082e31bdae044e3e15e7) to update your project

#### Redesign contact informations ([#4221](https://github.com/shopsys/shopsys/pull/4221))

- updated the design and appearance of contact information in the order process. Also updated and unified the handling of delivery addresses across contact information, profile editing in the customer section, and the complaint creation popup
- added default shopsys favicon
- added new component `IconButton`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d1a6ef2f41e20bd9533185d7cbc001dd2d990f1e) to update your project

#### Autocomplete search input accessibility ([#4230](https://github.com/shopsys/shopsys/pull/4230))

- improved accessibility and visual design of the autocomplete search popup. Refined accessibility navigation logic to prevent displaying the container ID in the URL
- fixed Safari accessibility issue with the skip link to the search input
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b3c3e0c6debc84a9d1c23b21a31a351e6dfa5f7a) to update your project

#### graphql queries are sent exclusively by POST ([#4236](https://github.com/shopsys/shopsys/pull/4236))

- the storefront urql client now explicitly uses POST method via `preferGetMethod: false` configuration
    - this disables the automatic GET optimization introduced in urql v5.1.0
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/28bbf7851e1ea5b64e63408ca5fc18c8abf5127d) to update your project

#### Make middleware more readable ([#4242](https://github.com/shopsys/shopsys/pull/4242))

- `middleware.ts` is refactored and more readable now
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/434b2cfe5d7396a00881a3346ae1d77d83df51ad) to update your project

#### Cart price calculation and UX improvements ([#4314](https://github.com/shopsys/shopsys/pull/4314))

- updated to properly display price before discount, total savings on products in sale, and overall total savings
- added `CartStickyBar` component that appears when users have more than 4 products in cart
    - displays total price (with/without VAT), free delivery information and "Continue with order" button for quick checkout access
    - auto-hides using `IntersectionObserver` when scrolling to original button
    - Configurable threshold via `MIN_ITEMS_FOR_STICKY_BAR` constant (default: 4)
- in second and third step - Product list limited to 3 visible items with scroll and gradient overlay
- in third step right panel (order summary) is now sticky in step 3 for better overview
- reduced scrolling for users with larger carts
- better visibility of pricing and savings
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1ae0d9abf35746211db63ff63c260f5a500fbe71) to update your project

#### SF: getBasePathWithLocale now requires context instead of locale ([#4249](https://github.com/shopsys/shopsys/pull/4249))

- **`getBasePathWithLocale()` function signature changed**
    - update all calls to pass the full `context` object instead of just the locale string
- **`handleServerSideErrorResponseForFriendlyUrls()` function signature changed**
    - pass the full `context` object instead of separate `res` and `contextLocale` parameters (the `contextLocale` parameter was removed entirely)
- **`getLoginUrlWithRedirect()` function signature changed**
    - pass the `context` object as the third parameter instead of `contextLocale` string
- **`getUnauthenticatedRedirectSSR()` function signature changed**
    - pass the `context` object as the third parameter instead of `contextLocale` string
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/43d96b1464a74a36cfc1edd67b1ec81b2d78830a) to update your project

#### Fix styles issues ([#4253](https://github.com/shopsys/shopsys/pull/4253))

- fixed content alignment in the newsletter form above the footer
- fixed image size in the blog preview on the homepage
- fixed toast style variables
- added a new warning icon for toasts
- updated close icon colors
- removed unused code
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/21d91f5c353279110f3503352abc9d16c4dcf5d9) to update your project

#### Updated Reset password page ([#4254](https://github.com/shopsys/shopsys/pull/4254))

- improved the password reset flow to include automatic redirects and user-friendly confirmation messages
- updated the info page shown after submitting a password reset request
- created a new `PageHero` component and added it to several pages across the storefront, and updated skeletons accordingly
- refactored form error handling
    - removed the error popup from most forms
    - new behavior: forms now scroll to the first error field instead of showing a popup
    - the error popup is now only used for the contact form submission process
    - e.g., the registration form now scrolls to the email field with an inline error "email is already registered" instead of showing a popup
- fixed the form clearing function that runs after a successful submission
- unified the lock icons (clear, check, cross) for consistent visual design
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/42da89c46fa2fca4f0511beb226eec592f911f71) to update your project

#### Fixed automatic token refresh after token expiration ([#4258](https://github.com/shopsys/shopsys/pull/4258))

- when the access token expires, it will automatically be refreshed using the refresh token stored in cookies
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/42c4182823c7f47b5a0fa0b1a7a8e0e0ef380ef7) to update your project

#### Customer now has properly resolved roles no matter the current context ([#4262](https://github.com/shopsys/shopsys/pull/4262))

- GraphQL enum `CustomerUserRoleEnum` now includes `ROLE_API_MANAGE_CUSTOMERS` value
- GraphQL enum `CustomerUserRoleEnum` now includes `ROLE_API_MANAGE_COMPANY_DATA` value
- update your custom authorization checks that use `TypeCustomerUserRoleEnum.RoleApiAll` for managing customer users to use `TypeCustomerUserRoleEnum.RoleApiManageCustomers` or `TypeCustomerUserRoleEnum.RoleApiManageCompanyData` instead
- example change in `AuthorizationProvider.tsx`:

    ```typescript
    // Before:
    const canManageUsers = isCompanyUser && customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiAll);

    // After:
    const canManageUsers = isCompanyUser && customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiManageCustomers);
    ```

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/67a451cab68f97e56f327b8b7154c8c1343bb2be) to update your project

#### order withdrawal from contract ([#4246](https://github.com/shopsys/shopsys/pull/4246))

- Frontend API: new GraphQL fields added to `Order` type:
    - `deliveredAt: DateTime` - date when order was delivered to customer
    - `withdrawalRequest: OrderWithdrawalRequest` - withdrawal request information
    - `withdrawalInstructions: String!` - withdrawal instructions for the order
    - `canRequestWithdrawal: Boolean!` - indicates if withdrawal can be requested
    - `withdrawalDeadline: DateTime` - withdrawal deadline date
- On the order detail page, customers can now see a new "Withdrawal from contract" section where they can:
    - request the withdrawal (using new `OrderWithdrawalRequest` mutation),
    - view their withdrawal data,
    - or information why it is not possible to request the withdrawal.
- Frontend API: new GraphQL type `OrderWithdrawalRequest` with customer contact details and request timestamp
- There are two new pages with their own skeleton loaders:
    - `order-withdrawal/[orderUrlHash]` - page with a form to request withdrawal from contract
    - `order-withdrawal-sucess/[orderUrlHash]` - confirmation page displayed after successful withdrawal request
- `PageGuard` now displays a flash error message when access to the guarded page is denied
- `validateTelephone` was adjusted to allow empty strings (to support optional telephone fields)
- `deliveryTelephone` field in the order contact information delivery data form properly marked as optional
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b7b8706f5f9abb4903b08861ecf80349e8ab4793) to update your project

#### Z-index issue ([#4273](https://github.com/shopsys/shopsys/pull/4273))

- the animate-in animation on <main> creates a CSS stacking context that breaks modal z-index, fixed by rendering ModalGallery via portal at document root
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0f9adeaab641e4d1e08348568032b6514a95ba68) to update your project

#### Translated Cypress on single domain ([#4272](https://github.com/shopsys/shopsys/pull/4272))

- tests now support multiple locales (cs, en, sk) instead of hardcoded text
- `cypress/support/translations.ts` - loads translations from `public/locales/{locale}/common.json` and `.po` files
- `cypress/fixtures/translationKeys.ts` - centralized translation keys for common UI elements (buttons, placeholders, toasts, etc.)
- `cypress/support/navigation.ts` - entity navigation helpers that work with localized URLs
- added `TEST_LOCALE` environment variable to specify testing locale
- added task to dynamically load `.po` translation files
- replaced hardcoded text with `translations.*` references (example: `translations.toast.success.loggedIn` instead of `'Successfully logged in'`)
- tests can now run on any locale by setting `TEST_LOCALE` environment variable
- makes tests more maintainable and easier to adapt for different languages
- to setup tests for single domain correctly you need to change
    - in `gitlab.ci.yml` value `TEST_LOCALE`
    - in `docker-build.yaml` value `TEST_LOCALE`
    - in `docker-compose.github-actions.cypress.yml` value `TEST_LOCALE`
        - in `domains.yaml` value `locale` and keep single domain
        - in `Makefile` value `TEST_LOCALE`
        - in `next.config.js` value `defaultLocale`
    - in `parameters_common.yaml` set `locale` and correct langs order in `shopsys.allowed_admin_locales`
    - in `routes.ts` keep single domain
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/87f5639b587e243c899cfe82e97752872f3df715) to update your project

#### Fixed robots.txt for domains with locale-only configuration ([#4302](https://github.com/shopsys/shopsys/pull/4302))

- fixed `robots.txt` returning an error when all domains on a host have locale suffixes (e.g., `example.com/cs`, `example.com/sk`) with no pure host domain configured
- example on domains `example.com/cs` and `example.com/sk`:
    - `example.com/robots.txt` - before: "Domain not configured" error, now: returns robots.txt
    - `example.com/cs/robots.txt` - returns 404 (by design, robots.txt must be in root)
- added fallback domain resolution in `getDomainConfig` for requests to root host when only locale-suffixed domains exist
- the original 404 condition checked domain configuration (`getLocalePrefix(domainConfig)`), which always returned locale suffix for fallback domains - this worked only when a pure host domain existed
- changed the condition to check the actual request URL (`context.locale`) - now it correctly distinguishes between `example.com/robots.txt` (allowed) and `example.com/cs/robots.txt` (404)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/65105c68d87c24e409863ffc06e2ab938c40bbe3) to update your project

#### Handle success state after form submission ([#4303](https://github.com/shopsys/shopsys/pull/4303))

- improved handling of the form submission success state for Contact, Personal Data Export, and Personal Data Overview
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/343ac497eca31b987fb852913bb680c9b0f3a964) to update your project

#### Report hidden products ([#4304](https://github.com/shopsys/shopsys/pull/4304))

- added ability to report hidden products
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5f3f83e02a30991ee2d06471bdfa0d1905a5df2f) to update your project

#### Delivery address pre-selection fix ([#4316](https://github.com/shopsys/shopsys/pull/4316))

- fixed delivery address pre-selection in order contact information to correctly select user's default address (or first address as fallback)
- user can now change selected delivery address without it resetting back to default
- first created delivery address is now automatically set as default only when user has exactly one address
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8e702a6b1020955dda6341d0a704d69873059dee) to update your project

#### fix operationNameExchange to properly preserve existingFetchOptions ([#4306](https://github.com/shopsys/shopsys/pull/4306))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/aa25af41d8353c2d46f9f32eb8cd0d0c8a62183b) to update your project

#### Tag onClick ([#4322](https://github.com/shopsys/shopsys/pull/4322))

- added missing `onClick` on `Tag` components for GTM events
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f740f00e67958f5396a2ee656e3c5ed0c8a72147) to update your project

#### removed fragment-matcher which is an Apollo-only concept ([#4323](https://github.com/shopsys/shopsys/pull/4323))

- removed `fragment-matcher`, which is an Apollo-only concept
- removed unused code, reducing file sizes and improving readability
- possibleTypes are already included in the compressed schema, we don't need to define them in each file, which was causing duplicated code
- it's safe to expose the GraphQL schema, as it only describes types and is commonly public in GraphQL APIs without creating security risks
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fc80d9b3065b3094ef9e4f970d8972f1c4043c10) to update your project

#### Fix oversize product detail page ([#4324](https://github.com/shopsys/shopsys/pull/4324))

- removed unnecessary data from queries in the product/variant detail
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ed709c15289a25f486f245c0ee6849306e0d767d) to update your project

#### Cart sync ([#4327](https://github.com/shopsys/shopsys/pull/4327))

- defer cart refetch for background tabs until they become visible, reducing unnecessary API calls when multiple tabs are open
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cd03297960d1ade6e8c84c7815b0323342bf00ae) to update your project

#### Add confirmation popup ([#4328](https://github.com/shopsys/shopsys/pull/4328))

- added confirmation popup for "Remove all" action on wishlist and product comparison pages to prevent accidental deletion
- polished action buttons in product comparison
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6b0996c4c541c2d3797a9c40d0ade0d254490556) to update your project

#### Products temporarily out of stock now show proper info message ([#4329](https://github.com/shopsys/shopsys/pull/4329))

- `WatchDogButton` component now accepts optional `buttonSize` prop with type `'small' | 'medium' | 'large' | 'xlarge'`
    - text "Watch the goods" only displays when `buttonSize` is `'large'`
    - if you use this component, review your usages to ensure correct button sizing
- `ProductAction` component now shows WatchDog button for products where `isCurrentlyOutOfStock` is `true`
- `ProductDetailAddToCart` component displays specific info message for temporarily out-of-stock products
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/521314a313a79638df1291719541023bd4f79ea4) to update your project

#### GitLab Cypress pipeline ([#4333](https://github.com/shopsys/shopsys/pull/4333))

- added missing TEST_LOCALE for GitLab pipeline
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/997dcd3451e87bc46ffb7305700b1f97178c0471) to update your project

#### Added routeName support to SliderItem for skeleton loader selection ([#4337](https://github.com/shopsys/shopsys/pull/4337))

- added `routeName` field to `SliderItemFragment` and updated `BannersSlider` to use it for skeleton type selection
- extracted `getSkeletonTypeFromLink` helper function to shared utils
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/39ed99bd70321a3f760d09863fb72a900c6271fe) to update your project

#### Extra word in flash message ([#4340](https://github.com/shopsys/shopsys/pull/4340))

- flash message is now correct without any additional words
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/01b136a75687555c58e8457c26aba769558fcda9) to update your project

#### Improved 404 handling for deleted categories ([#4344](https://github.com/shopsys/shopsys/pull/4344))

- the application now correctly displays a 404 page when navigating to a deleted article, blog article, blog category or product category via client-side navigation (clicking a link)
- previously, client-side navigation to a deleted item showed an empty page with console errors
- added `category-not-found` to suppressed errors to prevent flash messages
- updated several application errors to suppress flash error messages and instead display the correct 404 page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d4f723c6224038776b80834608ddef2b1be63dc6) to update your project

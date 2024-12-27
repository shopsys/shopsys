# UPGRADING FROM 15.x to 16.0

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
    - update the `@shopsys/framework` package in your `package.json` (in "dependencies" section) to the version you are upgrading to
        - eg. `"@shopsys/framework": "9.0.4",`
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

## [Upgrade from v15.0.0 to v16.0.0](https://github.com/shopsys/shopsys/compare/v15.0.0...v16.0.0)

#### allow administrator to limit the managed domains ([#3289](https://github.com/shopsys/shopsys/pull/3289))

- interface `Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface` contains a new method `getAdminEnabledDomainIds()`
- method `Shopsys\FrameworkBundle\Component\Domain\Domain::__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly array $domainConfigs,
            protected readonly Setting $setting,
    +       protected readonly AdministratorFacade $administratorFacade,
    ```
- method `Shopsys\FrameworkBundle\Component\Domain\DomainFactory::__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly DomainsConfigLoader $domainsConfigLoader,
            protected readonly Setting $setting,
    +       protected readonly AdministratorFacade $administratorFacade,
    ```
- method `Shopsys\FrameworkBundle\Component\Domain\DomainFactoryOverwritingDomainUrl::__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly DomainsConfigLoader $domainsConfigLoader,
            protected readonly Setting $setting,
    +       protected readonly AdministratorFacade $administratorFacade,
    ```
- method `Shopsys\FrameworkBundle\Controller\Admin\CountryController::__construct()` changed its interface:
    ```diff
        public function __construct(
            // ...
    +       protected readonly Domain $domain,
    ```
- method `Shopsys\FrameworkBundle\Controller\Admin\CustomerUserRoleGroupController::__construct()` changed its interface:
    ```diff
        public function __construct(
            // ...
    +       protected readonly Domain $domain,
    ```
- interface `Shopsys\FrameworkBundle\Model\Article\ArticleDataFactoryInterface` was removed, use `Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory` instead
- method `Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory::__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly FriendlyUrlFacade $friendlyUrlFacade,
            protected readonly Domain $domain,
    -       protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ```
- method `Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory::create()` changed its interface:
    ```diff
        public function create(
    +       int $domainId,
    ```
- method `Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory::fillNew()` changed its interface:
    ```diff
        protected function fillNew(
            ArticleData $articleData,
    +       int $domainId,
    ```
- method `Shopsys\FrameworkBundle\Model\Complaint\Status\Grid\ComplaintStatusInlineEdit::__construct()` changed its interface:
    ```diff
        public function __construct(
            // ...
    +       protected readonly Domain $domain,
    ```
- method `Shopsys\FrameworkBundle\Model\Order\Status\Grid\OrderStatusInlineEdit::__construct()` changed its interface:
    ```diff
        public function __construct(
            // ...
    +       protected readonly Domain $domain,
    ```
- `Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade`
    - method `__construct()` changed its interface
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly PricingGroupRepository $pricingGroupRepository,
    -       protected readonly Domain $domain,
            protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
            protected readonly ProductVisibilityFacade $productVisibilityFacade,
            protected readonly CustomerUserRepository $customerUserRepository,
            protected readonly PricingGroupFactoryInterface $pricingGroupFactory,
            protected readonly EventDispatcherInterface $eventDispatcher,
    ```
    - method `delete()` changed its interface
    ```diff
        public function delete(
    -       $oldPricingGroupId,
    +       int $oldPricingGroupId,
    -       $newPricingGroupId = null,
    +       ?int $newPricingGroupId = null,
    +       ?DomainConfig $selectedDomain = null,
        ): void {
    ```
    - method `getAllIndexedByDomainId()` was removed
- `Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade`
    - method `__construct()` changed its interface
    ```diff
        public function __construct(
            protected readonly PricingGroupRepository $pricingGroupRepository,
            protected readonly Domain $domain,
    -       protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
            protected readonly Setting $setting,
    ```
    - method `isPricingGroupUsedOnSelectedDomain()` was removed, use `isPricingGroupUsedOnDomain()` instead
    - method `getDefaultPricingGroupByCurrentDomain()` is now strictly typed
    - method `getDefaultPricingGroupBySelectedDomain()` was removed, use `getDefaultPricingGroupByDomain()` instead
    - method `setDefaultPricingGroupForSelectedDomain()` was removed, use `setDefaultPricingGroupForDomain()` instead
    - method `isPricingGroupDefaultOnSelectedDomain()` was removed, use `isPricingGroupDefaultOnDomain()` instead
- method `Shopsys\FrameworkBundle\Model\Product\Product\ProductFacade::__construct` changed its interface
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly ProductRepository $productRepository,
    -       protected readonly ProductVisibilityFacade $productVisibilityFacade,
            // ...
    ```
- method `Shopsys\FrameworkBundle\Model\Stock\StockSettingsDataFacade::__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly Setting $setting,
    -       protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
            protected readonly ProductRecalculationDispatcher
    ```
- method `Shopsys\FrameworkBundle\Model\Stock\StockSettingsDataFacade::edit()` changed its interface:
    ```diff
        public function edit(
            StockSettingsData $stockSettingsData,
    +       DomainConfig $domainConfig,
    ```
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/25b01d0031af43b5c5bc714815ec546533693cfe) to update your project

#### Remove deprecated title, caption, geo_location, license + lastmod from image sitemap ([#3400](https://github.com/shopsys/shopsys/pull/3400))

- `Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapItemImage` class was changed:
    - `$caption` property was removed
    - `$geoLocation` property was removed
    - `$title` property was removed
    - `$license` property was removed
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4437a303c28b4fbd42d279bb0df64f785fccdcff) to update your project

#### improve getting position for new image ([#3421](https://github.com/shopsys/shopsys/pull/3421))

- `Shopsys\FrameworkBundle\Component\Image\ImageRepository` class was changed:
    - `getImagesCountByEntityIndexedById()` method was renamed to `getNewImagePosition()`

#### Added complaints to personal data page ([#3433](https://github.com/shopsys/shopsys/pull/3433))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a5eab40fcd489c8ab21faa29355d2437544b1c99) to update your project

#### Added demo images for complaints ([#3434](https://github.com/shopsys/shopsys/pull/3434))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ce4b825) to update your project

#### transport restrictions ([#3397](https://github.com/shopsys/shopsys/pull/3397))

- `Shopsys\FrameworkBundle\Model\Transport\Grid\TransportGridFactory` class was changed:
    - method `getDisplayPrice()` was renamed to `getDisplayPrices()` and returns now an array of `Price` objects
- `Shopsys\FrameworkBundle\Model\Transport\Transport` class was changed:
    - `$maxWeight` property was removed, use `TransportPrice::$maxWeight` instead
    - `setPrice()`, `hasPriceForDomain()`, and `addPrice()` methods were removed, use `setPrices()` instead
    - `getPrice()`, and `getMaxWeight()` methods were removed, you can use `getPricesByDomainId()` or `TransportPriceFacade::getTransportPriceOnDomainByTransportAndClosestWeight()` instead
- `Shopsys\FrameworkBundle\Model\Transport\TransportData` class was changed:
    - `$pricesIndexedByDomainId`, `$vatsIndexedByDomainId`, and `$maxWeight` properties were removed, use `$inputPricesByDomain` instead
- `Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactoryInterface` was removed, use `Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactory` instead
- `Shopsys\FrameworkBundle\Model\Transport\TransportFacade` class was changed:
    - `getPricesIndexedByDomainId()` method was removed, `getPricesIndexedByTransportPriceId()` can be used to get all the transport prices
- `Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation` class was changed:
    - `getCalculatedPricesIndexedByTransportId()` method was removed without replacement
- `Shopsys\FrontendApiBundle\Model\Cart\TransportAndPaymentWatcherFacade` class was changed:
    - `checkTransportPrice()` method was renamed to `checkTransportPriceAndWeightLimit()`
    - `checkTransportWeightLimit()` method was removed, use `checkTransportPriceAndWeightLimit()` instead
- `Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade` class was changed:
    - `checkTransportPrice()` method was renamed to `checkTransportPriceAndWeightLimit()`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3f9e627d2212c8be2de2d3b70debeb4c7134fc4a) to update your project

#### homepage redesign ([#3446](https://github.com/shopsys/shopsys/pull/3446))

- class `Shopsys\FrontendApiBundle\Model\Resolver\Settings\MainBlogCategoryUrlQuery`:
    - class was renamed to `MainBlogCategoryDataQuery`
    - constructor changed its interface
    ```diff
        public function __construct(
            protected readonly BlogCategoryFacade $blogCategoryFacade,
            protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    +       protected readonly ImagesQuery $imagesQuery,
    ```
    - method `mainBlogCategoryUrlQuery()` was removed, use `mainBlogCategoryDataQuery()` instead
- graphql query `Settings` has now removed the `mainBlogCategoryUrl` field, use `mainBlogCategoryData` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5b1fc3409539a00a562517a7e9a41ed40d9c69cb) to update your project

#### propagate changes in banner sliders immediately ([#3451](https://github.com/shopsys/shopsys/pull/3451))

- class `Shopsys\FrameworkBundle\Model\Navigation\NavigationSaveOrderingListener` was removed, use general `Shopsys\FrameworkBundle\Component\Grid\CleanStorefrontCacheOnSaveOrderingListener` instead
- method `Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade::__construct` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly SliderItemRepository $sliderItemRepository,
            protected readonly ImageFacade $imageFacade,
            protected readonly Domain $domain,
            protected readonly SliderItemFactoryInterface $sliderItemFactory,
    +       protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ```
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/33913d0d8919d78490cd25b3a9ccd50b220ffd25) to update your project

#### registration after order refactoring ([#3462](https://github.com/shopsys/shopsys/pull/3462))

- FE API: `lastOrderUuid` parameter was removed from `RegistrationDataInput` type, use new `RegisterByOrder` mutation with `RegistrationByOrderInput` to register user after order
- `CustomerUser::$newsletterSubscription` property was removed to get rid of redundant data in the database, use `NewsletterFacade::isSubscribed()` method instead
    - check also `Shopsys\FrameworkBundle\Migrations\Version20241002121943` migration whether it suits your needs
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `RegistrationDataInput` type fields
    - `NameInputObjectDecorator`
    - `TelephoneInputObjectDecorator`
    - `RegisterMutation`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1969a8ddcec5bac788dc32cf563fd87354d997c8) to update your project

#### Make parameter groups editable in admin ([#3484](https://github.com/shopsys/shopsys/pull/3484))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - Parameter groups
- field `visible` was removed from GraphQL field `Parameter`.
- a parameter groups link has been added to the menu. The order of the parameter groups will affect the order of retrieved parameters with GraphQL.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4df90c95c852b0c56f13725a2153e5f2244abedd) to update your project
- see also [project-base diff](https://github.com/shopsys/project-base/commit/3b6fc3db57e3957272c7e24393f64c1643d5b41a) of [#3557](https://github.com/shopsys/shopsys/pull/3557) where the functionality was further fixed

#### FE API: order creation: email is always taken from the logged user ([#3468](https://github.com/shopsys/shopsys/pull/3468))

- FE API: order creation: email is now required and validated for anonymous users only
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/079048c24a586ea25c5a2cf467c9679fa9d01080) to update your project

#### replace shopsys/ordered-form package with becklyn/ordered-form-bundle ([#3496](https://github.com/shopsys/shopsys/pull/3496))

- calling `setPosition()` directly on existing form fields is not supported anymore, otherwise, ordering form fields works the same way
- see [becklyn/ordered-form-bundle](https://github.com/Becklyn/OrderedFormBundle) for the documentation
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/25374669aad990372abd12754042600b2fc40edb) to update your project

#### added product inquiry type ([#3465](https://github.com/shopsys/shopsys/pull/3465))

- products upon inquiry are always listed, even if they are not in stock ot their price is not set
    - the price of such products is hidden
- new class `Shopsys\FrameworkBundle\Component\Money\HiddenMoney` was added to represent hidden prices (the amount returned is always `***`)
- products upon inquiry are listed after the regular products if sorted by price
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/123022ff62d26b83b8bd3f27d38a12004a0b1337) to update your project

#### upgrade doctrine/persistence to ^3.3.3 ([#3498](https://github.com/shopsys/shopsys/pull/3498))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9c8305a7d7e170346574dda3846c0cfc7eaeae0c) to update your project

#### Display a 404 error page when the customer file is not found ([#3497](https://github.com/shopsys/shopsys/pull/3497))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - ErrorController
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5b1cfc6) to update your project

#### upgrade shopsys/deployment package ([#3525](https://github.com/shopsys/shopsys/pull/3525))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b6479bd6575cf2f353f698969de021faddb95010) to update your project

#### use moved Ready Category Seo Mix code from packages ([#3494](https://github.com/shopsys/shopsys/pull/3494))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api and framework packages:

    - `ReadyCategorySeoMix` code along with the admin controller and templates
    - `ProductFilter` now takes into account the `ReadyCategorySeoMix` code
    - `Flag` methods used in graphql queries
    - `Parameters` now takes into account the `ReadyCategorySeoMix` code
    - `Sitemap` generating code now takes into account the `ReadyCategorySeoMix` code
    - `AdvertsQuery` in graphql now takes into account the `ReadyCategorySeoMix` code
    - `Brands` in graphql now takes into account the `ReadyCategorySeoMix` code
    - `Categories` in graphql now takes into account the `ReadyCategorySeoMix` code
    - `Flags` in graphql now takes into account the `ReadyCategorySeoMix` code
    - the following graphql fields were moved from project-base to frontend-api package:
        - queries `flag` and `flags`
        - `Category#id`
        - `Category#name`
        - `Category#description`
        - `Category#children`
        - `Category#bestsellers`
        - `Category#slug`
        - `Category#breadcrumb`
        - `Category#images`
        - `Category#products`
        - `Category#originalCategorySlug`
        - `Category#readyCategorySeoMixLinks`
        - `Category#linkedCategories`
        - `Category#mainImage`
    - method `App\Model\CategorySeo\ReadyCategorySeoMixFacade::deleteAllWithParameter()` was moved to `Shopsys\FrameworkBundle\Model\CategorySeo\DeleteReadyCategorySeoMixFacade::deleteAllWithParameter()`

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/24b61b253b5a6dd0ca08a21ba38a01b46875f23c) to update your project

#### FE API: add variantsCount to MainVariant type ([#3490](https://github.com/shopsys/shopsys/pull/3490))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6d3dc25c5a378d230f469d601955ad4b82516432) to update your project

#### role ROLE_ALL_API sees all company complaints ([#3534](https://github.com/shopsys/shopsys/pull/3534))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3d7743b) to update your project
- see also [project-base diff](https://github.com/shopsys/project-base/commit/2e1a0b4ca12610630495f366c9f4973032a608ad) of [#3563](https://github.com/shopsys/shopsys/pull/3563) with additional fix and refactor of Company Complaints

#### fix loading of elfinder with the version 12.6 of helios-ag/fm-elfinder-bundle ([#3540](https://github.com/shopsys/shopsys/pull/3540))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f7fcf3a) to update your project

#### fix product edit in dev environment ([#3544](https://github.com/shopsys/shopsys/pull/3544))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - product catnum is now required and forced to be unique
- check your code for use of the `FormBuilderInterface::getAttributes()` method and replace it with `FormBuilderInterface::getOptions()['attr'] ?? []` to prevent errors about array-to-string conversion
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/018d415) to update your project

#### improve check if the customer can be registered on B2B domains ([#3546](https://github.com/shopsys/shopsys/pull/3546))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `isCustomerUserRegisteredQuery`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0c344b9) to update your project

#### allow frontend API testing on b2b domain ([#3508](https://github.com/shopsys/shopsys/pull/3508))

- there are new tests that are relevant for you only if you use [B2B domain type](https://docs.shopsys.com/en/latest/introduction/start-building-your-application/#set-up-domain-type)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/946d40228bbd48a2d6d5a826d08c79395189bd7b) to update your project

#### [project-base] GrapesJS new plugins ([#3464](https://github.com/shopsys/shopsys/pull/3464))

- added custom table plugin
- added styleManager
- added missing translations
- see [project-base diff](https://github.com/shopsys/project-base/commit/1454c684ea34dd22423937dbc57d6909442ae26a) to update your project

#### allow administrator to select the administration locale ([#3577](https://github.com/shopsys/shopsys/pull/3577))

- `%shopsys.admin_locale%` container parameter was removed, use `%shopsys.allowed_admin_locales%` instead
    - the parameter is now an array defining the allowed locales for the administration, the first locale in the list is the default one
- `Shopsys\FrameworkBundle\Component\Domain\DomainFacade::getAllDomainConfigs()` method was removed, use `Shopsys\FrameworkBundle\Component\Domain\Domain::getAll()` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/10283dbea34635f48de6ee556f91c03cd3a3beff) to update your project

#### Upgrade Sentry package ([#3539](https://github.com/shopsys/shopsys/pull/3539))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5f55848) to update your project

#### Upgrade to Symfony 6.4 ([#3584](https://github.com/shopsys/shopsys/pull/3584))

- see upgrade notes of Symfony:
    - https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.0.md
    - https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.1.md
    - https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.2.md
    - https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.3.md
    - https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.4.md
- `Session` Symfony service no longer exists and sessions should be accessed via `Request` object
- replace typehints of `\Symfony\Bridge\Monolog\Logger` with `\Monolog\Logger` or `\Psr\Log\LoggerInterface`
- all Symfony related annotations like `@Route` or `@Required` should be replaced with appropriate attributes like `#[Route]` or `#[Required]`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/917bb6c3df9b3b145fe5f849a194d88a9164eb39) to update your project

#### replace hidden root blog category by 1st level one ([#3595](https://github.com/shopsys/shopsys/pull/3595))

- The hidden 0 level root category has been replaced by 1st level category and is visible now. Also there can be only one main category from now. If you had multiple 1st level categories until now, you will need to update your code to reflect this change and also review `packages/framework/src/Migrations/Version20241112100245.php` migration if it wont fail in your use case.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fccbccf) to update your project

#### Out of stock products behavior ([#3587](https://github.com/shopsys/shopsys/pull/3587))

- products with stock quantity 0 or less are now considered sellable
- main variants stock quantity is now always null, and it's availability is based on it's variants
    - check `Version20241121094752` migration whether it suits your needs
- `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider::AVAILABILITY_DISPATCH_TIME` was removed with no replacement
- `Shopsys\FrameworkBundle\Form\WarningMessageType` was removed, use `Shopsys\FrameworkBundle\Form\MessageType` instead
- `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade::getProductAvailabilityDaysByDomainId()` was renamed to `getProductAvailabilityDaysForFeedsByDomainId()`
- `Shopsys\FrameworkBundle\Model\Product\Product::getFullnames()` was renamed to `Product::getFullNames()`
- `Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade::checkUnavailableStockQuantityItems()` was removed with no replacement
- `Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult::addCartItemWithChangedQuantity()` and `addNoLongerAvailableCartItemDueToQuantity()` were removed with no replacement
- `Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult::$itemModifications` array no longer contains `cartItemsWithChangedQuantity` and `noLongerAvailableCartItemsDueToQuantity` keys
    - the `cartItemsWithChangedQuantity` and `noLongerAvailableCartItemsDueToQuantity` fields are removed front the FE API (`CartItemModificationsResultDecorator.types.yaml`) as well
- `Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery::applyDefaultOrdering()` was removed, use `applyOrderingByIdAscending()` instead
    - the method is used in tests only
- `Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem` class was changed:
    - `$isAvailable` and `$availableInDays` properties were removed
    - instead, `$availabilityRank` property was added
    - `getAvailability()` now always return `1` as all the items are sellable
- `Shopsys\FrameworkBundle\Model\Cart\CartMigrationFacade` class was removed. If you use FE API, you should use `Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade` instead. Otherwise, you need to implement your own cart migration logic.
- `product_available_stores_count_information` field was removed from the Elasticsearch index without replacement
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the packages:
    - `AvailabilityStatusEnum` API type was moved to the frontend-api package as `AvailabilityStatusEnumDecorator`
    - `StoreAvailability` API type was moved to the frontend-api package as `StoreAvailabilityDecorator`
    - `Availability.status` API field was moved to the frontend-api package to `AvailabilityDecorator`
    - `Product.storeAvailabilities` and `Product.availableStoresCount` API fields were moved to the frontend-api package to `ProductDecorator`
    - `ProductEntityFieldMapper::getStockQuantity()`, `getStoreAvailabilities()`, and `getAvailableStoresCount()` were moved to the frontend-api package
    - `ProductArrayFieldMapper::getStoreAvailabilities()` and `getAvailableStoresCount()` were moved to the frontend-api package
    - `ProductExportFieldProvider::AVAILABLE_STORES_COUNT`, `STORE_AVAILABILITIES_INFORMATION`, and `AVAILABILITY_STATUS` were moved to the frontend-api package
    - the whole Mergado XML feed implementation was moved from project-base to the new `product-feed-mergado` package
    - `ProductTranslation::$namePrefix`, `$nameSufix`, and `Product::getFullname()` with all the related logic was moved to the framework package
    - `ProductTranslation::$nameSufix` was renamed to `$nameSuffix`
    - `Product::getFullname()` was renamed to `getFullName()`
    - corresponding API types were moved to the frontend-api package
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2aa8dc4b751636b5ab9b00a55c0383be4bee4afd) to update your project

#### Fixed failing MainBlogCategoryDataSettingsTest because of incorrect domain url ([#3598](https://github.com/shopsys/shopsys/pull/3598))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/93a56f9) to update your project

#### split changePersonalDataMutation into two mutations ([#3601](https://github.com/shopsys/shopsys/pull/3601))

- `ChangePersonalDataInputDecorator` now contain the `newsletterSubscription` field
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d830e3e) to update your project

#### provide mandatory attribute weight to Packeta ([#3604](https://github.com/shopsys/shopsys/pull/3604))

- see [project-base diff](https://github.com/shopsys/project-base/commit/778bd9e9a170fb5d6d0439fdedf632eac450b038) to update your project

#### Extended banner slider ([#3574](https://github.com/shopsys/shopsys/pull/3574))

- created NumberSliderFormType for admin
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/32ba35274b5eb675ca4b636775eea3f36bbcee8c) to update your project

#### Removing administrator password settings from administration ([#3606](https://github.com/shopsys/shopsys/pull/3606))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - AdministratorFormType
- The password fields in the administrator form are separated into a new form type `AdministratorPasswordFormType`.
- Method `AdministratorFacade::changePassword()` was removed, use `AdministratorPasswordFacade::setPassword()` instead
- Field `Administrator::$password` is now nullable, check your usage
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7d6e5bd) to update your project

#### switching to parallel execution of cypress tests ([#3514](https://github.com/shopsys/shopsys/pull/3514))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/965346907d06684c03d5f6df90e42168c9850a16) to update your project

#### refactor Dockerfiles for PHP-FPM ([#3518](https://github.com/shopsys/shopsys/pull/3518))

- Dockerfile for PHP-FPM was rewritten from debian to alpine linux
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1a5af5db8882c19b45d87f47890f1cbce39c94bf) to update your project
- additionally fixed in [3618](https://github.com/shopsys/shopsys/pull/3618)
    - see also [project-base diff](https://www.github.com/shopsys/project-base/commit/50b2264cf0cd0ba47d99ca7e698d7c2bac9883d4) to update your project
- additionally fixed in [#3619](https://github.com/shopsys/shopsys/pull/3619)
    - see also [project-base diff](https://www.github.com/shopsys/project-base/commit/897bf3a8ccfd630ec7de8fb5a24f3c9d30f1f1dd) to update your project

#### create JwtConfiguration on demand ([#3616](https://github.com/shopsys/shopsys/pull/3616))

- `Lcobucci\JWT\Configuration` is not a service anymore, use `Shopsys\FrontendApiBundle\Model\Token\JwtConfigurationProvider::getConfiguration()` instead
- `Shopsys\FrontendApiBundle\Model\Token\JwtConfigurationFacade` has been renamed to `Shopsys\FrontendApiBundle\Model\Token\JwtConfigurationProvider` and its method `create` has been renamed to `getConfiguration`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ec77b36) to update your project

#### Slug of Seo page is now saved as text instead of FriendlyUrl ([#3608](https://github.com/shopsys/shopsys/pull/3608))

- `SeoPageFriendlyUrlDataProvider` class has been removed as it is no longer needed
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/081fa39d637df0adf71ccefdf70f6f754ed92079) to update your project

#### enable sentry performance monitoring ([#3626](https://github.com/shopsys/shopsys/pull/3626))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8a0bdae) to update your project

#### promo code for free transport and payment ([#3625](https://github.com/shopsys/shopsys/pull/3625))

- `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode::$discountType` is now a string instead of an integer
    - check `Version20241126152128` migration whether it suits your needs
    - `PromoCode::DISCOUNT_TYPE_PERCENT` and `PromoCode::DISCOUNT_TYPE_NOMINAL` constants were removed, use the constants defined in `PromoCodeTypeEnum` instead
- `Shopsys\FrameworkBundle\Model\Order\Order` has a new attribute `$freeTransportAndPaymentApplied` that contains information about whether the order has free transport and payment applied (either via reaching limit or via applying the promo code)
    - check `Version20241128111224` migration whether it suits your needs - it sets the attribute to `false` for all existing orders
- `Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation::isFree()` method was removed, use `Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade::isFree()` instead
- `Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation::isFree()` method was removed, use `Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade::isFree()` instead
- frontend API: `Cart.promoCode` changed its type from string to `PromoCode` object (consisting of string `code` and string `type` properties)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/43cf6203af08959eabb06ab0104bc87d0fc73e5f) to update your project

#### Extend gitlab pipelines for cypress screenshots regeneration ([#3630](https://github.com/shopsys/shopsys/pull/3630))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/088f7513ba360eda971569111c9ad77b365d4362) to update your project
- see also [project-base diff](https://github.com/shopsys/project-base/commit/965346907d06684c03d5f6df90e42168c9850a16) of [#3514](https://github.com/shopsys/shopsys/pull/3514) with additional information about the changes in the cypress.config.ts

#### admin: sending test mail templates on click ([#3639](https://github.com/shopsys/shopsys/pull/3639))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `MailController::editAction()`
    - `MailController::transformBodyVariables()`
    - `GrapesJsMailType` (along with `grapesJsMailType.html.twig` and `grapesJsType.html.twig` twig templates)
    - `MailTemplateFormTypeExtension` changes were moved to `MailTemplateFormType`
    - `DomainExtension::getDomainUrlByLocale()`
    - `GrapesJsMailExtension`
- `CustomerActivationMail::getVariableNewPasswordUrl()` and `ResetPasswordMail::getVariableNewPasswordUrl()` were removed, use `NewPasswordUrlProvider::getNewPasswordUrl()` instead
- `CustomerUser` and `Administrator` now implement new `ResetPasswordInterface`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a773b9efa0ba1e3f556428204cb20e03c99afafa) to update your project

#### remove no longer needed SessionChecker ([#3650](https://github.com/shopsys/shopsys/pull/3650))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/88efdc5bbe5340c8480577141d2a19cb6d73bee8) to update your project

#### fixed wrong usage of classes ([#3651](https://github.com/shopsys/shopsys/pull/3651))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/22176994a8b601d84a200635e24ddc46fafd6f58) to update your project

#### watchdog ([#3640](https://github.com/shopsys/shopsys/pull/3640))

- FE API: main variant product now returns all visible variants (`MainVariant.variants`) and all visible variants count (`MainVariant.variantsCount`) instead of just the sellable ones
- `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper` class was changed:
    - `$productsSellableByIdsBatchLoader` is no longer used to get variants, use `$productsVisibleByIdsBatchLoader` instead
    - `$productsSellableCountByIdsBatchLoader` is no longer used to get variants count, use `$productsVisibleCountByIdsBatchLoader` instead
- `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper` class was changed:
    - `$productsSellableByIdsBatchLoader` is no longer used to get variants, use `$productsVisibleByIdsBatchLoader` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a1f963231d3821f794102ba86a04fa9517148201) to update your project

#### ensure manually sent mail templates are wrapped with the GrapesJS body ([#3664](https://github.com/shopsys/shopsys/pull/3664))

- `Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade::get()` was renamed to `getWrappedWithGrapesJsBody()`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/dd75ce6450082c1b86cd0796d6fc48e5db61096d) to update your project

#### improve rendering of the eshop required settings ([#3658](https://github.com/shopsys/shopsys/pull/3658))

- `Shopsys\FrameworkBundle\Controller\Admin\DefaultController`
    - method `addWarningMessagesOnDashboard()` was removed
    - method `checkEnabledMailTemplatesHaveTheirBodyAndSubjectFilled()` was removed
    - method `checkAtLeastOneUnitExists()` was removed
    - method `checkDefaultUnitIsSet()` was removed
    - method `checkMandatoryArticlesExist()` was removed
    - method `checkAllSliderNumericValuesAreSet()` was removed
- use new class `RequiredSettingExtension` instead of removed methods
- the required settings are now rendered in the `layoutWithPanel.html.twig` template by calling the `render_required_settings()` function instead of inside the flash message
- translations for the required settings have changed, check if you need to update your custom translations

#### Upgrade Redis to the newest version ([#3673](https://github.com/shopsys/shopsys/pull/3673))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f8daff5fd0e5a0adf81d8acf8e7fbde8029a0bb9) to update your project
- Upgrade `shopsys/deployment` package to minimal version `3.3.2`
- If you have installed Review server, then don't forget to update the `redis` service in `docker-compose.yml` to the `7.4-alpine`.

#### Hide warning about not secured CKEditor ([#3677](https://github.com/shopsys/shopsys/pull/3677))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9c78358a72bf6f3c982ce723505a1fb5e74b37f8) to update your project

#### do not restart consumer container to prevent network issues ([#3681](https://github.com/shopsys/shopsys/pull/3681))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5e0c9b44683d5891aec5927e54e1ad047d3783ce) to update your project

#### ensure graphql schema is always rendered same ([#3678](https://github.com/shopsys/shopsys/pull/3678))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cf46f6297f491abc4b0c7e34502c23b8113ba50b) to update your project

#### administrator email now must be unique ([#3686](https://github.com/shopsys/shopsys/pull/3686))

- `Shopsys\FrameworkBundle\Model\Administrator\Administrator::$email` has now unique constraint
    - check your database for administrator duplicate email addresses and fix them manually
- `Shopsys\FrameworkBundle\Model\Administrator\Exception\DuplicateUserNameException` was removed, use `UniqueEntityField` constraint for validating administrator unique username instead
- `Shopsys\FrameworkBundle\Model\Administration\AdministratorFacade::checkUsername()` was removed, use `UniqueEntityField` constraint for validating administrator unique username instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ee9075949b205e4e5d37dac20b8fa83cdb017cde) to update your project

#### Upgrade some composer dependencies ([#3688](https://github.com/shopsys/shopsys/pull/3688))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a2fb71a81f37cc5775bcc1cd04b7f8fb81dd6939) to update your project
- see also [project-base diff](https://github.com/shopsys/project-base/commit/fa63983c9639f6cda8fd29e0ec71c913b4312e3b) of [#3698](https://github.com/shopsys/shopsys/pull/3698) with additional fix

#### Fix zero downtime deployment ([#3689](https://github.com/shopsys/shopsys/pull/3689))

- Phing target `db-migrations-count-with-maintenance` was updated, check your `build.xml` and modify the target appropriately if you have overridden it:
    ```diff
    <target name="db-migrations-count-with-maintenance" hidden="true" description="Get count of database migrations to execute and enable maintenance mode if more than zero.">
    -    <exec executable="${path.php.executable}" checkreturn="true" outputProperty="migrationCounts">
    +    <exec executable="${path.php.executable}" checkreturn="true" passthru="true" returnProperty="migrationCounts">
            <arg value="${path.bin-console}"/>
            <arg value="shopsys:migrations:count"/>
            <arg value="--simple"/>
            <arg value="--verbose"/>
        </exec>
    ...
    ```
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/102a1d5542044195f2f8baabd181fb25b0497b4d) to update your project

#### add missing symfony/doctrine-messenger dependency ([#3694](https://github.com/shopsys/shopsys/pull/3694))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c067aab4a08b67786a2cb8b2f4168285339b620a) to update your project

### Storefront

#### Added complaints to personal data query ([#3433](https://github.com/shopsys/shopsys/pull/3433))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a5eab40fcd489c8ab21faa29355d2437544b1c99) to update your project

#### homepage redesign ([#3446](https://github.com/shopsys/shopsys/pull/3446))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5b1fc3409539a00a562517a7e9a41ed40d9c69cb) to update your project

#### Improve cypress tests developer workflow ([#3411](https://github.com/shopsys/shopsys/pull/3411))

- add option to regenerate cypress snapshots as part of the github workflow
    - if developer makes any change in design or anything somewhat related to change of snapshots the cypress acceptance test in github workflow will fail
    - they can now add label to the pull request called `regenerate screenshots` and then manually run the job again to switch from acceptance cypress test to base cypress test to generate new set of sreenshots to replace snapshots
    - new snapshots are automatically pushed to the branch the of the PR
- adjust test names and format of snapshot names so it is easier to distinguish from one and other when navigating in file explorer to view failed test screenshot or diffs between them
- fix `get_ip` function in Makefile to get correct display when running GUI tests locally using xQuartz
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9cf723baddb33a944f58f0589396c9beda01ad53) to update your project

#### typography based on design ([#3454](https://github.com/shopsys/shopsys/pull/3454))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6014f0df7044452f51a2bb58fbd3d4bc05485d85) to update your project

#### browser sync for product lists ([#3463](https://github.com/shopsys/shopsys/pull/3463))

- see [project-base diff](https://github.com/shopsys/project-base/commit/af747d1990db7164dc275fca304fa1b4e94cb933) to update your project

#### login popup based on design ([#3466](https://github.com/shopsys/shopsys/pull/3466))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/271d89940d402d6f9935c3d97ac01d3e5c4bce00) to update your project

#### prevent white page after deploy ([#3467](https://github.com/shopsys/shopsys/pull/3467))

- see [project-base diff](https://github.com/shopsys/project-base/commit/7df91b55f626d4f476c8fb68283700cf9f960287) to update your project

#### disabled user email input in cart ([#3468](https://github.com/shopsys/shopsys/pull/3468))

- logged-in users can no longer change their email in the cart
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/079048c24a586ea25c5a2cf467c9679fa9d01080) to update your project

#### added cart skeleton loader ([#3470](https://github.com/shopsys/shopsys/pull/3470))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/39670c7f462d1614278f2ddd4aacd3cbaccb6b28) to update your project

#### unified date format based on localization ([#3471](https://github.com/shopsys/shopsys/pull/3471))

- unified date format based on localization
- specific localization needs to be imported in file formatDate.ts from
- eg. import 'dayjs/locale/en-gb';

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b082dbe386fc037491d1e6c823c0963b40dc43c8) to update your project

#### fix complaint search ([#3472](https://github.com/shopsys/shopsys/pull/3472))

- complaints and ordered items search now display correct values, even when the user is navigating through pagination
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3db715f4bad11abf97dcd9566eb3d434b3735c92) to update your project

#### remove link of hide product in order detail ([#3476](https://github.com/shopsys/shopsys/pull/3476))

- removed link of hidden product in order detail
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7603f0c2a103138c06daf67bb164bbd3ad58d16d) to update your project

#### registration after order refactoring ([#3462](https://github.com/shopsys/shopsys/pull/3462))

- there is a new mutation `RegisterByOrder` in the FE API, which is used instead of `RegisterMutation` and `lastOrderUuid` parameter in `RegistrationDataInput`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1969a8ddcec5bac788dc32cf563fd87354d997c8) to update your project

#### fix console warining and errors ([#3479](https://github.com/shopsys/shopsys/pull/3479))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/59e285c7490447a1aef8c6d9f9e41124bba3e454) to update your project

#### fix product sorting by relevance and prices ([#3488](https://github.com/shopsys/shopsys/pull/3488))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8b4dd2883a1120e7aebfe3741c346f61ef0d785d) to update your project

#### Make parameter groups editable in admin ([#3484](https://github.com/shopsys/shopsys/pull/3484))

- field `visible` was removed from GraphQL field `Parameter`.
- changing the order of parameters. Parameters without groups are listed first, then parameters by groups according to the group order set in the administration.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4df90c95c852b0c56f13725a2153e5f2244abedd) to update your project

#### fixed empty cart in header ([#3493](https://github.com/shopsys/shopsys/pull/3493))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ca45fe9aac77153ecc0e5de90ff3b0f06fb3cf82) to update your project

#### added variants count to product list item ([#3490](https://github.com/shopsys/shopsys/pull/3490))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6d3dc25c5a378d230f469d601955ad4b82516432) to update your project

#### GTM event about order creation is sent twice ([#3495](https://github.com/shopsys/shopsys/pull/3495))

- now is GTM event about order creation sent once
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/aa8a019f62ec9ebf6c6e1431016765088f1c05bc) to update your project

#### inquiry popup with form ([#3465](https://github.com/shopsys/shopsys/pull/3465))

- added new popup with inquiry form and validation
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/123022ff62d26b83b8bd3f27d38a12004a0b1337) to update your project

#### redesign sales representative ([#3500](https://github.com/shopsys/shopsys/pull/3500))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7745176a73b90eebcf83691ad95a621adc6d0aee) to update your project

#### hide users section for regular customers ([#3502](https://github.com/shopsys/shopsys/pull/3502))

- the user section is not available for regular customers
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/47f910fd43205b4352d8836d78caa328e4669cd8) to update your project

#### Add animations ([#3469](https://github.com/shopsys/shopsys/pull/3469))

- add animations to multiple components, unify and improve current animations
- to use animated components, you can choose from predefined animations in `/components/Basic/Animations` or you can create your own (using `AnimatePresence` and motion components from `framer-motion` lib)
- please note that when using `AnimateCollapseDiv`, the padding or margin prop would interfere with the box system resulting in wrong (scattered) animation of the component's height, so set the padding or margin to children or parent component instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f88f88124ef15ef003f9022b3c2316beed9b0766) to update your project

#### fix select box responsiveness ([#3504](https://github.com/shopsys/shopsys/pull/3504))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/63f49fdcf514bd6e204fba48546d783cb4957278) to update your project

#### fix active and hover state for inverted button in responsive ([#3506](https://github.com/shopsys/shopsys/pull/3506))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c43f8e6d89e0014dfe7d185d029ad93f3ed7abed) to update your project

#### add filter panel to brad and flag pages ([#3507](https://github.com/shopsys/shopsys/pull/3507))

- brand and flag pages now have filter panel
- refactored filter panel with state management
- search page now uses new filter panel
- added new content wrappers
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e0ebe71b8c872d0bcd051f8148c9361d38659e47) to update your project

#### Fix unknown error messages ([#3517](https://github.com/shopsys/shopsys/pull/3517))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fbed6b30a048353ddc224934a9f3f63ac084c514) to update your project

#### show category image just on first page ([#3522](https://github.com/shopsys/shopsys/pull/3522))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/408e6cec09d82eacec331268209d8fbe228f26e7) to update your project

#### reset form state on logout ([#3523](https://github.com/shopsys/shopsys/pull/3523))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/55336212c582345719fd7425ba7e12fca3911091) to update your project

#### refactor form messages ([#3524](https://github.com/shopsys/shopsys/pull/3524))

- unified handling of form messages
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c680bc743fbf45a865825c28e2f5b2784072b148) to update your project

#### disabled fields in cart for B2B customers ([#3527](https://github.com/shopsys/shopsys/pull/3527))

- in the B2B domain, the company customers cannot edit their company details during the order process
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/eda15cbbedca55f76e2d8b2b381eda50646ab645) to update your project

#### remove unused slug query related code ([#3494](https://github.com/shopsys/shopsys/pull/3494))

- `slugQuery.graphql` and `slugTypeQuery.graphql` were removed along with generated types
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/24b61b253b5a6dd0ca08a21ba38a01b46875f23c) to update your project

#### Fix popup blurry text ([#3521](https://github.com/shopsys/shopsys/pull/3521))

- fixed issue with non-integer position values after `translate` to center popup
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0bca3d3cb4918046f839b9b429cd0ad9b29a83e0) to update your project

#### fix order detail skeleton ([#3529](https://github.com/shopsys/shopsys/pull/3529))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1c4d3c24ce6ccb6352e29594a8be0163eeb6b99c) to update your project

#### add form validation delay ([#3530](https://github.com/shopsys/shopsys/pull/3530))

- to enhance the user experience, we’ve added a delay 500ms in displaying errors while the user is typing
- unified the form resolver shape type
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5290bd2a4421db07cec48fee708a072e88c3542f) to update your project

#### removed filter re-render on search page ([#3531](https://github.com/shopsys/shopsys/pull/3531))

- removed unnecessary re-renders of the filter panel on the search page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ba7272aca61683933b1c44525826fad4f71826e4) to update your project

#### Add Luigi's box recommender identifier ([#3520](https://github.com/shopsys/shopsys/pull/3520))

- `useRouter`'s pathname is used to identify in which place does the Luigi's box recommended products are shown
- please follow the example of pre-defined identifier names and fill all your pathnames to `RECOMMENDER_PATHNAMES` in `/utils/getRecommenderClientIdentifier.ts` and give them unique identifier as well
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7b8aead23c6e2e668da0e8e4ea9e115d86021cb8) to update your project

#### hide repeat order button ([#3535](https://github.com/shopsys/shopsys/pull/3535))

- the repeat order button is hidden now if all items are not visible or denied for sale
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f6ab1476a65fce5411a9fce613add2fbbca104a6) to update your project

#### fix delivery address form validation ([#3541](https://github.com/shopsys/shopsys/pull/3541))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/54bebf5800c095894dd83982689c2952b60f9b5c) to update your project

#### enhance email validation ([#3543](https://github.com/shopsys/shopsys/pull/3543))

- enhanced the email regex for more comprehensive validation
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/74fab82deb45c667b975bd28960eb7d4ad34f45a) to update your project

#### Fix payment/transport animation scrollbar flicker ([#3538](https://github.com/shopsys/shopsys/pull/3538))

- on 4k monitor the select/unselect of payment caused content height to exceed the screen height
- this triggered the scroll bar to appear for short duration causing layout shift
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0e1906080aee3fd1a7dbd4138a424f3217154a8f) to update your project

#### create page change password ([#3545](https://github.com/shopsys/shopsys/pull/3545))

- added page change password
- remove change password from edit profile
- added link to user menu and customer navigation
- polished skeleton loaders
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bf01c658cfa9a50d065631f59437e5307a9ea8c6) to update your project

#### Fix wrong redirect after refresh ([#3537](https://github.com/shopsys/shopsys/pull/3537))

- FIX: wait for data fetching finish before checking for user permissions
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/eedd7ba69f004b71b904ef9c690ebd9665e92407) to update your project

#### do not show the registration form after order if the customer cannot be registered ([#3546](https://github.com/shopsys/shopsys/pull/3546))

- error message mapper now handles the `company-already-registered` error code and shows the appropriate message
- in after order registration, the registration form is not shown if the customer cannot be registered
    - the `couldBeCustomerRegisteredQuery` is used to determine if the customer can be registered
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f8b75d9c2eb979abdfae4ccb5c01d0e2bbb068c7) to update your project

#### Always create db when running cypress tests locally ([#3562](https://github.com/shopsys/shopsys/pull/3562))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c9c6c4b0883105c057c071257c526ebab97ce9ea) to update your project

#### category image responsive ([#3565](https://github.com/shopsys/shopsys/pull/3565))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d09babce7b94a5fafa58c95c18d5d13c26a348eb) to update your project

#### popup for account / login ([#3568](https://github.com/shopsys/shopsys/pull/3568))

- changed logic for showing popup and drawer for account / login
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/85aacfaeac93dad60f64aec2915dc22df289c68d) to update your project

#### Add animations for mobile navigation menu ([#3569](https://github.com/shopsys/shopsys/pull/3569))

- refactored menu items UI into `MenuItems` component
- there are two instances, one with id `animation-visible-element` and one with `animation-hidden-element`
    - the former is always shown at the end of animation
    - the latter is always hidden from the view after the animation
    - we need two instances to show the animation between expanding the menu or going back to parent items
- at the beginning of a transition, based on the transition function (`handleExpandClick` or `handleBackClick`), we either position the components correctly before the animation, or adjust the position at the end of the animation to make sure we always end up in the default position of components (this is done by `AnimationSequence` and these sequences are named descriptively to ilustrate the resulting transition)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/63870aad0bfe80a0529e08960c7afec61f36335f) to update your project

#### fix change password page ([#3576](https://github.com/shopsys/shopsys/pull/3576))

- refactored page for users without password (logged via social networks)
- refactored styles for user menu
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/eb61eb0f37d81cbca9d95ed6c91161260b64f617) to update your project

#### remove initial tab animation on product detail page ([#3579](https://github.com/shopsys/shopsys/pull/3579))

- first tab on product detail page is not animated in first load
- refactored slider placeholders and skeletons
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c1476ec5721303f0d6ebb286d60197e1ae7b9bf9) to update your project

#### polish menu animations ([#3580](https://github.com/shopsys/shopsys/pull/3580))

- enhanced menu animations for smoother transitions
- developed new animation variants specifically for the menu
- adjusted z-index for each menu animation variant to ensure immediate coverage during menu closing
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a3e6758092030fa743c933f715498fa04d3991c3) to update your project

#### create custom select box ([#3588](https://github.com/shopsys/shopsys/pull/3588))

- created a new select box featuring a combo box option and infinite scrolling within the selection list
- updated style guide with examples
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c00e17d195436a3a3192dddd4ad1f9fde3e01d3) to update your project

#### Add banner description UI ([#3574](https://github.com/shopsys/shopsys/pull/3574))

- the dynamic background color property is build with `getRGBColorString`
    - splits the hex string into parts representing each color
        - first two chars representing hexadecimal red
        - middle two chars representing hexadecimal green
        - last two chars representing hexadecimal blue
    - `parseInt` function then converts the two char string in hexadecimals to rgb value (_[16,16] string -> 256 number_)
    - then it is formatted as `rgb(r g b / a)` css property and set as element's style because tailwind cannot handle dynamic class names
- the color of the desription text is computed via `getYIQContrastTextColor` to align with the accessibility recommendations (black-ish text for lighter bg and white text for darker bg)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/32ba35274b5eb675ca4b636775eea3f36bbcee8c) to update your project

#### Update UX for user menu ([#3583](https://github.com/shopsys/shopsys/pull/3583))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d9c688c1a3749140c548adbb8e058fc229c5b68a) to update your project

#### Remove stale original category slug from session ([#3592](https://github.com/shopsys/shopsys/pull/3592))

- sorting is now allowed on different pages than category, but the sort was still trying to recover from SEO category by replacing current url with the original category slug and sort/filter queries
- flags, brands and search pages are out of scope of SEO, therefore the stale `originalCategorySlug` is removed from session store preventing wrong url rewrites
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ae40bb922b333dcbb39bfbe5786f148782194805) to update your project

#### disable link click on product list item text select text ([#3593](https://github.com/shopsys/shopsys/pull/3593))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/06978de597df991f880464b2b023155e8a0863a7) to update your project
- to `ExtendedLink` component added new property `onClickExtended` - if defined this can override local click function from component

#### brand and product code alignment ([#3596](https://github.com/shopsys/shopsys/pull/3596))

- the brand and product code on product detail page are now align correctly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/16cc0167479bf8ccabf57e80a4565eee3c39fcd1) to update your project

#### Out of stock products behavior ([#3587](https://github.com/shopsys/shopsys/pull/3587))

- products with stock quantity 0 or less are now considered sellable
- `<ProductAvailableStoresCount>` component changed:
    - the component was renamed to `<ProductAvailability>`
    - it is used on the product detail page as well instead of `<ProductDetailAvailability>` which was removed
    - property `name: string` was replaced by `availability: TypeAvailability`
    - property `isMainVariant` was removed, `availableStoresCount` is now nullable (main variant has `null` value)
    - add new properties `isInquiryType: boolean`, `onClick?: () => void`, and `className: string` (inherited from `FC`) to customize the component
- rendering of product availability in `<ProductAvailableStoresCount>` and `ProductDetailAvailability>` components is now based on the availability status
- all the logic related to the cart modifications due to the stock quantity (`cartItemsWithChangedQuantity`, `noLongerAvailableCartItemsDueToQuantity`, `addProductResult.notOnStockQuantity`) was removed
- "add to cart" spinboxes are no longer restricted by product quantity
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2aa8dc4b751636b5ab9b00a55c0383be4bee4afd) to update your project

#### fix not rendering category without image ([#3600](https://github.com/shopsys/shopsys/pull/3600))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f1f3fada7f3fd2d1b1fe6d29e9fffef82f9e713a) to update your project

#### split changePersonalDataMutation into two mutations ([#3601](https://github.com/shopsys/shopsys/pull/3601))

- `useChangePersonalDataMutation` now accepts only first/last name, telephone, and newsletterSubscription as input
- use `changeCompanyDataMutation` to change the company data (e.g., billing address, company name, company number, etc.)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d830e3e5509aefd3d1c2b82100f14fc7cbc698ba) to update your project

#### Enable deleting Spinbox's value ([#3607](https://github.com/shopsys/shopsys/pull/3607))

- refactored and simplified `CartListItem` and `Spinbox` with `useDebounce` hook
- user is able to delete the value and write a new one (not enforcing min value on `NaN` value)
- when left blank and input is blurred (unfocused), value is set to the previous one
- internally, the value of `spinboxRef` can become `NaN`, but the `onChangeValueCallback` is only invoked when the value is a number or after input is blurred and is then restored to the previous value
- fixed decreasing value on mouse click
- the `useDebounce` hook in `Spinbox` helps to achieve smoother UX as it waits 500ms before invoking the `onChangeValueCallback`, making it more forgiving for user mistakes
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cee5d1ea2abe24bbf10ce0f3633166e961a7db61) to update your project

#### fix user navigation links alignment ([#3610](https://github.com/shopsys/shopsys/pull/3610))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ddbfb08a3a0b8e2381679a96722c792fe4e68aa6) to update your project

#### Revision of .env files ([#3603](https://github.com/shopsys/shopsys/pull/3603))

- analysed `.env` files and compared them with variables used in Gitlab CI/CD configuration
- added few comments explaining some variables
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/75d2790992559dd9e496afec5b73176f3b7cbb43) to update your project

#### Remove duplicate Webline for RecommendedProducts ([#3613](https://github.com/shopsys/shopsys/pull/3613))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2f65ce48e8d6693f31aeb96ed8a2e3d29689d2ed) to update your project

#### Add role column to users table ([#3617](https://github.com/shopsys/shopsys/pull/3617))

- the users preview table now includes a "Role" column
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5a793985125315d7ed09f2e9f8a65b326c1446cd) to update your project

#### failed build ([#3621](https://github.com/shopsys/shopsys/pull/3621))

- added URQL fetcher condition to prevent caching of failed queries
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/41789b0d60c586210cf2a6f0f774a65344b07d89) to update your project

#### Do not log 'no-log' errors in dev environment ([#3608](https://github.com/shopsys/shopsys/pull/3608))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/081fa39d637df0adf71ccefdf70f6f754ed92079) to update your project

#### promo code for free transport and payment ([#3625](https://github.com/shopsys/shopsys/pull/3625))

- in `components/Blocks/OrderSummary/PromoCode.tsx`, display the discount only if the discount price is greater than 0
- frontend API: `Cart.promoCode` changed its type from string to `PromoCode` object (consisting of string `code` and string `type` properties)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/43cf6203af08959eabb06ab0104bc87d0fc73e5f) to update your project

#### unknow error after visit error page ([#3631](https://github.com/shopsys/shopsys/pull/3631))

- added cookiesStore to error page props to avoid undefined userIdentifier
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a8691a04c116103d54cb257f7566fea1b39b4164) to update your project

#### remove necessary space in product box ([#3632](https://github.com/shopsys/shopsys/pull/3632))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e1a87fbf59dbf62c6f8f821961b85d9e80b886a8) to update your project

#### add watchdog ([#3640](https://github.com/shopsys/shopsys/pull/3640))

- customer can create watchdog for product from product detail page
- product must be visible out of stock and not type inquiry
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a1f963231d3821f794102ba86a04fa9517148201) to update your project

#### dynamic tab index ([#3644](https://github.com/shopsys/shopsys/pull/3644))

- tab index is now dynamic, so it will be incremented by 1 for each tab, because there can be missing tab in list
- active tab state is needed for animations to work correctly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/36b01ec7608cb4c7423da2a8544a0f3edec5dbad) to update your project

#### Fix gopay double spend issue ([#3635](https://github.com/shopsys/shopsys/pull/3635))

- `order-payment-confirmation.tsx` page was refactored to new `PaymentStatus` component because of increased mental capacity needed to differenciate between states
- `hasPaymentInProcess` has beed added to indicate the new order state
- new UI was added to handle this
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0a290486973b34f78d752b329bc18045009258c7) to update your project

#### fix single banner overflow ([#3660](https://github.com/shopsys/shopsys/pull/3660))

- single banner is now showing correctly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c1c9b23e4e6011751f5e23230f149a5a1c9bde4) to update your project

#### Fix preselecting packetery with empty pickup point ([#3663](https://github.com/shopsys/shopsys/pull/3663))

- when we unselect transport or clear a persist store, we lose the previously selected pickup point of packetery transport
- now we check the correctness of data before preselecting packetery transport with corrupted pickup point
- in case of incomplete info, the error is thrown and then caught in `loadTransportAndPaymentFromLastOrder` just to skip the `changeTransportInCart` and the rest of preselecting of transport and payment from last order
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9b6ebdf1573166192af40686804770850bc99a5e) to update your project

#### update dependencies and resolve vulnerabilities ([#3646](https://github.com/shopsys/shopsys/pull/3646))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6d9b5043202650dcbe060b62f75c7c2e73f78856) to update your project

#### remove stock availability from products excluded from sale ([#3667](https://github.com/shopsys/shopsys/pull/3667))

- product availability is no longer displayed for products excluded from sale
- pages: wishlist, comparison, detail and blog
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/933dc250ca9a1c7eb5145c0240c319198bcc3a72) to update your project

#### hide repeat button when product is type inquiry ([#3668](https://github.com/shopsys/shopsys/pull/3668))

- repeat button is no longer visible when product is ty inquiry
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3a52c56d6b2501b5c1de970bdb7c254e501c5292) to update your project

#### Upgrade Redis to newest version ([#3673](https://github.com/shopsys/shopsys/pull/3673))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f8daff5fd0e5a0adf81d8acf8e7fbde8029a0bb9) to update your project

#### article page slider not appearing correctly ([#3674](https://github.com/shopsys/shopsys/pull/3674))

- The product slider is now appearing correctly on the Article page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5d509b3e929183899d33919b24b8769029dfb448) to update your project

#### Fix Luigi's box recommended products missing identifiers ([#3671](https://github.com/shopsys/shopsys/pull/3671))

- to fix unhandled error, `logException` is used instead of throwing new Error on missing pathname in `RECOMMENDER_PATHNAMES`
- added more pathname options from where a product may be added to cart, resulting in the `RecommendedProductsQuery` being refetched due to `AddToCartPopup` containing `DeferredRecommendedProducts`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2051bde8d4ed2c84441d27491e7d7267be093f6d) to update your project

#### ensure graphql schema is always rendered same ([#3678](https://github.com/shopsys/shopsys/pull/3678))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cf46f6297f491abc4b0c7e34502c23b8113ba50b) to update your project

#### migrate sentry to version 8 ([#3691](https://github.com/shopsys/shopsys/pull/3691))

- migrate sentry to version 8 base on [migration guide](https://docs.sentry.io/platforms/javascript/guides/nextjs/migration/v7-to-v8/)
- add experimental `instrumentationHook` to `next.config.js`
- add `instrumentation.ts` with configuration for node and edgeruntime
- remove `sentry.server.config.js`, keep only `sentry.client.config.js`
- in `next.config.js` move sentry property from `nextConfig` to `SentryWebpackPluginOptions`
- add property `sourcemaps: { deleteSourcemapsAfterUpload: true}` to `SentryWebpackPluginOptions` to prevent disabling debugging app by users
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2f806de9568e8b84a2344cbe3bfa115f812ade80) to update your project

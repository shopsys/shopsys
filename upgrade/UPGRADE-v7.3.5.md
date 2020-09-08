# [Upgrade from v7.3.4 to v7.3.5](https://github.com/shopsys/shopsys/compare/v7.3.4...v7.3.5)

This guide contains instructions to upgrade from version v7.3.4 to v7.3.5.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Application
- add `psr/event-dispatcher` to your composer dependencies in order to prevent PHPStan errors in Event classes ([#1894](https://github.com/shopsys/shopsys/pull/1894))
    - add `"psr/event-dispatcher": "0.6.0",` to `require-dev` section in your `composer.json` file

- enable automatic deleting of sessions older than 7 days in Redis ([#1842](https://github.com/shopsys/shopsys/pull/1842))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/2549aae4a8f9188606d344771ee7b8807f630d86) to update your project
    - you should consider what to do with current sessions, if you want to keep them, set them TTL or delete them

- add missing elasticsearch host to production docker-compose.yml file ([#1861](https://github.com/shopsys/shopsys/pull/1861))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/2172ee8867d926f07bda0b09c8285b1ee95c8aea) to update your project

- fix login form validation is initialized too early ([#1906](https://github.com/shopsys/shopsys/pull/1906))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/478c26252225317669af8dae2f545b34c33ca564) to update your project

- order can now be completed when successful flash message exists ([#1644](https://github.com/shopsys/shopsys/pull/1644))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/5ffaff19de9c581e4ac92b963d78cd76ff649c71) to update your project

- set default availability for ProductData ([#1723](https://github.com/shopsys/shopsys/pull/1723))
    - following constructor changed its interface:
        - `ProductDataFactory::__construct()`
            ```diff
                public function __construct(
                    VatFacade $vatFacade,
                    ProductInputPriceFacade $productInputPriceFacade,
                    UnitFacade $unitFacade,
                    Domain $domain,
                    ProductRepository $productRepository,
                    ParameterRepository $parameterRepository,
                    FriendlyUrlFacade $friendlyUrlFacade,
                    ProductAccessoryRepository $productAccessoryRepository,
                    ImageFacade $imageFacade,
                    PluginCrudExtensionFacade $pluginDataFormExtensionFacade,
                    ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
            -       PricingGroupFacade $pricingGroupFacade
            +       PricingGroupFacade $pricingGroupFacade,
            +       ?AvailabilityFacade $availabilityFacade = null
                ) {
            ```

- update docker-php-entrypoint to show all lines from first command output ([#1827](https://github.com/shopsys/shopsys/pull/1827))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/c3a602f3c814262021369d829a9328903b6f1d6d) to update your project

- fix wrong url of freshly uploaded files in wysiwyg ([#1926](https://github.com/shopsys/shopsys/pull/1926))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/f2233690b18a9f1d0d132de83ec1e6b9c69cf592) to update your project

- fixed displaying errors in popup window ([#1970](https://github.com/shopsys/shopsys/pull/1970))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/46afe793cf5255dfc53519008de7ec39f8e9cca3) to update your project

- update your redis build-version to include application environment ([#1985](https://github.com/shopsys/shopsys/pull/#1985))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/6e5afbb8c8e1948becb8d9be3c5d2e3995c9100b) to update your project
    - run `php phing build-version-generate`

- fix spinbox increase value with set min-value ([#1979](https://github.com/shopsys/shopsys/pull/#1979))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/6e6ba110ad4649d0a4d7207916d4351fca8331d0) to update your project

- load product accessories in after add window only if corresponding module is enabled ([#1990](https://github.com/shopsys/shopsys/pull/#1990))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/dc4c450612eaf0969cb3bf024c05cd82145cacb9) to update your project

- fix Elasticsearch test reliability on fail ([#1662](https://github.com/shopsys/shopsys/pull/1662))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/a21e807175b2c2d69296cfee30ab2af61104165b) to update your project

- categories in admin are now loaded using admin locale ([#1982](https://github.com/shopsys/shopsys/pull/1982))
    - `CategoryRepository::getTranslatedAllWithoutBranch()` is deprecated, use `CategoryRepository::getAllTranslatedWithoutBranch()` instead
    - `CategoryRepository::getTranslatedAll` is deprecated, use `CategoryRepository::getAllTranslated()` instead
    - `CategoryFacade::getTranslatedAllWithoutBranch()` is deprecated, use `CategoryFacade::getAllTranslatedWithoutBranch()` instead
    - `CategoryFacade::getTranslatedAll` is deprecated, use `CategoryFacade::getAllTranslated()` instead
    - `ProductCategoryFilter::__construct()` has changed its interface and argument Domain will be removed in next major
    ```diff
    -   public function __construct(CategoryFacade $categoryFacade, Domain $domain = null)
    +   public function __construct(CategoryFacade $categoryFacade, ?Domain $domain = null, ?LocalizationAlias $localization = null)
    ```

- fix 500 error during logout when the user is already logged out ([#1909](https://github.com/shopsys/shopsys/pull/1909))
    - you might want to update your translations, because new translation message has been added for Czech and English language only

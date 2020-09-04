# [Upgrade from v7.3.4 to v7.3.5-dev](https://github.com/shopsys/shopsys/compare/v7.3.4...7.3)

This guide contains instructions to upgrade from version v7.3.4 to v7.3.5-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Application
- add `psr/event-dispatcher` to your composer dependencies in order to prevent PHPStan errors in Event classes ([#1894](https://github.com/shopsys/shopsys/pull/1894))
    - add `"psr/event-dispatcher": "0.6.0",` to `require-dev` section in your `composer.json` file

- enable automatic deleting of sessions older than 7 days in Redis ([#1842](https://github.com/shopsys/shopsys/pull/1842))
    - see #project-base-diff to update your project
    - you should consider what to do with current sessions, if you want to keep them, set them TTL or delete them

- add missing elasticsearch host to production docker-compose.yml file ([#1861](https://github.com/shopsys/shopsys/pull/1861))
    - see #project-base-diff to update your project

- fix login form validation is initialized too early ([#1906](https://github.com/shopsys/shopsys/pull/1906))
    - see #project-base-diff to update your project

- order can now be completed when successful flash message exists ([#1644](https://github.com/shopsys/shopsys/pull/1644))
    - see #project-base-diff to update your project

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
    - see #project-base-diff to update your project

- fix wrong url of freshly uploaded files in wysiwyg ([#1926](https://github.com/shopsys/shopsys/pull/1926))
    - see #project-base-diff to update your project

- fixed displaying errors in popup window ([#1970](https://github.com/shopsys/shopsys/pull/1970))
    - see #project-base-diff to update your project

- update your redis build-version to include application environment ([#1985](https://github.com/shopsys/shopsys/pull/#1985))
    - see #project-base-diff to update your project
    - run `php phing build-version-generate`

- fix spinbox increase value with set min-value ([#1979](https://github.com/shopsys/shopsys/pull/#1979))
    - see #project-base-diff to update your project

- load product accessories in after add window only if corresponding module is enabled ([#1990](https://github.com/shopsys/shopsys/pull/#1990))
    - see #project-base-diff to update your project

- fix Elasticsearch test reliability on fail ([#1662](https://github.com/shopsys/shopsys/pull/1662))
    - see #project-base-diff to update your project

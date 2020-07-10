# [Upgrade from v9.0.0 to v9.0.1-dev](https://github.com/shopsys/shopsys/compare/v9.0.0...9.0)

This guide contains instructions to upgrade from version v9.0.0 to v9.0.1-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/9.0/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Application
- add `psr/event-dispatcher` to your composer dependencies in order to prevent PHPStan errors in Event classes ([#1894](https://github.com/shopsys/shopsys/pull/1894))
    - add `"psr/event-dispatcher": "^1.0.0",` to `require-dev` section in your `composer.json` file

- fix not working upload of files in wysiwyg editor ([#1899](https://github.com/shopsys/shopsys/pull/1899))
    - see #project-base-diff to update your project

- add missing elasticsearch host to production docker-compose.yml file ([#1861](https://github.com/shopsys/shopsys/pull/1861))
    - see #project-base-diff to update your project

- fix login form validation is initialized too early ([#1906](https://github.com/shopsys/shopsys/pull/1906))
    - see #project-base-diff to update your project

- fix exporting of JavaScript translations ([#1880](https://github.com/shopsys/shopsys/pull/1880))
    - see #project-base-diff to update your project

- enable automatic deleting of sessions older than 7 days in Redis ([#1842](https://github.com/shopsys/shopsys/pull/1842))
    - see #project-base-diff to update your project
    - you should consider what to do with current sessions, if you want to keep them, set them TTL or delete them

- fix 500 error during logout when the user is already logged out ([#1909](https://github.com/shopsys/shopsys/pull/1909))
    - you might want to update your translations, because new translation message has been added for Czech and English language only

- restrict access to Admin > Pricing > Currencies only to superadmin ([#1338](https://github.com/shopsys/shopsys/pull/1338))
    - see #project-base-diff to upgrade your project 

- fix wrong translations in CartCest ([#1582](https://github.com/shopsys/shopsys/pull/1582))
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

- order can now be completed when successful flash message exists ([#1644](https://github.com/shopsys/shopsys/pull/1644))
    - see #project-base-diff to update your project

- add variant visibility on product detail ([#771](https://github.com/shopsys/shopsys/pull/771))
    - see #project-base-diff to update your project

- fix Window.js - default options are not overridden by jQuery.extend function anymore ([#1892](https://github.com/shopsys/shopsys/pull/1892))
    - see #project-base-diff to update your project

- optimization of ProductDataFixture ([#1810](https://github.com/shopsys/shopsys/pull/1810))
    - see #project-base-diff to update your project
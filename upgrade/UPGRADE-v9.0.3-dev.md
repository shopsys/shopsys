# [Upgrade from v9.0.2 to v9.0.3-dev](https://github.com/shopsys/shopsys/compare/v9.0.2...9.0)

This guide contains instructions to upgrade from version v9.0.2 to v9.0.3-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- fix path for importing easy coding standard resources ([#2026](https://github.com/shopsys/shopsys/pull/2026))
    - see #project-base-diff to update your project

- ProductOnCurrentDomainElasticFacade has been refactored ([#2036](https://github.com/shopsys/shopsys/pull/2036))
    - these methods are now deprecated:
        - `ProductOnCurrentDomainElasticFacade::createListableProductsInCategoryFilterQuery()` use `ProductFilterQueryFactory::createListableProductsByCategoryId()` instead
        - `ProductOnCurrentDomainElasticFacade::createListableProductsForBrandFilterQuery()` use `ProductFilterQueryFactory::createListableProductsByBrandId()` instead
        - `ProductOnCurrentDomainElasticFacade::createListableProductsForSearchTextFilterQuery()` use `ProductFilterQueryFactory::createListableProductsBySearchText()` instead
        - `ProductOnCurrentDomainElasticFacade::createFilterQueryWithProductFilterData()` use `ProductFilterQueryFactory::createWithProductFilterData()` instead
        - `ProductOnCurrentDomainElasticFacade::getIndexName()` use `ProductFilterQueryFactory::getIndexName()` instead

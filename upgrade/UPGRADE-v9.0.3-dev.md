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

- remove FE API only dependencies from framework ([#2041](https://github.com/shopsys/shopsys/pull/2041))
    - this methods has been marked as deprecated and will be removed in next major version:
        - `Shopsys\FrameworkBundle\Model\Product\ProductFacade::getSellableByUuid()` use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getSellableByUuid()` instead
        - `Shopsys\FrameworkBundle\Model\Product\ProductRepository::getSellableByUuid()` use `Shopsys\FrontendApiBundle\Model\Product\ProductRepository::getSellableByUuid()` instead
        - `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface::getProductFilterCountDataForSearch()` use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getProductFilterCountDataForSearch()` instead
        - `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface::getProductsOnCurrentDomain()` use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getProductsOnCurrentDomain()` instead
        - `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface::getProductsByCategory()` use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getProductsByCategory()` instead
        - `Shopsys\FrameworkBundle\Component\Image\ImageFacade::getImagesByEntityIdAndNameIndexedById()` use `Shopsys\FrontendApiBundle\Component\Image\ImageFacade::getImagesByEntityIdAndNameIndexedById()` instead

- fix webserver URL in frontend API tests ([#2045](https://github.com/shopsys/shopsys/pull/2045))
    - see #project-base-diff to update your project

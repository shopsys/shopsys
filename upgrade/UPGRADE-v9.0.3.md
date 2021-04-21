# [Upgrade from v9.0.2 to v9.0.3](https://github.com/shopsys/shopsys/compare/v9.0.2...v9.0.3)

This guide contains instructions to upgrade from version v9.0.2 to v9.0.3.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/9.0/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- fix path for importing easy coding standard resources ([#2026](https://github.com/shopsys/shopsys/pull/2026))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/02e7f2d20a40a7453f6f9039990088eb559014b6) to update your project

- ProductOnCurrentDomainElasticFacade has been refactored ([#2036](https://github.com/shopsys/shopsys/pull/2036))
    - these methods are now deprecated:
        - `ProductOnCurrentDomainElasticFacade::createListableProductsInCategoryFilterQuery()` use `FilterQueryFactory::createListableProductsByCategoryId()` instead
        - `ProductOnCurrentDomainElasticFacade::createListableProductsForBrandFilterQuery()` use `FilterQueryFactory::createListableProductsByBrandId()` instead
        - `ProductOnCurrentDomainElasticFacade::createListableProductsForSearchTextFilterQuery()` use `FilterQueryFactory::createListableProductsBySearchText()` instead
        - `ProductOnCurrentDomainElasticFacade::createFilterQueryWithProductFilterData()` use `FilterQueryFactory::createWithProductFilterData()` instead
        - `ProductOnCurrentDomainElasticFacade::getIndexName()` use `FilterQueryFactory::getIndexName()` instead

- remove FE API only dependencies from framework ([#2041](https://github.com/shopsys/shopsys/pull/2041))
    - these methods have been marked as deprecated and will be removed in next major version:
        - `Shopsys\FrameworkBundle\Model\Product\ProductFacade::getSellableByUuid()` use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getSellableByUuid()` instead
        - `Shopsys\FrameworkBundle\Model\Product\ProductRepository::getSellableByUuid()` use `Shopsys\FrontendApiBundle\Model\Product\ProductRepository::getSellableByUuid()` instead
        - `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface::getProductsCountOnCurrentDomain()` use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getProductsCountOnCurrentDomain()` instead
        - `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface::getProductsOnCurrentDomain()` use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getProductsOnCurrentDomain()` instead
        - `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface::getProductsByCategory()` use `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getProductsByCategory()` instead
        - `Shopsys\FrameworkBundle\Component\Image\ImageFacade::getImagesByEntityIdAndNameIndexedById()` use `Shopsys\FrontendApiBundle\Component\Image\ImageFacade::getImagesByEntityIdAndNameIndexedById()` instead

- fix webserver URL in frontend API tests ([#2045](https://github.com/shopsys/shopsys/pull/2045))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/bbdce13714696368a0ee71a597beeb6c306e880a) to update your project

- fix loading of multi design templates ([#2050](https://github.com/shopsys/shopsys/pull/2050))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/eed1a3a58938dc205a3e131829d90a0bae7e86e5) to update your project

# Front-end Product Filtering

Products can be by default filtered by price, flags, brand, parameters and in stock availability.

Filtering can be performed on category list and search results.  
These two pages are represented by `ProductController` and `SearchController`, where is used the interface (`Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface`) that describes common methods to get a filtered result:

-   `getPaginatedProductsInCategory()` to obtain filtered products in category
-   `getPaginatedProductsForSearch()` to obtain filtered products from search results

Currently, there is single implementation of `ProductOnCurrentDomainFacadeInterface`:

-   `ProductOnCurrentDomainElasticFacade`
    -   filters data through Elasticsearch
    -   much faster than filtering through SQL and remains fast independently on the number of selected filters

## Filtering through Elasticsearch

Behavior of the filter is defined in the class `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade`.

Each filtering method internally uses their own factory method `createProducts*FilterQuery` to create `Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery` object that represents the query for Elasticsearch.

Elasticsearch return a sorted list of product IDs and products itself are loaded from PostgreSQL.

Aggregation numbers are counted with help of Elasticsearch too thanks to methods `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade::getProductFilterCountDataInCategory` and
`Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade::getProductFilterCountDataForSearch`.

List of choices (exact parameters, brands, flags) is loaded from PostgreSQL as there is no benefit from loading them from Elasticsearch.

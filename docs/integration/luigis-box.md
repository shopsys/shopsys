# Luigi's Box

Luigi's Box is a set of e-commerce tools that help you to increase your sales by upgrading the shopping experience.
We use Luigi's Box to provide a better search experience in autocomplete and search results, including product filter on the search results page.
More information about Luigi's Box can be found on their [website](https://luigisbox.com/) or in their [documentation](https://docs.luigisbox.com/).

## Differences from the standard search

-   autocomplete does not include the number of found entities as Luigi's Box does not provide this information
-   search filter (e.g., price range) is limited by already selected filters

## Installation

You can find installation steps on the GitHub page of our [package](https://github.com/shopsys/luigis-box?tab=readme-ov-file#installation).
If you use the default Shopsys Platform installation, the package is already enabled by default.

## Search providers implementation

With the implementation of Luigi's Box, we have introduced search providers for easier switching between search implementations.

There is abstract class `SearchResultsProvider` that needs to be implemented for each search provider and provides functionality for enabling and disabling the search provider on specific domains.
This enables you to use different search providers for different domains, that could be helpful for A/B testing or for different approach on B2B and B2C domains.

Next, you need to use one of the existing search result provider interfaces:

-   `ArticlesSearchResultsProviderInterface`
-   `BrandSearchResultsProviderInterface`
-   `CategoriesSearchResultsProviderInterface`
-   `ProductSearchResultsProviderInterface`

Last you need to define your new provider in `services.yaml` and set priority of the provider.

```yaml
Shopsys\LuigisBoxBundle\Model\Product\ProductSearchResultsProvider:
    arguments:
        $enabledDomainIds: '%env(LUIGIS_BOX_ENABLED_DOMAIN_IDS)%'
    tags:
        - { name: 'shopsys.frontend_api.products_search_results_provider', priority: 100 }
```

The first provider of each type with the highest priority that is enabled on the domain will be used.

## Filters (facets) setting

Filtering functionality in Luigi's Box is provided via facets.
These provide filtering options and number of results for each option.
Luigi's Box support needs to set them up for you to make filters work correctly.
By default, these facets are used:

-   `availability_rank_text`
-   `brand`
-   `labels`
-   `price_amount`

# Luigi's Box

Luigi's Box is a set of e-commerce tools that help you to increase your sales by upgrading the shopping experience.
We use Luigi's Box to provide a better and personalized search experience in autocomplete and search results, including product filter on the search results page.
We also provide users with personalized recommended products on homepage, product detail and in the basket and basket popup window.
More information about Luigi's Box can be found on their [website](https://luigisbox.com/) or in their [documentation](https://docs.luigisbox.com/).

## Differences from the standard search

- autocomplete returns result lists, not paginated connections with total counts
- search filter (e.g., price range) is limited by already selected filters

## Installation

You can find installation steps on the GitHub page of our [package](https://github.com/shopsys/luigis-box?tab=readme-ov-file#installation).
If you use the default Shopsys Platform installation, the package is already installed.
To enable Luigi's Box for a domain, configure `LUIGIS_BOX_ENABLED_DOMAIN_IDS` and `LUIGIS_BOX_TRACKER_IDS_BY_DOMAIN_IDS`.

## Search providers implementation

With the implementation of Luigi's Box, we have introduced search providers for easier switching between search implementations.

There is abstract class `SearchResultsProvider` that needs to be implemented for each search provider and provides functionality for enabling and disabling the search provider on specific domains.
This enables you to use different search providers for different domains, that could be helpful for A/B testing or for different approach on B2B and B2C domains.

Next, you need to use one of the existing search result provider interfaces:

- `ArticlesSearchResultsProviderInterface`
- `BrandSearchResultsProviderInterface`
- `CategoriesSearchResultsProviderInterface`
- `ProductSearchResultsProviderInterface`

Last you need to define your new provider in `services.yaml` and set priority of the provider.

```yaml
Shopsys\LuigisBoxBundle\Model\Product\ProductSearchResultsProvider:
    arguments:
        $enabledDomainIds: '%env(LUIGIS_BOX_ENABLED_DOMAIN_IDS)%'
    tags:
        - { name: 'shopsys.frontend_api.products_search_results_provider', priority: 100 }
```

The first provider of each type with the highest priority that is enabled on the domain will be used.

## Search request behavior

Search results are loaded independently for each result type.
This means products, categories, articles, and brands can each use their own limit and total count, and secondary results are not limited by product search results.
Because Luigi's Box evaluates autocomplete and full search differently, autocomplete suggestions and search page results may differ for the same search phrase.
Each independently loaded result type on the search page is counted as a separate Luigi's Box request for billing purposes.

Autocomplete still returns a mixed list of result types for quick suggestions.

## Filters (facets) setting

Filtering functionality in Luigi's Box is provided via facets.
These provide filtering options and number of results for each option.
Luigi's Box support needs to set them up for you to make filters work correctly.
By default, these facets are used:

- `availability_rank_text`
- `brand`
- `labels`
- `price_amount`

### Parametric filter

Parametric filter uses Luigi's Box AI for suggesting the best filter options for the user based on filtered products.
You can read more about this in Luigi's Box [docs](https://docs.luigisbox.com/search/api.html#best-practices-use-dynamic-facets).

## SEO attributes in search

Luigi's Box feeds include SEO attributes (`seoTitle`, `seoMetaDescription`, `seoH1`) for products, categories, brands, and articles.
To leverage these attributes for search, contact Luigi's Box support and ask them to include these elements in search with partial matching enabled if desired.

## Recommended products

Luigi's Box uses data provided by searching and GTM to analyze user behavior and thanks to that provides personalized product recommendations.
These recommendations are displayed on the homepage, product detail, and in the basket and basket popup window.

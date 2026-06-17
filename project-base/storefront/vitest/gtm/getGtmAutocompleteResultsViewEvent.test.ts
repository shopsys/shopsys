import { TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { getGtmAutocompleteResultsViewEvent } from 'gtm/factories/getGtmAutocompleteResultsViewEvent';
import { describe, expect, test } from 'vitest';

const autocompleteSearchResult = {
    articlesSearch: [
        {
            __typename: 'ArticleSite',
            uuid: '92c929d6-0fc5-4df2-8dbd-b7a94b2d9629',
            name: 'Smart article',
            slug: '/smart-article',
            placement: 'footer',
            external: false,
        },
    ],
    brandSearch: [{ __typename: 'Brand', name: 'Smart brand', slug: '/smart-brand' }],
    categoriesSearch: {
        __typename: 'CategoryConnection',
        totalCount: 7,
        edges: [
            {
                __typename: 'CategoryEdge',
                node: {
                    __typename: 'Category',
                    uuid: 'd6034ed4-0c54-4f7f-ab98-0f0bff5e4af1',
                    name: 'Smart category',
                    slug: '/smart-category',
                },
            },
        ],
    },
    productsSearch: {
        __typename: 'ProductConnection',
        orderingMode: TypeProductOrderingModeEnum.Relevance,
        defaultOrderingMode: null,
        totalCount: 23,
        productFilterOptions: {
            __typename: 'ProductFilterOptions',
            minimalPrice: '0',
            maximalPrice: '0',
            inStock: 0,
            brands: null,
            flags: null,
            parameters: null,
        },
        pageInfo: { hasNextPage: true },
        edges: [{ __typename: 'ProductEdge', node: null }],
    },
} satisfies TypeAutocompleteSearchQuery;

describe('getGtmAutocompleteResultsViewEvent', () => {
    test('should count found autocomplete results instead of displayed results', () => {
        const result = getGtmAutocompleteResultsViewEvent(autocompleteSearchResult, 'smart');

        expect(result).toEqual({
            event: GtmEventType.autocomplete_results_view,
            autocompleteResults: {
                keyword: 'smart',
                results: 32,
                sections: {
                    category: 7,
                    product: 23,
                    brand: 1,
                    article: 1,
                },
            },
            _clear: true,
        });
    });

    test('should fall back to displayed results count when found results count is not provided by the search provider', () => {
        const result = getGtmAutocompleteResultsViewEvent(
            {
                ...autocompleteSearchResult,
                categoriesSearch: {
                    ...autocompleteSearchResult.categoriesSearch,
                    totalCount: -1,
                },
                productsSearch: {
                    ...autocompleteSearchResult.productsSearch,
                    totalCount: -1,
                },
            },
            'smart',
        );

        expect(result).toMatchObject({
            autocompleteResults: {
                results: 4,
                sections: {
                    category: 1,
                    product: 1,
                    brand: 1,
                    article: 1,
                },
            },
        });
    });
});

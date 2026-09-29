import { Kind } from 'graphql';
import { AutocompleteSearchQueryDocument } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { SearchProductsQueryDocument } from 'graphql/requests/search/queries/SearchProductsQuery.generated';
import { describe, expect, test } from 'vitest';
import { getFields, getSelectionAt } from 'vitest/helpers/graphqlSelections';

describe('autocomplete product data requirements', () => {
    test('fetches products and result count without catalog filters, ordering or pagination metadata', () => {
        const fields = getFields(
            AutocompleteSearchQueryDocument,
            getSelectionAt(AutocompleteSearchQueryDocument, ['productsSearch']),
        );

        expect(fields.map((field) => field.name.value).sort()).toEqual(['__typename', 'edges', 'totalCount']);
    });

    test('keeps filtering, ordering and pagination available to the full search listing', () => {
        const fields = getFields(
            SearchProductsQueryDocument,
            getSelectionAt(SearchProductsQueryDocument, ['productsSearch']),
        );

        expect(fields.map((field) => field.name.value)).toEqual(
            expect.arrayContaining([
                'orderingMode',
                'defaultOrderingMode',
                'productFilterOptions',
                'pageInfo',
                'totalCount',
            ]),
        );
    });

    test.each([
        ['autocomplete', AutocompleteSearchQueryDocument, 'CompactProductFragment'],
        ['full search', SearchProductsQueryDocument, 'ListedProductFragment'],
    ])('%s reuses the product card contract instead of maintaining another field list', (_, document, fragmentName) => {
        const nodeSelection = getSelectionAt(document, ['productsSearch', 'edges', 'node']);

        const fragmentNames = nodeSelection.selections
            .filter((selection) => selection.kind === Kind.FRAGMENT_SPREAD)
            .map((selection) => selection.name.value);

        expect(fragmentNames).toContain(fragmentName);
    });
});

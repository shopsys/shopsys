import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { AutocompleteSearchPopup } from 'components/Layout/Header/AutocompleteSearch/AutocompleteSearchPopup';
import { type TypeAutocompleteSearchQuery } from 'graphql/requests/search/queries/AutocompleteSearchQuery.generated';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

const mockRouterPush = vi.fn();

vi.mock('next/router', () => ({
    useRouter: () => ({ push: mockRouterPush }),
}));

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => key,
    }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (urls: string[]) => urls,
}));

vi.mock('components/Layout/Header/AutocompleteSearch/AutocompleteSearchBrandsResult', () => ({
    AutocompleteSearchBrandsResult: () => <div>Brand results</div>,
}));

const autocompleteSearchResults = {
    articlesSearch: [],
    brandSearch: [{ __typename: 'Brand', name: 'Apple', slug: '/apple' }],
    categoriesSearch: { __typename: 'CategoryConnection', totalCount: 0, edges: [] },
    productsSearch: {
        __typename: 'ProductConnection',
        defaultOrderingMode: null,
        edges: [],
        orderingMode: TypeProductOrderingModeEnum.Relevance,
        pageInfo: { hasNextPage: false },
        productFilterOptions: {
            __typename: 'ProductFilterOptions',
            brands: [],
            flags: [],
            inStock: 0,
            maximalPrice: '0',
            minimalPrice: '0',
            parameters: [],
        },
        totalCount: 0,
    },
} satisfies TypeAutocompleteSearchQuery;

describe('AutocompleteSearchPopup', () => {
    test('closes the mobile search overlay when navigating to all results', async () => {
        const user = userEvent.setup();
        const onClosePopupCallback = vi.fn();
        const onSearchSubmit = vi.fn();

        render(
            <AutocompleteSearchPopup
                areAutocompleteSearchDataFetching={false}
                autocompleteSearchQueryValue="apple"
                autocompleteSearchResults={autocompleteSearchResults}
                favoritesData={undefined}
                showFavorites={false}
                onClosePopupCallback={onClosePopupCallback}
                onSearchSubmit={onSearchSubmit}
            />,
        );

        await user.click(screen.getByRole('button', { name: 'View all results' }));

        expect(onClosePopupCallback).toHaveBeenCalledOnce();
        expect(onSearchSubmit).toHaveBeenCalledOnce();
        expect(mockRouterPush).toHaveBeenCalledWith({ pathname: '/search', query: { q: 'apple' } });
    });
});

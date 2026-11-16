import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { AutocompleteSearch } from 'components/Layout/Header/AutocompleteSearch/AutocompleteSearch';
import { CookiesStoreProvider } from 'components/providers/CookiesStoreProvider';
import {
    type TypeAutocompleteFavoritesQuery,
    useAutocompleteFavoritesQuery,
} from 'graphql/requests/autocomplete/queries/AutocompleteFavoritesQuery.generated';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { createProduct } from 'vitest/helpers/productFixture';

vi.mock('graphql/requests/autocomplete/queries/AutocompleteFavoritesQuery.generated', () => ({
    useAutocompleteFavoritesQuery: vi.fn(),
}));

vi.mock('graphql/requests/search/queries/AutocompleteSearchQuery.generated', () => ({
    useAutocompleteSearchQuery: () => [{ fetching: false }],
}));

vi.mock('next/dynamic', () => ({
    default:
        () =>
        ({ showFavorites }: { showFavorites: boolean }) => (
            <div data-testid="autocomplete-popup">{showFavorites ? 'Favorites' : 'Search results'}</div>
        ),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({ push: vi.fn() }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (urls: string[]) => urls,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('gtm/utils/pageReadyEvents/useGtmAutocompleteResultsViewEvent', () => ({
    useGtmAutocompleteResultsViewEvent: vi.fn(),
}));

const emptyFavorites = {
    products: [],
    categories: [],
    brands: [],
} satisfies TypeAutocompleteFavoritesQuery['autocompleteFavorites'];

const mockFavorites = (autocompleteFavorites: TypeAutocompleteFavoritesQuery['autocompleteFavorites']) => {
    vi.mocked(useAutocompleteFavoritesQuery).mockReturnValue([
        { data: { autocompleteFavorites }, fetching: false, stale: false, hasNext: false },
        vi.fn(),
    ]);
};

describe('AutocompleteSearch popup visibility', () => {
    beforeEach(() => {
        mockFavorites(emptyFavorites);
    });

    test('does not open an empty recommendations panel on mobile mount or input click', async () => {
        const user = userEvent.setup();
        render(<AutocompleteSearch shouldOpenPopupOnMount shouldRenderResultsOverlay={false} />, {
            wrapper: CookiesStoreProvider,
        });

        expect(screen.queryByTestId('autocomplete-popup')).not.toBeInTheDocument();

        await user.click(screen.getByRole('searchbox'));

        expect(screen.queryByTestId('autocomplete-popup')).not.toBeInTheDocument();
    });

    test.each([
        ['products', { ...emptyFavorites, products: [createProduct('recommended-product')] }],
        [
            'categories',
            {
                ...emptyFavorites,
                categories: [{ __typename: 'Category', uuid: 'category', name: 'Electronics', slug: '/electronics' }],
            },
        ],
        ['brands', { ...emptyFavorites, brands: [{ __typename: 'Brand', name: 'Apple', slug: '/apple' }] }],
    ] satisfies [string, TypeAutocompleteFavoritesQuery['autocompleteFavorites']][])(
        'opens recommendations on mobile when only %s are configured',
        async (_, favorites) => {
            mockFavorites(favorites);
            render(<AutocompleteSearch shouldOpenPopupOnMount shouldRenderResultsOverlay={false} />, {
                wrapper: CookiesStoreProvider,
            });

            expect(await screen.findByTestId('autocomplete-popup')).toHaveTextContent('Favorites');
        },
    );

    test('opens search results without recommendations and hides the panel after clearing the query', async () => {
        const user = userEvent.setup();
        render(<AutocompleteSearch shouldOpenPopupOnMount shouldRenderResultsOverlay={false} />, {
            wrapper: CookiesStoreProvider,
        });
        const input = screen.getByRole('searchbox');
        expect(input).toHaveValue('');
        expect(screen.queryByTestId('autocomplete-popup')).not.toBeInTheDocument();

        await user.type(input, 'apple');

        expect(await screen.findByTestId('autocomplete-popup')).toHaveTextContent('Search results');

        await user.click(screen.getByRole('button', { name: 'Clear search input' }));

        expect(input).toHaveValue('');
        expect(screen.queryByTestId('autocomplete-popup')).not.toBeInTheDocument();
    });
});

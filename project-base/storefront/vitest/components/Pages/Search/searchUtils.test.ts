import { act, renderHook, waitFor } from '@testing-library/react';
import { useSearchProductsData, useSearchQuery } from 'components/Pages/Search/searchUtils';
import { TypeSearchProductsQuery } from 'graphql/requests/search/queries/SearchProductsQuery.generated';
import { TypeSearchQuery } from 'graphql/requests/search/queries/SearchQuery.generated';
import { FilterQueries } from 'types/urlQueries';
import { beforeEach, describe, expect, test, vi } from 'vitest';

type SearchResponse = {
    data: TypeSearchQuery;
    error?: undefined;
};

type SearchProductsResponse = {
    data: TypeSearchProductsQuery;
};

const testState = vi.hoisted(() => {
    const query = vi.fn();
    const readQuery = vi.fn();

    return {
        client: { query, readQuery },
        currentFilter: null as FilterQueries | null,
        currentLoadMore: 0,
        currentSearchString: 'phone',
        query,
        readQuery,
        requestResolvers: [] as Array<(response: SearchResponse) => void>,
        searchProductsRequestResolvers: [] as Array<(response: SearchProductsResponse) => void>,
    };
});

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({ replace: vi.fn() }),
}));

vi.mock('store/useCookiesStore', () => ({
    useCookiesStore: (selector: (state: { userIdentifier: string }) => string) =>
        selector({ userIdentifier: 'user-identifier' }),
}));

vi.mock('urql', () => ({
    useClient: () => testState.client,
}));

vi.mock('utils/errors/expectedErrors', () => ({
    isExpectedPriceFilterError: () => false,
}));

vi.mock('utils/queryParams/useCurrentFilterQuery', () => ({
    useCurrentFilterQuery: () => testState.currentFilter,
}));

vi.mock('utils/queryParams/useCurrentLoadMoreQuery', () => ({
    useCurrentLoadMoreQuery: () => testState.currentLoadMore,
}));

vi.mock('utils/queryParams/useCurrentPageQuery', () => ({
    useCurrentPageQuery: () => 1,
}));

vi.mock('utils/queryParams/useCurrentSearchStringQuery', () => ({
    useCurrentSearchStringQuery: () => testState.currentSearchString,
}));

vi.mock('utils/queryParams/useCurrentSortQuery', () => ({
    useCurrentSortQuery: () => null,
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: () => ['/search'],
}));

const createSearchData = (identifier: string) => ({ identifier }) as unknown as TypeSearchQuery;
const createSearchProductsData = (identifier: string) =>
    ({ productsSearch: { identifier } }) as unknown as TypeSearchProductsQuery;

const resolveRequest = async (index: number, data: TypeSearchQuery) => {
    await act(async () => {
        testState.requestResolvers[index]({ data });
    });
};

const resolveSearchProductsRequest = async (index: number, data: TypeSearchProductsQuery) => {
    await act(async () => {
        testState.searchProductsRequestResolvers[index]({ data });
    });
};

describe('useSearchQuery', () => {
    beforeEach(() => {
        testState.currentFilter = null;
        testState.currentLoadMore = 0;
        testState.requestResolvers = [];
        testState.query.mockReset();
        testState.query.mockImplementation(
            () =>
                new Promise<SearchResponse>((resolve) => {
                    testState.requestResolvers.push(resolve);
                }),
        );
    });

    test('keeps the current search page rendered while filters are refetched', async () => {
        const initialSearchData = createSearchData('initial');
        const filteredSearchData = createSearchData('filtered');
        const { result, rerender } = renderHook(() => useSearchQuery('phone'));
        await waitFor(() => expect(testState.query).toHaveBeenCalledTimes(1));
        await resolveRequest(0, initialSearchData);
        await waitFor(() => expect(result.current.isSearchPageFetching).toBe(false));

        testState.currentFilter = { brands: ['brand-uuid'] };
        rerender();
        await waitFor(() => expect(testState.query).toHaveBeenCalledTimes(2));

        expect(result.current.isSearchPageFetching).toBe(false);
        expect(result.current.searchData).toBe(initialSearchData);

        await resolveRequest(1, filteredSearchData);
        await waitFor(() => expect(result.current.searchData).toBe(filteredSearchData));
    });

    test('shows the page skeleton when the search string changes', async () => {
        const initialSearchData = createSearchData('initial');
        const updatedSearchData = createSearchData('updated');
        const { result, rerender } = renderHook(({ searchString }) => useSearchQuery(searchString), {
            initialProps: { searchString: 'phone' },
        });
        await waitFor(() => expect(testState.query).toHaveBeenCalledTimes(1));
        await resolveRequest(0, initialSearchData);
        await waitFor(() => expect(result.current.isSearchPageFetching).toBe(false));

        rerender({ searchString: 'laptop' });

        expect(result.current.isSearchPageFetching).toBe(true);
        await waitFor(() => expect(testState.query).toHaveBeenCalledTimes(2));

        await resolveRequest(1, updatedSearchData);
        await waitFor(() => expect(result.current.isSearchPageFetching).toBe(false));
        expect(result.current.searchData).toBe(updatedSearchData);
    });

    test('ignores an obsolete response when the search string changes', async () => {
        const obsoleteSearchData = createSearchData('obsolete');
        const currentSearchData = createSearchData('current');
        const { result, rerender } = renderHook(({ searchString }) => useSearchQuery(searchString), {
            initialProps: { searchString: 'phone' },
        });
        await waitFor(() => expect(testState.query).toHaveBeenCalledTimes(1));

        rerender({ searchString: 'laptop' });
        await waitFor(() => expect(testState.query).toHaveBeenCalledTimes(2));
        await resolveRequest(1, currentSearchData);
        await waitFor(() => expect(result.current.isSearchPageFetching).toBe(false));
        await resolveRequest(0, obsoleteSearchData);

        expect(result.current.searchData).toBe(currentSearchData);
        expect(result.current.isSearchPageFetching).toBe(false);
    });
});

describe('useSearchProductsData', () => {
    beforeEach(() => {
        testState.currentFilter = null;
        testState.currentLoadMore = 0;
        testState.currentSearchString = 'phone';
        testState.searchProductsRequestResolvers = [];
        testState.query.mockReset();
        testState.readQuery.mockReset();
        testState.readQuery.mockReturnValue(undefined);
        testState.query.mockImplementation(() => ({
            toPromise: () =>
                new Promise<SearchProductsResponse>((resolve) => {
                    testState.searchProductsRequestResolvers.push(resolve);
                }),
        }));
    });

    test('uses initial search products without an additional query', () => {
        const initialSearchProductsData = createSearchProductsData('initial');

        const { result } = renderHook(() =>
            useSearchProductsData({
                searchProductsDataFromMainQuery: initialSearchProductsData.productsSearch,
            }),
        );

        expect(result.current.searchProductsData).toMatchObject({ identifier: 'initial' });
        expect(result.current.areSearchProductsFetching).toBe(false);
        expect(testState.query).not.toHaveBeenCalled();
    });

    test('fetches additional products when load more changes', async () => {
        const initialSearchProductsData = createSearchProductsData('initial');
        const { result, rerender } = renderHook(() =>
            useSearchProductsData({
                searchProductsDataFromMainQuery: initialSearchProductsData.productsSearch,
            }),
        );
        expect(testState.query).not.toHaveBeenCalled();

        testState.currentLoadMore = 1;
        rerender();

        await waitFor(() => expect(testState.query).toHaveBeenCalledTimes(1));
        expect(result.current.isLoadingMoreSearchProducts).toBe(true);
    });

    test('uses updated main query products after filters change without an additional query', async () => {
        const initialSearchProductsData = createSearchProductsData('initial');
        const filteredSearchProductsData = createSearchProductsData('filtered');
        const { result, rerender } = renderHook(
            ({ searchProductsDataFromMainQuery }) => useSearchProductsData({ searchProductsDataFromMainQuery }),
            {
                initialProps: {
                    searchProductsDataFromMainQuery: initialSearchProductsData.productsSearch,
                },
            },
        );

        testState.currentFilter = { brands: ['brand-uuid'] };
        rerender({ searchProductsDataFromMainQuery: initialSearchProductsData.productsSearch });
        expect(result.current.areSearchProductsFetching).toBe(true);

        rerender({ searchProductsDataFromMainQuery: filteredSearchProductsData.productsSearch });
        await waitFor(() => expect(result.current.searchProductsData).toMatchObject({ identifier: 'filtered' }));

        expect(result.current.areSearchProductsFetching).toBe(false);
        expect(testState.query).not.toHaveBeenCalled();
    });

    test('ignores an obsolete response when filters change', async () => {
        const obsoleteSearchProductsData = createSearchProductsData('obsolete');
        const currentSearchProductsData = createSearchProductsData('current');
        const { result, rerender } = renderHook(() => useSearchProductsData());
        await waitFor(() => expect(testState.query).toHaveBeenCalledTimes(1));

        testState.currentFilter = { brands: ['brand-uuid'] };
        rerender();
        await waitFor(() => expect(testState.query).toHaveBeenCalledTimes(2));
        await resolveSearchProductsRequest(1, currentSearchProductsData);
        await waitFor(() => expect(result.current.searchProductsData).toMatchObject({ identifier: 'current' }));
        await resolveSearchProductsRequest(0, obsoleteSearchProductsData);

        expect(result.current.searchProductsData).toMatchObject({ identifier: 'current' });
        expect(result.current.areSearchProductsFetching).toBe(false);
    });
});

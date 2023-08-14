import { ProductOrderingModeEnumApi } from 'graphql/generated';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/useSessionStore';
import { describe, expect, Mock, test, vi } from 'vitest';

const mockSeoSensitiveFiltersGetter = vi.fn(() => ({
    SORT: true,
    AVAILABILITY: false,
    PRICE: false,
    FLAGS: true,
    PARAMETERS: {
        CHECKBOX: true,
        SLIDER: false,
    },
}));

const CATEGORY_URL = '/category-url';
const CATEGORY_PATHNAME = '/categories/[categorySlug]';
const ORIGINAL_CATEGORY_URL = '/original-category-slug';
const DEFAULT_SEO_CATEGORY_PARAMETERS = new Map([
    ['default-parameter-1', new Set(['default-parameter-value-1', 'default-parameter-value-2'])],
    ['default-parameter-2', new Set(['default-parameter-value-3', 'default-parameter-value-4'])],
]);
const DEFAULT_SEO_CATEGORY_FLAGS = new Set(['default-flag-1', 'default-flag-2']);

vi.mock('helpers/filterOptions/seoCategories', async (importOriginal) => {
    const actualSeoCategoriesModule = await importOriginal<any>();

    return {
        ...actualSeoCategoriesModule,
        get SEO_SENSITIVE_FILTERS() {
            return mockSeoSensitiveFiltersGetter();
        },
    };
});

const mockPush = vi.fn();
vi.mock('next/router', () => ({
    useRouter: vi.fn(() => ({
        pathname: CATEGORY_PATHNAME,
        asPath: CATEGORY_URL,
        push: mockPush,
        query: {},
    })),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: vi.fn((selector) => {
        return selector({
            defaultProductFiltersMap: {
                flags: new Set(),
                sort: ProductOrderingModeEnumApi.PriorityApi,
                parameters: new Map(),
            },
            originalCategorySlug: null,
        });
    }),
}));

describe('useQueryParams().updateFilterPrices tests', () => {
    test('only minimalPrice should be updated if it differs from the current minimal price', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ minimalPrice: 100, maximalPrice: 1000 }) },
        }));

        useQueryParams().updateFilterPrices({ minimalPrice: 150, maximalPrice: 1000 });

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ minimalPrice: 150, maximalPrice: 1000 }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ minimalPrice: 150, maximalPrice: 1000 }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('only maximalPrice should be updated if it differs from the current maximal price', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ minimalPrice: 100, maximalPrice: 1000 }) },
        }));

        useQueryParams().updateFilterPrices({ minimalPrice: 100, maximalPrice: 1100 });

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ minimalPrice: 100, maximalPrice: 1100 }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ minimalPrice: 100, maximalPrice: 1100 }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('both minimalPrice and maximalPrice should be updated if they differs from the current values', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ minimalPrice: 100, maximalPrice: 1000 }) },
        }));

        useQueryParams().updateFilterPrices({ minimalPrice: 150, maximalPrice: 1100 });

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ minimalPrice: 150, maximalPrice: 1100 }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ minimalPrice: 150, maximalPrice: 1100 }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('minimalPrice should be reset if it is set to undefined', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ minimalPrice: 100, maximalPrice: 1000 }) },
        }));

        useQueryParams().updateFilterPrices({ minimalPrice: undefined, maximalPrice: 1000 });

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ maximalPrice: 1000 }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ maximalPrice: 1000 }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('changing price filters should not redirect from SEO category if it is not SEO-sensitive', () => {
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: ProductOrderingModeEnumApi.PriceAscApi,
                    flags: DEFAULT_SEO_CATEGORY_FLAGS,
                    parameters: DEFAULT_SEO_CATEGORY_PARAMETERS,
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
            });
        });

        useQueryParams().updateFilterPrices({ minimalPrice: 100, maximalPrice: 1000 });

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        minimalPrice: 100,
                        maximalPrice: 1000,
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        minimalPrice: 100,
                        maximalPrice: 1000,
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('changing price filters should redirect from SEO category if it is SEO-sensitive', () => {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ PRICE: true }));
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: ProductOrderingModeEnumApi.PriceAscApi,
                    flags: DEFAULT_SEO_CATEGORY_FLAGS,
                    parameters: DEFAULT_SEO_CATEGORY_PARAMETERS,
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
            });
        });

        useQueryParams().updateFilterPrices({ minimalPrice: 100, maximalPrice: 1000 });

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: ORIGINAL_CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        minimalPrice: 100,
                        maximalPrice: 1000,
                        flags: ['default-flag-1', 'default-flag-2'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceAscApi,
                },
            },
            {
                pathname: ORIGINAL_CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        minimalPrice: 100,
                        maximalPrice: 1000,
                        flags: ['default-flag-1', 'default-flag-2'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceAscApi,
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('changing prices resets page and load more', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [PAGE_QUERY_PARAMETER_NAME]: '2',
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '2',
            },
        }));

        useQueryParams().updateFilterPrices({ minimalPrice: 100, maximalPrice: 1000 });

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        minimalPrice: 100,
                        maximalPrice: 1000,
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        minimalPrice: 100,
                        maximalPrice: 1000,
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });
});

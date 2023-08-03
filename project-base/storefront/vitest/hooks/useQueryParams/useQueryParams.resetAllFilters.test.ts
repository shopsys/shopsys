import { ProductOrderingModeEnumApi } from 'graphql/generated';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/zustand/useSessionStore';
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

const mockDefaultSort = vi.fn(() => ProductOrderingModeEnumApi.PriorityApi);
vi.mock('helpers/filterOptions/seoCategories', async (importOriginal) => {
    const actualSeoCategoriesModule = await importOriginal<any>();

    return {
        ...actualSeoCategoriesModule,
        get DEFAULT_SORT() {
            return mockDefaultSort();
        },
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

vi.mock('store/zustand/useSessionStore', () => ({
    useSessionStore: vi.fn((selector) => {
        return selector({
            defaultProductFiltersMap: {
                flags: new Set(),
                sort: mockDefaultSort(),
                parameters: new Map(),
            },
            originalCategorySlug: null,
        });
    }),
}));

describe('useQueryParams().resetAllFilters tests', () => {
    test('resetting all filters should clear filter, load more, and page from query', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                    onlyInStock: true,
                    minimalPrice: 100,
                    maximalPrice: 1000,
                    brands: ['default-brand-1', 'default-brand-2'],
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
                [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceDescApi,
                [PAGE_QUERY_PARAMETER_NAME]: '2',
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '2',
            },
        }));

        useQueryParams().resetAllFilters();

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceDescApi,
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceDescApi,
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('resetting all filters should redirect from SEO category', () => {
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

        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                    onlyInStock: true,
                    minimalPrice: 100,
                    maximalPrice: 1000,
                    brands: ['default-brand-1', 'default-brand-2'],
                }),
                [PAGE_QUERY_PARAMETER_NAME]: '2',
            },
        }));

        useQueryParams().resetAllFilters();

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: ORIGINAL_CATEGORY_URL,
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceAscApi,
                },
            },
            {
                pathname: ORIGINAL_CATEGORY_URL,
                query: {
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceAscApi,
                },
            },
            {
                shallow: true,
            },
        );
    });
});

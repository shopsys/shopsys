import { ProductOrderingModeEnum } from 'graphql/types';
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

const CATEGORY_URL = '/category-url';
const CATEGORY_PATHNAME = '/categories/[categorySlug]';
const ORIGINAL_CATEGORY_URL = '/original-category-slug';
const GET_DEFAULT_SEO_CATEGORY_PARAMETERS = () =>
    new Map([
        ['default-parameter-1', new Set(['default-parameter-value-1', 'default-parameter-value-2'])],
        ['default-parameter-2', new Set(['default-parameter-value-3', 'default-parameter-value-4'])],
    ]);
const GET_DEFAULT_SEO_CATEGORY_FLAGS = () => new Set(['default-flag-1', 'default-flag-2']);
const GET_DEFAULT_SEO_CATEGORY_BRANDS = () => new Set(['default-brand-1', 'default-brand-2']);

const mockDefaultSort = vi.fn(() => ProductOrderingModeEnum.Priority);
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
const setWasRedirectedFromSeoCategoryMock = vi.fn();
vi.mock('config/constants', async (importOriginal) => {
    const actualConstantsModule = await importOriginal<any>();

    return {
        ...actualConstantsModule,
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

vi.mock('store/useSessionStore', () => ({
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
                    flags: Array.from(GET_DEFAULT_SEO_CATEGORY_FLAGS()),
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
                [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnum.PriceDesc,
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
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnum.PriceDesc,
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnum.PriceDesc,
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
                    sort: ProductOrderingModeEnum.PriceAsc,
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
                setWasRedirectedFromSeoCategory: setWasRedirectedFromSeoCategoryMock,
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
                    brands: Array.from(GET_DEFAULT_SEO_CATEGORY_BRANDS()),
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
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnum.PriceAsc,
                },
            },
            {
                pathname: ORIGINAL_CATEGORY_URL,
                query: {
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnum.PriceAsc,
                },
            },
            {
                shallow: true,
            },
        );
        expect(setWasRedirectedFromSeoCategoryMock).toBeCalledTimes(1);
        expect(setWasRedirectedFromSeoCategoryMock).toBeCalledWith(true);
    });
});

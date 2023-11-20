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

vi.mock('config/constants', async (importOriginal) => {
    const actualConstantsModule = await importOriginal<any>();

    return {
        ...actualConstantsModule,
        get SEO_SENSITIVE_FILTERS() {
            return mockSeoSensitiveFiltersGetter();
        },
    };
});

describe('useQueryParams().updateFilterBrands tests', () => {
    test('brand should be added to query if not present', () => {
        useQueryParams().updateFilterBrands('test-brand');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('brand should be removed from query if already present', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ brands: ['test-brand'] }) },
        }));

        useQueryParams().updateFilterBrands('test-brand');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: { categorySlug: CATEGORY_URL },
            },
            {
                pathname: CATEGORY_URL,
                query: {},
            },
            {
                shallow: true,
            },
        );
    });

    test('changing brands resets page and load more', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [PAGE_QUERY_PARAMETER_NAME]: '2',
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '2',
            },
        }));

        useQueryParams().updateFilterBrands('test-brand');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('changing brand should not redirect from SEO category if brands are not SEO-sensitive', () => {
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: ProductOrderingModeEnumApi.PriceAscApi,
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    brands: GET_DEFAULT_SEO_CATEGORY_BRANDS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
            });
        });

        useQueryParams().updateFilterBrands('test-brand');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('changing brand should redirect from SEO category if brands are SEO-sensitive', () => {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ BRANDS: true }));
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: ProductOrderingModeEnumApi.PriceAscApi,
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    brands: GET_DEFAULT_SEO_CATEGORY_BRANDS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
            });
        });

        useQueryParams().updateFilterBrands('test-brand');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: ORIGINAL_CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: [...Array.from(GET_DEFAULT_SEO_CATEGORY_BRANDS()), 'test-brand'],
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
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceAscApi,
                },
            },
            {
                pathname: ORIGINAL_CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: [...Array.from(GET_DEFAULT_SEO_CATEGORY_BRANDS()), 'test-brand'],
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
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceAscApi,
                },
            },
            {
                shallow: true,
            },
        );
    });
});

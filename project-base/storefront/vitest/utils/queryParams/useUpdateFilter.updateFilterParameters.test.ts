import { ProductOrderingModeEnum } from 'graphql/types';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/useSessionStore';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';
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
                sort: ProductOrderingModeEnum.Priority,
                parameters: new Map(),
            },
            originalCategorySlug: null,
        });
    }),
}));

describe('useUpdateFilter().updateFilterParameters tests', () => {
    test('checkbox parameter value should be added to query if parameter is already present but value is not', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                    parameters: [
                        {
                            parameter: 'default-parameter-1',
                            values: ['default-parameter-value-1'],
                        },
                        {
                            parameter: 'default-parameter-2',
                            values: ['default-parameter-value-3', 'default-parameter-value-4'],
                        },
                    ],
                }),
            },
        }));

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-1', 'default-parameter-value-2');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
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
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
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
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('checkbox parameter should be added to query if not present', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                    parameters: [
                        {
                            parameter: 'default-parameter-1',
                            values: ['default-parameter-value-1', 'default-parameter-value-2'],
                        },
                    ],
                }),
            },
        }));

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-2', 'default-parameter-value-3');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3'],
                            },
                        ],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3'],
                            },
                        ],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('checkbox parameter value should be removed from query if parameter and value are both present', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
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
            },
        }));

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-2', 'default-parameter-value-4');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3'],
                            },
                        ],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3'],
                            },
                        ],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('checkbox parameter should be removed from query if parameter and value are both present and removed value is the only one', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                    parameters: [
                        {
                            parameter: 'default-parameter-1',
                            values: ['default-parameter-value-1', 'default-parameter-value-2'],
                        },
                        {
                            parameter: 'default-parameter-2',
                            values: ['default-parameter-value-3'],
                        },
                    ],
                }),
            },
        }));

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-2', 'default-parameter-value-3');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                        ],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                        ],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('slider parameter should be added to query if not present', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                    parameters: [
                        {
                            parameter: 'default-parameter-1',
                            minimalValue: 100,
                            maximalValue: 1000,
                        },
                    ],
                }),
            },
        }));

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-2', undefined, 200, 2000);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                minimalValue: 100,
                                maximalValue: 1000,
                            },
                            {
                                parameter: 'default-parameter-2',
                                minimalValue: 200,
                                maximalValue: 2000,
                            },
                        ],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                minimalValue: 100,
                                maximalValue: 1000,
                            },
                            {
                                parameter: 'default-parameter-2',
                                minimalValue: 200,
                                maximalValue: 2000,
                            },
                        ],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('slider parameter should be updated if its values change', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                    parameters: [
                        {
                            parameter: 'default-parameter-1',
                            minimalValue: 100,
                            maximalValue: 1000,
                        },
                        {
                            parameter: 'default-parameter-2',
                            minimalValue: 200,
                            maximalValue: 2000,
                        },
                    ],
                }),
            },
        }));

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-2', undefined, 300, 3000);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                minimalValue: 100,
                                maximalValue: 1000,
                            },
                            {
                                parameter: 'default-parameter-2',
                                minimalValue: 300,
                                maximalValue: 3000,
                            },
                        ],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                minimalValue: 100,
                                maximalValue: 1000,
                            },
                            {
                                parameter: 'default-parameter-2',
                                minimalValue: 300,
                                maximalValue: 3000,
                            },
                        ],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('slider parameter should be removed from query if already present and values are set to undefined', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                    parameters: [
                        {
                            parameter: 'default-parameter-1',
                            minimalValue: 100,
                            maximalValue: 1000,
                        },
                        {
                            parameter: 'default-parameter-2',
                            minimalValue: 200,
                            maximalValue: 2000,
                        },
                    ],
                }),
            },
        }));

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-2', undefined, undefined, undefined);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                minimalValue: 100,
                                maximalValue: 1000,
                            },
                        ],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                minimalValue: 100,
                                maximalValue: 1000,
                            },
                        ],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('changing checkbox parameter should redirect from SEO category if checkbox parameters are SEO-sensitive', () => {
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: ProductOrderingModeEnum.PriceAsc,
                    brands: GET_DEFAULT_SEO_CATEGORY_BRANDS(),
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
                setWasRedirectedFromSeoCategory: setWasRedirectedFromSeoCategoryMock,
            });
        });

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-2', 'default-parameter-value-4');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: ORIGINAL_CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: Array.from(GET_DEFAULT_SEO_CATEGORY_BRANDS()),
                        flags: Array.from(GET_DEFAULT_SEO_CATEGORY_FLAGS()),
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3'],
                            },
                        ],
                    }),
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnum.PriceAsc,
                },
            },
            {
                pathname: ORIGINAL_CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: Array.from(GET_DEFAULT_SEO_CATEGORY_BRANDS()),
                        flags: Array.from(GET_DEFAULT_SEO_CATEGORY_FLAGS()),
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3'],
                            },
                        ],
                    }),
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

    test('changing checkbox parameter should not redirect from SEO category if checkbox parameters are not SEO-sensitive', () => {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ PARAMETERS: { CHECKBOX: false } }));
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: ProductOrderingModeEnum.PriceAsc,
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
            });
        });

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-2', 'default-parameter-value-5');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-5'],
                            },
                        ],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-5'],
                            },
                        ],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('changing slider parameter should redirect from SEO category if slider parameters are SEO-sensitive', () => {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ PARAMETERS: { SLIDER: true } }));
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: ProductOrderingModeEnum.PriceAsc,
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    brands: GET_DEFAULT_SEO_CATEGORY_BRANDS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
                setWasRedirectedFromSeoCategory: setWasRedirectedFromSeoCategoryMock,
            });
        });

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-3', undefined, 100, 1000);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: ORIGINAL_CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
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
                            {
                                parameter: 'default-parameter-3',
                                minimalValue: 100,
                                maximalValue: 1000,
                            },
                        ],
                    }),
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnum.PriceAsc,
                },
            },
            {
                pathname: ORIGINAL_CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
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
                            {
                                parameter: 'default-parameter-3',
                                minimalValue: 100,
                                maximalValue: 1000,
                            },
                        ],
                    }),
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

    test('changing slider parameter should not redirect from SEO category if slider parameters are not SEO-sensitive', () => {
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: ProductOrderingModeEnum.PriceAsc,
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
            });
        });

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-3', undefined, 100, 1000);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-3',
                                minimalValue: 100,
                                maximalValue: 1000,
                            },
                        ],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-3',
                                minimalValue: 100,
                                maximalValue: 1000,
                            },
                        ],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('changing parameters resets page and load more', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [PAGE_QUERY_PARAMETER_NAME]: '2',
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '2',
            },
        }));

        useUpdateFilterQuery().updateFilterParametersQuery('default-parameter-2', 'default-parameter-value-5');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-5'],
                            },
                        ],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        parameters: [
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-5'],
                            },
                        ],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });
});

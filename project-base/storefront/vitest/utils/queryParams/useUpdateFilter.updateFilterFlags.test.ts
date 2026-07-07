import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/useSessionStore';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';
import { beforeEach, describe, expect, Mock, test, vi } from 'vitest';
import { resetUseUpdateFilterTestMocks } from 'vitest/utils/queryParams/useUpdateFilterTestUtils';

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
                sort: TypeProductOrderingModeEnum.Priority,
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

describe('useUpdateFilter().updateFilterFlags tests', () => {
    beforeEach(() => {
        resetUseUpdateFilterTestMocks({
            mockSeoSensitiveFiltersGetter,
            useRouterMock: useRouter as Mock,
            useSessionStoreMock: useSessionStore as unknown as Mock,
            mockPush,
            categoryPathname: CATEGORY_PATHNAME,
            categoryUrl: CATEGORY_URL,
        });
    });

    test('flag should be added to query if not present', () => {
        useUpdateFilterQuery().updateFilterFlagsQuery('test-flag');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('flag should be removed from query if already present', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ flags: ['test-flag'] }) },
        }));

        useUpdateFilterQuery().updateFilterFlagsQuery('test-flag');

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

    test('changing flags resets page and load more', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [PAGE_QUERY_PARAMETER_NAME]: '2',
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '2',
            },
        }));

        useUpdateFilterQuery().updateFilterFlagsQuery('test-flag');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('changing flag should not redirect from SEO category if flags are not SEO-sensitive', () => {
        // @ts-expect-error
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ FLAGS: false }));
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: TypeProductOrderingModeEnum.PriceAsc,
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    brands: GET_DEFAULT_SEO_CATEGORY_BRANDS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
            });
        });

        useUpdateFilterQuery().updateFilterFlagsQuery('test-flag');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('changing flag should redirect from SEO category if flags are SEO-sensitive', () => {
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: TypeProductOrderingModeEnum.PriceAsc,
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    brands: GET_DEFAULT_SEO_CATEGORY_BRANDS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
                setWasRedirectedFromSeoCategory: setWasRedirectedFromSeoCategoryMock,
            });
        });

        useUpdateFilterQuery().updateFilterFlagsQuery('test-flag');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: ORIGINAL_CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: Array.from(GET_DEFAULT_SEO_CATEGORY_BRANDS()),
                        flags: [...Array.from(GET_DEFAULT_SEO_CATEGORY_FLAGS()), 'test-flag'],
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
                    [SORT_QUERY_PARAMETER_NAME]: TypeProductOrderingModeEnum.PriceAsc,
                },
            },
            {
                pathname: ORIGINAL_CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: Array.from(GET_DEFAULT_SEO_CATEGORY_BRANDS()),
                        flags: [...Array.from(GET_DEFAULT_SEO_CATEGORY_FLAGS()), 'test-flag'],
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
                    [SORT_QUERY_PARAMETER_NAME]: TypeProductOrderingModeEnum.PriceAsc,
                },
            },
            {
                shallow: true,
            },
        );
        expect(setWasRedirectedFromSeoCategoryMock).toBeCalledTimes(1);
        expect(setWasRedirectedFromSeoCategoryMock).toBeCalledWith(true);
    });

    test('rapidly toggling the same flag in SEO category should keep the latest state', () => {
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: TypeProductOrderingModeEnum.PriceAsc,
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    brands: GET_DEFAULT_SEO_CATEGORY_BRANDS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
                setWasRedirectedFromSeoCategory: setWasRedirectedFromSeoCategoryMock,
            });
        });

        const { updateFilterFlagsQuery } = useUpdateFilterQuery();

        updateFilterFlagsQuery('test-flag');
        updateFilterFlagsQuery('test-flag');

        const secondPushFilterAsString = mockPush.mock.calls[1][0].query[FILTER_QUERY_PARAMETER_NAME];
        const secondPushFilter = JSON.parse(secondPushFilterAsString);

        expect(secondPushFilter.flags).toStrictEqual(Array.from(GET_DEFAULT_SEO_CATEGORY_FLAGS()));
        expect(secondPushFilter.flags).not.toContain('test-flag');
    });

    test('changing flag in SEO category should keep already selected slider parameter value', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                    parameters: [
                        {
                            parameter: 'slider-parameter-1',
                            minimalValue: 100,
                            maximalValue: 1000,
                        },
                    ],
                }),
            },
        }));
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: TypeProductOrderingModeEnum.PriceAsc,
                    flags: GET_DEFAULT_SEO_CATEGORY_FLAGS(),
                    brands: GET_DEFAULT_SEO_CATEGORY_BRANDS(),
                    parameters: GET_DEFAULT_SEO_CATEGORY_PARAMETERS(),
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
                setWasRedirectedFromSeoCategory: setWasRedirectedFromSeoCategoryMock,
            });
        });

        useUpdateFilterQuery().updateFilterFlagsQuery('test-flag');

        const pushedFilterAsString = mockPush.mock.calls[0][0].query[FILTER_QUERY_PARAMETER_NAME];
        const pushedFilter = JSON.parse(pushedFilterAsString);

        expect(mockPush.mock.calls[0][0].query.categorySlug).toBe(ORIGINAL_CATEGORY_URL);
        expect(pushedFilter.flags).toStrictEqual([...Array.from(GET_DEFAULT_SEO_CATEGORY_FLAGS()), 'test-flag']);
        expect(pushedFilter.parameters).toContainEqual({
            parameter: 'slider-parameter-1',
            minimalValue: 100,
            maximalValue: 1000,
        });
    });
});

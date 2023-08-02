import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { FILTER_QUERY_PARAMETER_NAME, SORT_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
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
                sort: ProductOrderingModeEnumApi.PriorityApi,
                parameters: new Map(),
            },
            originalCategorySlug: null,
        });
    }),
}));

describe('useQueryParams().updateFilterPriceMaximum tests', () => {
    test('maximalPrice should not be updated if it is the same as current maximal price', () => {
        (useRouter as Mock).mockImplementation(() => ({
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ maximalPrice: 1000 }) },
        }));

        useQueryParams().updateFilterPriceMaximum(1000);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_URL,
                query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ maximalPrice: 1000 }) },
            },
            undefined,
            {
                shallow: true,
            },
        );
    });

    test('maximalPrice should be updated if it differs from the current maximal price', () => {
        (useRouter as Mock).mockImplementation(() => ({
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ maximalPrice: 1000 }) },
        }));

        useQueryParams().updateFilterPriceMaximum(1100);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_URL,
                query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ maximalPrice: 1100 }) },
            },
            undefined,
            {
                shallow: true,
            },
        );
    });

    test('maximalPrice should be reset if it is set to undefined', () => {
        (useRouter as Mock).mockImplementation(() => ({
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ maximalPrice: 1000 }) },
        }));

        useQueryParams().updateFilterPriceMaximum(undefined);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_URL,
                query: {},
            },
            undefined,
            {
                shallow: true,
            },
        );
    });

    test('changing maximalPrice should not redirect from SEO category if price is not SEO-sensitive', () => {
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

        useQueryParams().updateFilterPriceMaximum(1000);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        maximalPrice: 1000,
                    }),
                },
            },
            undefined,
            {
                shallow: true,
            },
        );
    });

    test('changing maximalPrice should redirect from SEO category if price is SEO-sensitive', () => {
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

        useQueryParams().updateFilterPriceMaximum(1000);

        expect(mockPush).toBeCalledWith(
            {
                pathname: ORIGINAL_CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
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
            undefined,
            {
                shallow: true,
            },
        );
    });
});

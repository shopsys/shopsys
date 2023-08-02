import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { FILTER_QUERY_PARAMETER_NAME, SORT_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useQueryParams } from 'hooks/useQueryParams';
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

describe('useQueryParams().updateSort tests', () => {
    test('sort should not be updated if updating with the default sort', () => {
        useQueryParams().updateSort(ProductOrderingModeEnumApi.PriorityApi);

        expect(mockPush).toBeCalledWith({ pathname: CATEGORY_URL, query: {} }, undefined, { shallow: true });
    });

    test('sort should be updated if updating with new sort', () => {
        useQueryParams().updateSort(ProductOrderingModeEnumApi.PriceAscApi);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_URL,
                query: { [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceAscApi },
            },
            undefined,
            { shallow: true },
        );
    });

    test('sort should redirect from SEO category if it is SEO-sensitive', () => {
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

        useQueryParams().updateSort(ProductOrderingModeEnumApi.PriceDescApi);

        expect(mockPush).toBeCalledWith(
            {
                pathname: ORIGINAL_CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
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
                },
            },
            undefined,
            {
                shallow: true,
            },
        );
    });

    test('sort should not redirect from SEO category if it is not SEO-sensitive', () => {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ SORT: false }));
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

        useQueryParams().updateSort(ProductOrderingModeEnumApi.PriceDescApi);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_URL,
                query: {
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceDescApi,
                },
            },
            undefined,
            {
                shallow: true,
            },
        );
    });
});

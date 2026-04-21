import { AdvertsQueryDocument } from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import { ArticlesQueryDocument } from 'graphql/requests/articlesInterface/articles/queries/ArticlesQuery.generated';
import { CurrentCustomerUserAuthQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserAuthQuery.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { NavigationQueryDocument } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { NotificationBarsDocument } from 'graphql/requests/notificationBars/queries/NotificationBarsQuery.generated';
import { SeoPageQueryDocument } from 'graphql/requests/seoPage/queries/SeoPageQuery.generated';
import { SettingsQueryDocument } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { TypeArticlePlacementTypeEnum } from 'graphql/types';
import { Client } from 'urql';
import { prefetchLayoutQueries } from 'utils/serverSide/prefetchLayoutQueries';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { mockIsFullPageRequest, mockExtractSeoPageSlugFromUrl } = vi.hoisted(() => ({
    mockIsFullPageRequest: vi.fn(),
    mockExtractSeoPageSlugFromUrl: vi.fn(),
}));

vi.mock('utils/isFullPageRequest', () => ({
    isFullPageRequest: mockIsFullPageRequest,
}));

vi.mock('utils/seo/extractSeoPageSlugFromUrl', () => ({
    extractSeoPageSlugFromUrl: mockExtractSeoPageSlugFromUrl,
}));

type QueryCall = {
    query: unknown;
    variables?: unknown;
};

const createMockClient = () => {
    const queryCalls: QueryCall[] = [];
    const query = vi.fn((requestQuery: unknown, variables?: unknown) => {
        queryCalls.push({ query: requestQuery, variables });

        return {
            toPromise: vi.fn().mockResolvedValue({
                data: { callIndex: queryCalls.length },
            }),
        };
    });

    return { client: { query } as unknown as Client, queryCalls };
};

const createContext = () =>
    ({
        req: { headers: {} },
        resolvedUrl: '/test-page',
    }) as any;

const domainConfig = {
    url: 'https://example.com',
} as any;

describe('prefetchLayoutQueries', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mockExtractSeoPageSlugFromUrl.mockReturnValue(null);
    });

    test('prefetches layout and customer queries on full page request', async () => {
        mockIsFullPageRequest.mockReturnValue(true);
        const { client, queryCalls } = createMockClient();

        const result = await prefetchLayoutQueries({
            client,
            context: createContext(),
            domainConfig,
        });

        expect(queryCalls).toEqual([
            { query: CurrentCustomerUserAuthQueryDocument, variables: undefined },
            { query: NotificationBarsDocument, variables: undefined },
            { query: NavigationQueryDocument, variables: undefined },
            {
                query: ArticlesQueryDocument,
                variables: {
                    placement: [
                        TypeArticlePlacementTypeEnum.Footer1,
                        TypeArticlePlacementTypeEnum.Footer2,
                        TypeArticlePlacementTypeEnum.Footer3,
                        TypeArticlePlacementTypeEnum.Footer4,
                    ],
                    first: 100,
                },
            },
            { query: AdvertsQueryDocument, variables: { positionNames: ['header', 'footer'], categoryUuid: null } },
            { query: SettingsQueryDocument, variables: undefined },
        ]);
        expect(result.resolvedQueries).toHaveLength(6);
        expect(result.seoPageSlug).toBeNull();
    });

    test('prefetches full current customer query when full mode is enabled', async () => {
        mockIsFullPageRequest.mockReturnValue(true);
        const { client, queryCalls } = createMockClient();

        const result = await prefetchLayoutQueries({
            client,
            context: createContext(),
            domainConfig,
            currentCustomerUserPrefetchMode: 'full',
        });

        expect(queryCalls[0]).toEqual({
            query: CurrentCustomerUserQueryDocument,
            variables: undefined,
        });
        expect(result.resolvedQueries).toHaveLength(6);
    });

    test('fetches only customer and additional queries on client-side data request', async () => {
        mockIsFullPageRequest.mockReturnValue(false);
        const { client, queryCalls } = createMockClient();
        const additionalQuery = { query: 'AdditionalQuery', variables: { foo: 'bar' } };

        const result = await prefetchLayoutQueries({
            client,
            context: createContext(),
            domainConfig,
            prefetchedQueries: [additionalQuery],
        });

        expect(queryCalls).toEqual([
            { query: CurrentCustomerUserAuthQueryDocument, variables: undefined },
            { query: 'AdditionalQuery', variables: { foo: 'bar' } },
        ]);
        expect(result.resolvedQueries).toHaveLength(2);
    });

    test('includes SeoPage query when slug can be extracted', async () => {
        mockIsFullPageRequest.mockReturnValue(true);
        mockExtractSeoPageSlugFromUrl.mockReturnValue('seo-slug');
        const { client, queryCalls } = createMockClient();

        const result = await prefetchLayoutQueries({
            client,
            context: createContext(),
            domainConfig,
        });

        expect(queryCalls.at(-1)).toEqual({
            query: SeoPageQueryDocument,
            variables: { pageSlug: 'seo-slug' },
        });
        expect(result.resolvedQueries).toHaveLength(7);
        expect(result.seoPageSlug).toBe('seo-slug');
    });
});

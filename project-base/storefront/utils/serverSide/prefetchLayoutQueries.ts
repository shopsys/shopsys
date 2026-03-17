import { LayoutQueryResult, PrefetchLayoutParams, QueriesArray } from './types';
import { Variables } from '@urql/exchange-graphcache';
import { AdvertsQueryDocument } from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import { ArticlesQueryDocument } from 'graphql/requests/articlesInterface/articles/queries/ArticlesQuery.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { NavigationQueryDocument } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { NotificationBarsDocument } from 'graphql/requests/notificationBars/queries/NotificationBarsQuery.generated';
import { SeoPageQueryDocument } from 'graphql/requests/seoPage/queries/SeoPageQuery.generated';
import { SettingsQueryDocument } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { TypeArticlePlacementTypeEnum } from 'graphql/types';
import { isFullPageRequest } from 'utils/isFullPageRequest';
import { extractSeoPageSlugFromUrl } from 'utils/seo/extractSeoPageSlugFromUrl';

const getLayoutOnlyQueries = (seoPageSlug: string | null): QueriesArray<any> => [
    { query: NotificationBarsDocument },
    { query: NavigationQueryDocument },
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
    { query: SettingsQueryDocument },
    ...(seoPageSlug ? [{ query: SeoPageQueryDocument, variables: { pageSlug: seoPageSlug } }] : []),
];

/**
 * Prefetches queries during SSR so their results are available via client.readQuery().
 *
 * IMPORTANT: Graphcache does NOT run on the server — only the SSRExchange stores results.
 * Any query that needs to be read via client.readQuery() during SSR MUST be included here
 * (either in getLayoutOnlyQueries or via additionalPrefetchQueries). Otherwise readQuery()
 * will silently return null.
 */
export const prefetchLayoutQueries = async <VariablesType extends Variables>({
    client,
    context,
    domainConfig,
    prefetchedQueries: additionalPrefetchQueries = [],
}: PrefetchLayoutParams<VariablesType>): Promise<LayoutQueryResult> => {
    const seoPageSlug = extractSeoPageSlugFromUrl(context.resolvedUrl, domainConfig.url);
    const isFirstPageLoad = isFullPageRequest(context.req.headers);

    const queries: QueriesArray<any> = [
        { query: CurrentCustomerUserQueryDocument },
        ...(isFirstPageLoad ? getLayoutOnlyQueries(seoPageSlug) : []),
        ...additionalPrefetchQueries,
    ];

    const resolvedQueries = await Promise.all(queries.map((q) => client.query(q.query, q.variables).toPromise()));

    return { resolvedQueries, seoPageSlug };
};

import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Wishlist } from 'components/Pages/Wishlist/Wishlist';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    ProductListSummaryQueryDocument,
    TypeProductListSummaryQuery,
    TypeProductListSummaryQueryVariables,
} from 'graphql/requests/productLists/queries/ProductListSummaryQuery.generated';
import {
    TypeWishlistPageQuery,
    TypeWishlistPageQueryVariables,
    WishlistPageQueryDocument,
} from 'graphql/requests/productLists/queries/WishlistPageQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { NextPage } from 'next';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { isUserLoggedInSSR } from 'utils/auth/isUserLoggedInSSR';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getRedirectWithOffsetPage } from 'utils/loadMore/getRedirectWithOffsetPage';
import { getWishlistPageRange } from 'utils/productLists/wishlist/getWishlistPageRange';
import { LOAD_MORE_QUERY_PARAMETER_NAME, PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { buildServerSideProps, prefetchLayoutQueries, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const WishlistPage: NextPage<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const currentDomainConfig = useDomainConfig();
    const [wishlistUrl] = getInternationalizedStaticUrls(['/wishlist'], currentDomainConfig.url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Wishlist'), slug: wishlistUrl }];

    useGtmPageReadyEvent(useGtmStaticPageReadyEvent(GtmPageType.wishlist, breadcrumbs));

    return (
        <CommonLayout breadcrumbs={breadcrumbs} defaultMetaRobots="noindex" defaultTitle={t('Wishlist')}>
            <Wishlist />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const requestedRange = getWishlistPageRange(
                Number(context.query[PAGE_QUERY_PARAMETER_NAME] || 1),
                Number(context.query[LOAD_MORE_QUERY_PARAMETER_NAME] || 0),
            );
            const [wishlistUrl] = getInternationalizedStaticUrls(['/wishlist'], domainConfig.url);
            const redirect = getRedirectWithOffsetPage(
                requestedRange.page,
                requestedRange.loadMore,
                wishlistUrl,
                context.query,
            );
            if (redirect) return redirect;

            const client = createClient({ t, ssrExchange, domainConfig, redisClient, context });
            const layoutResult = await prefetchLayoutQueries({ client, context, domainConfig });
            const pageQueryResults: OperationResult[] = [];
            if (isUserLoggedInSSR(client)) {
                const summaryResult = await client
                    .query<TypeProductListSummaryQuery, TypeProductListSummaryQueryVariables>(
                        ProductListSummaryQueryDocument,
                        {
                            input: { type: TypeProductListTypeEnum.Wishlist, uuid: null },
                        },
                    )
                    .toPromise();
                pageQueryResults.push(summaryResult);
                const summary = summaryResult.data?.productList;
                if (!summaryResult.error && summary && summary.itemsCount > 0) {
                    const range = getWishlistPageRange(requestedRange.page, 0, summary.itemsCount);
                    pageQueryResults.push(
                        await client
                            .query<TypeWishlistPageQuery, TypeWishlistPageQueryVariables>(WishlistPageQueryDocument, {
                                input: { type: TypeProductListTypeEnum.Wishlist, uuid: summary.uuid },
                                endCursor: getEndCursor(range.page),
                                pageSize: DEFAULT_PAGE_SIZE,
                            })
                            .toPromise(),
                    );
                }
            }

            return buildServerSideProps({
                layoutResult,
                pageQueryResults,
                client,
                redisClient,
                ssrExchange,
                context,
                domainConfig,
            });
        },
);

export default WishlistPage;

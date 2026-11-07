import { CommonLayout } from 'components/Layout/CommonLayout';
import { Wishlist } from 'components/Pages/Wishlist/Wishlist';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { ProductListSummaryQueryDocument } from 'graphql/requests/productLists/queries/ProductListSummaryQuery.generated';
import { WishlistPageQueryDocument } from 'graphql/requests/productLists/queries/WishlistPageQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { NextPage } from 'next';
import { createClient } from 'urql/createClient';
import { isUserLoggedInSSR } from 'utils/auth/isUserLoggedInSSR';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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
            const { offset, limit } = getWishlistPageRange(
                Number(context.query[PAGE_QUERY_PARAMETER_NAME] || 1),
                Number(context.query[LOAD_MORE_QUERY_PARAMETER_NAME] || 0),
            );
            const client = createClient({ t, ssrExchange, domainConfig, redisClient, context });
            const layoutResult = await prefetchLayoutQueries({ client, context, domainConfig });
            const pageQueryResults = isUserLoggedInSSR(client)
                ? [
                      await client
                          .query(ProductListSummaryQueryDocument, {
                              input: { type: TypeProductListTypeEnum.Wishlist, uuid: null },
                          })
                          .toPromise(),
                      await client
                          .query(WishlistPageQueryDocument, {
                              offset,
                              limit,
                              input: {
                                  type: TypeProductListTypeEnum.Wishlist,
                                  uuid: null,
                              },
                          })
                          .toPromise(),
                  ]
                : [];

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

import { CommonLayout } from 'components/Layout/CommonLayout';
import {
    StoreDetailQueryDocument,
    useStoreDetailQuery,
} from 'graphql/requests/stores/queries/StoreDetailQuery.generated';
import { useGtmFriendlyPageReadyEvent } from 'gtm/factories/useGtmFriendlyPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { getPrefixedSeoTitle } from 'utils/seo/getPrefixedSeoTitle';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { buildServerSideProps, prefetchLayoutQueries } from 'utils/serverSide/initServerSideProps';

const StoreDetailContent = dynamic(() =>
    import('components/Pages/StoreDetail/StoreDetailContent').then((mod) => mod.StoreDetailContent),
);

const StoreDetailPage: NextPage = () => {
    const { t } = useTranslation();
    const router = useRouter();
    const [{ data: storeDetailData, fetching: isStoreFetching }] = useStoreDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const pageReadyEvent = useGtmFriendlyPageReadyEvent(storeDetailData?.store);
    useGtmPageReadyEvent(pageReadyEvent, isStoreFetching);

    const seoTitle = getPrefixedSeoTitle(storeDetailData?.store?.storeName, t('Store'));
    const storeImageUrl = storeDetailData?.store?.storeImages[0]?.url;

    return (
        <CommonLayout
            breadcrumbs={storeDetailData?.store?.breadcrumb}
            breadcrumbsType="stores"
            canonicalQueryParams={[]}
            isFetchingData={isStoreFetching}
            ogImageUrlDefault={storeImageUrl}
            title={seoTitle}
        >
            {!!storeDetailData?.store && <StoreDetailContent store={storeDetailData.store} />}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const client = createClient({
                t,
                ssrExchange,
                domainConfig,
                redisClient,
                context,
            });

            const storePromise = client
                .query(StoreDetailQueryDocument, {
                    urlSlug: getSlugFromServerSideUrl(context.req.url ?? '', context.req.headers),
                })
                .toPromise();

            const [storeResponse, layoutResult] = await Promise.all([
                storePromise,
                prefetchLayoutQueries({ client, context, domainConfig }),
            ]);

            const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                storeResponse.error,
                storeResponse.data?.store,
                context,
                domainConfig.url,
            );

            if (serverSideErrorResponse) {
                return serverSideErrorResponse;
            }

            return buildServerSideProps({
                layoutResult,
                client,
                redisClient,
                ssrExchange,
                context,
                domainConfig,
                pageQueryResults: [storeResponse],
            });
        },
);

export default StoreDetailPage;

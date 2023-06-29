import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { StoreDetailContent } from 'components/Pages/StoreDetail/StoreDetailContent';
import { StorePageSkeleton } from 'components/Pages/StoreDetail/StorePageSkeleton';
import {
    StoreDetailQueryApi,
    StoreDetailQueryDocumentApi,
    StoreDetailQueryVariablesApi,
    useStoreDetailQueryApi,
} from 'graphql/generated';
import { getDomainConfig } from 'helpers/domain/domain';
import { useGtmFriendlyPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/misc/isServer';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { createClient } from 'helpers/urql/createClient';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult, ssrExchange } from 'urql';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'utils/getSlugFromUrl';

const StoreDetailPage: NextPage = () => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);

    const [{ data: storeDetailData, fetching }] = useQueryError(
        useStoreDetailQueryApi({
            variables: { urlSlug: getSlugFromUrl(slug) },
        }),
    );

    const pageViewEvent = useGtmFriendlyPageViewEvent(storeDetailData?.store);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout title={storeDetailData?.store?.storeName}>
            {!!storeDetailData?.store?.breadcrumb && (
                <Webline>
                    <Breadcrumbs key="breadcrumb" breadcrumb={storeDetailData.store.breadcrumb} />
                </Webline>
            )}
            {!!storeDetailData?.store && !fetching ? (
                <StoreDetailContent store={storeDetailData.store} />
            ) : (
                <StorePageSkeleton />
            )}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient) => async (context) => {
    const domainConfig = getDomainConfig(context.req.headers.host!);
    const ssrCache = ssrExchange({ isClient: false });
    const client = createClient(context, domainConfig.publicGraphqlEndpoint, ssrCache, redisClient);

    if (isRedirectedFromSsr(context.req.headers)) {
        const storeResponse: OperationResult<StoreDetailQueryApi, StoreDetailQueryVariablesApi> = await client!
            .query(StoreDetailQueryDocumentApi, {
                urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
            })
            .toPromise();

        if ((!storeResponse.data || !storeResponse.data.store) && !(context.res.statusCode === 503)) {
            return {
                notFound: true,
            };
        }
    }

    const initServerSideData = await initServerSideProps({
        context,
        client,
        ssrCache,
        redisClient,
    });

    return initServerSideData;
});

export default StoreDetailPage;

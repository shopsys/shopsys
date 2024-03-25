import { CommonLayout } from 'components/Layout/CommonLayout';
import { StoreDetailContent } from 'components/Pages/StoreDetail/StoreDetailContent';
import {
    useStoreDetailQuery,
    StoreDetailQuery,
    StoreDetailQueryVariables,
    StoreDetailQueryDocument,
} from 'graphql/requests/stores/queries/StoreDetailQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { handleServerSideErrorResponseForFriendlyUrls } from 'helpers/errors/handleServerSideErrorResponseForFriendlyUrls';
import { isRedirectedFromSsr } from 'helpers/isRedirectedFromSsr';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'helpers/parsing/urlParsing';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';

const StoreDetailPage: NextPage = () => {
    const router = useRouter();
    const [{ data: storeDetailData, fetching }] = useStoreDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const pageViewEvent = useGtmFriendlyPageViewEvent(storeDetailData?.store);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout
            breadcrumbs={storeDetailData?.store?.breadcrumb}
            canonicalQueryParams={[]}
            isFetchingData={fetching}
            title={storeDetailData?.store?.storeName}
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
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });

            if (isRedirectedFromSsr(context.req.headers)) {
                const storeResponse: OperationResult<StoreDetailQuery, StoreDetailQueryVariables> = await client!
                    .query(StoreDetailQueryDocument, {
                        urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                    })
                    .toPromise();

                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    storeResponse.error?.graphQLErrors,
                    storeResponse.data?.store,
                    context.res,
                );

                if (serverSideErrorResponse) {
                    return serverSideErrorResponse;
                }
            }

            const initServerSideData = await initServerSideProps({
                context,
                client,
                ssrExchange,
                domainConfig,
            });

            return initServerSideData;
        },
);

export default StoreDetailPage;

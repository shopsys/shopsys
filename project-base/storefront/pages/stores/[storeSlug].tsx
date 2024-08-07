import { CommonLayout } from 'components/Layout/CommonLayout';
import { StoreDetailContent } from 'components/Pages/StoreDetail/StoreDetailContent';
import {
    useStoreDetailQuery,
    TypeStoreDetailQuery,
    TypeStoreDetailQueryVariables,
    StoreDetailQueryDocument,
} from 'graphql/requests/stores/queries/StoreDetailQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { isRedirectedFromSsr } from 'utils/isRedirectedFromSsr';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { getPrefixedSeoTitle } from 'utils/seo/getPrefixedSeoTitle';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

const StoreDetailPage: NextPage = () => {
    const { t } = useTranslation();
    const router = useRouter();
    const [{ data: storeDetailData, fetching: isStoreFetching }] = useStoreDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const pageViewEvent = useGtmFriendlyPageViewEvent(storeDetailData?.store);
    useGtmPageViewEvent(pageViewEvent, isStoreFetching);

    const seoTitle = getPrefixedSeoTitle(storeDetailData?.store?.storeName, t('Store'));
    const storeImageUrl = storeDetailData?.store?.storeImages[0]?.url;

    return (
        <CommonLayout
            breadcrumbs={storeDetailData?.store?.breadcrumb}
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
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });

            const storeResponse: OperationResult<TypeStoreDetailQuery, TypeStoreDetailQueryVariables> = await client!
                .query(StoreDetailQueryDocument, {
                    urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                })
                .toPromise();

            if (isRedirectedFromSsr(context.req.headers)) {
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

import { CommonLayout } from 'components/Layout/CommonLayout';
import {
    useStoreDetailQuery,
    TypeStoreDetailQuery,
    TypeStoreDetailQueryVariables,
    StoreDetailQueryDocument,
} from 'graphql/requests/stores/queries/StoreDetailQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getIsRedirectedFromSsr } from 'utils/getIsRedirectedFromSsr';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { getPrefixedSeoTitle } from 'utils/seo/getPrefixedSeoTitle';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

const StoreDetailContent = dynamic(() =>
    import('components/Pages/StoreDetail/StoreDetailContent').then((mod) => mod.StoreDetailContent),
);

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

            const storeResponse: OperationResult<TypeStoreDetailQuery, TypeStoreDetailQueryVariables> = await client!
                .query(StoreDetailQueryDocument, {
                    urlSlug: getSlugFromServerSideUrl(context.req.url ?? '', context.req.headers),
                })
                .toPromise();

            if (getIsRedirectedFromSsr(context.req.headers)) {
                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    storeResponse.error,
                    storeResponse.data?.store,
                    context,
                    domainConfig.url,
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

import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { PageGuard } from 'components/Helpers/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetailContent';
import { BreadcrumbFragmentApi, OrderDetailQueryDocumentApi, useOrderDetailQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const OrderDetailPage: FC = () => {
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], domainConfig.url);
    const router = useRouter();
    const [{ data: orderData }] = useQueryError(
        useOrderDetailQueryApi({ variables: { orderNumber: getStringFromUrlQuery(router.query.orderNumber) } }),
    );
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <PageGuard
                accessCondition={orderData?.order !== undefined && orderData.order !== null}
                errorRedirectUrl={customerOrdersUrl}
            >
                {orderData?.order !== undefined && orderData.order !== null && (
                    <CommonLayout title={`${t('Order number')} ${orderData.order.number}`}>
                        <OrderDetailContent order={orderData.order} breadcrumbs={breadcrumbs} />
                    </CommonLayout>
                )}
            </PageGuard>
        </>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => {
            if (typeof context.query.orderNumber !== 'string') {
                return {
                    redirect: {
                        destination: '/',
                        statusCode: 301,
                    },
                };
            }

            return initServerSideProps({
                context,
                store,
                authenticationRequired: true,
                prefetchedQueries: [
                    { query: OrderDetailQueryDocumentApi, variables: { orderNumber: context.query.orderNumber } },
                ],
                redisClient,
            });
        },
        store,
    ),
);

export default OrderDetailPage;

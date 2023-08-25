import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetailContent';
import { BreadcrumbFragmentApi, OrderDetailQueryDocumentApi, useOrderDetailQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getStringFromUrlQuery } from 'helpers/parsing/urlParsing';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { GtmPageType } from 'gtm/types/enums';

const OrderDetailPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], url);
    const router = useRouter();
    const orderNumber = getStringFromUrlQuery(router.query.orderNumber);
    const [{ data: orderData, error }] = useOrderDetailQueryApi({
        variables: { orderNumber },
    });
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <PageGuard accessCondition={!error} errorRedirectUrl={customerOrdersUrl}>
                <CommonLayout title={`${t('Order number')} ${orderNumber}`} breadcrumbs={breadcrumbs}>
                    {orderData?.order && <OrderDetailContent order={orderData.order} />}
                </CommonLayout>
            </PageGuard>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
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
        authenticationRequired: true,
        prefetchedQueries: [
            { query: OrderDetailQueryDocumentApi, variables: { orderNumber: context.query.orderNumber } },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default OrderDetailPage;

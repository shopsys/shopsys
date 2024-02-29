import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetailContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import {
    BreadcrumbFragmentApi,
    OrderDetailQueryDocumentApi,
    OrderDetailQueryVariablesApi,
    useOrderDetailQueryApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getStringFromUrlQuery } from 'helpers/parsing/urlParsing';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';

const OrderDetailPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerUrl, customerOrdersUrl] = getInternationalizedStaticUrls(['/customer', '/customer/orders'], url);
    const router = useRouter();
    const orderNumber = getStringFromUrlQuery(router.query.orderNumber);
    const [{ data: orderData, fetching, error }] = useOrderDetailQueryApi({
        variables: { orderNumber },
    });
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('Customer'), slug: customerUrl },
        { __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl },
        { __typename: 'Link', name: orderNumber, slug: '' },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <PageGuard errorRedirectUrl={customerOrdersUrl} isWithAccess={!error}>
                <CommonLayout
                    breadcrumbs={breadcrumbs}
                    isFetchingData={fetching}
                    title={`${t('Order number')} ${orderNumber}`}
                >
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

    return initServerSideProps<OrderDetailQueryVariablesApi>({
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

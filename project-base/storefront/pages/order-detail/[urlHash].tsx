import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderDetailContent } from 'components/Pages/Customer/OrderDetailContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { BreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    OrderDetailByHashQueryDocument,
    OrderDetailByHashQueryVariables,
    useOrderDetailByHashQuery,
} from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'helpers/staticUrls/getInternationalizedStaticUrls';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';

const OrderDetailByHashPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const router = useRouter();
    const orderHash = getStringFromUrlQuery(router.query.urlHash);
    const [{ data: orderData, fetching: orderFetching }] = useOrderDetailByHashQuery({
        variables: { urlHash: orderHash },
    });

    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], url);
    const breadcrumbs: BreadcrumbFragment[] = [{ __typename: 'Link', name: t('My orders'), slug: customerOrdersUrl }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <PageGuard errorRedirectUrl="/" isWithAccess={!!orderData?.order || orderFetching}>
                <CommonLayout breadcrumbs={breadcrumbs} title={`${t('Order number')} ${orderHash}`}>
                    {!!orderData?.order && <OrderDetailContent order={orderData.order} />}
                </CommonLayout>
            </PageGuard>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    if (typeof context.params?.urlHash !== 'string') {
        return {
            redirect: {
                destination: '/',
                statusCode: 301,
            },
        };
    }

    return initServerSideProps<OrderDetailByHashQueryVariables>({
        context,
        prefetchedQueries: [{ query: OrderDetailByHashQueryDocument, variables: { urlHash: context.params.urlHash } }],
        redisClient,
        domainConfig,
        t,
    });
});

export default OrderDetailByHashPage;

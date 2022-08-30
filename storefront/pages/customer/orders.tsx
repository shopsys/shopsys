import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrdersContent } from 'components/Pages/Customer/Orders/OrdersContent';
import { useOrders } from 'connectors/customer/Orders';
import { OrdersQueryDocumentApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { initialState } from 'redux/slices/user';

const OrdersPage: FC = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const currentDomainConfig = useShopsysSelector((state) => state.domain);
    const ordersData = useOrders(currentDomainConfig);
    const [customerUrl, customerOrdersUrl] = getInternationalizedStaticUrls(
        ['/customer', '/customer/orders'],
        domainUrl,
    );
    const breadcrumbs = useMemo(
        () => [
            { name: t('Customer'), slug: customerUrl },
            { name: t('My orders'), slug: customerOrdersUrl },
        ],
        [customerUrl, customerOrdersUrl, t],
    );
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('My orders')}>
                <OrdersContent
                    orders={ordersData?.orders}
                    totalCount={ordersData?.totalCount}
                    breadcrumbs={breadcrumbs}
                />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, true, [
        {
            query: OrdersQueryDocumentApi,
            variables: {
                after: store.getState().user.pagination.paginationCursor,
                first: initialState.pagination.pageSize,
            },
        },
    ]);
});

export default OrdersPage;

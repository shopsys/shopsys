import {
    NavigationQueryDocumentApi,
    NotificationBarsDocumentApi,
    OrderDetailQueryDocumentApi,
} from 'graphql/generated';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { getOrderDetail } from 'connectors/customer/Orders';
import { getStringFromUrlQuery } from 'utils/getStringFromUrlQuery';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import OrderDetail from 'components/Pages/Customer/OrderDetail';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useRouter } from 'next/router';

const Index: FC = () => {
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [customerOrdersUrl] = useGetInternationalizedStaticUrls(['/customer/orders'], domainConfig.url);
    const router = useRouter();
    const isUserLoggedIn = useShopsysSelector((state) => state.user.isUserLoggedIn);
    const order = getOrderDetail(getStringFromUrlQuery(router.query.orderNumber), domainConfig);

    if (!isUserLoggedIn) {
        router.push('/');
        return null;
    }

    if (order === null) {
        router.push(customerOrdersUrl);
        return null;
    }

    return (
        <StaticUrlGuard domainUrl={domainConfig.url}>
            <CommonLayout>
                <OrderDetail order={order} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    if (typeof context.query.orderNumber !== 'string') {
        return {
            redirect: {
                destination: '/',
                statusCode: 301,
            },
        };
    }

    initDomainConfig(context, store);
    return initServerSideProps(context, store, [
        { query: NotificationBarsDocumentApi },
        { query: NavigationQueryDocumentApi },
        { query: OrderDetailQueryDocumentApi, variables: { orderNumber: context.query.orderNumber } },
    ]);
});

export default Index;

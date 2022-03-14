import {
    NavigationQueryDocumentApi,
    NotificationBarsDocumentApi,
    OrderDetailByHashQueryDocumentApi,
} from 'graphql/generated';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { getOrderDetailByHash } from 'connectors/customer/Orders';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import OrderDetail from 'components/Pages/Customer/OrderDetail';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useRouter } from 'next/router';

const OrderDetailByHash: FC = () => {
    const domainConfig = useShopsysSelector((state) => state.domain);
    const router = useRouter();
    const order = getOrderDetailByHash(getParsedUrlHashQuery(router.query.urlHash), domainConfig);

    if (order === null) {
        router.push('/');
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
    if (typeof context.params?.urlHash !== 'string') {
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
        { query: OrderDetailByHashQueryDocumentApi, variables: { urlHash: context.params.urlHash } },
    ]);
});

export default OrderDetailByHash;

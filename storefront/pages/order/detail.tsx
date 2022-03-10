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
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useRouter } from 'next/router';

const OrderDetailByHash: FC = () => {
    const domainConfig = useShopsysSelector((state) => state.domain);
    const router = useRouter();
    const orderDetails = getOrderDetailByHash(getParsedUrlHashQuery(router.query.h), domainConfig);

    return (
        <StaticUrlGuard domainUrl={domainConfig.url}>
            <CommonLayout>
                {/* <OrderDetail orderNumber={orderNumberParam} /> */}
                {JSON.stringify(orderDetails)}
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    if (typeof context.query.h !== 'string') {
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
        { query: OrderDetailByHashQueryDocumentApi, variables: { urlHash: context.query.h } },
    ]);
});

export default OrderDetailByHash;

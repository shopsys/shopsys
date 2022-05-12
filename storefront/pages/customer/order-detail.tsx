import PageGuard from 'components/Helpers/PageGuard';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import OrderDetail from 'components/Pages/Customer/OrderDetail';
import { useOrderDetail } from 'connectors/customer/Orders';
import { OrderDetailQueryDocumentApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { getStringFromUrlQuery } from 'utils/getStringFromUrlQuery';

const Index: FC = () => {
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [customerOrdersUrl] = getInternationalizedStaticUrls(['/customer/orders'], domainConfig.url);
    const router = useRouter();
    const order = useOrderDetail(getStringFromUrlQuery(router.query.orderNumber), domainConfig);

    return (
        <StaticUrlGuard domainUrl={domainConfig.url}>
            <PageGuard accessCondition={order !== null} errorRedirectUrl={customerOrdersUrl}>
                <CommonLayout>
                    <OrderDetail order={order!} />
                </CommonLayout>
            </PageGuard>
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
    return initServerSideProps(context, store, true, [
        { query: OrderDetailQueryDocumentApi, variables: { orderNumber: context.query.orderNumber } },
    ]);
});

export default Index;

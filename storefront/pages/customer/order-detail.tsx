import {
    NavigationQueryDocumentApi,
    NotificationBarsDocumentApi,
    OrderDetailQueryDocumentApi,
} from 'graphql/generated';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { getOrderDetail } from 'connectors/customer/Orders';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import OrderDetail from 'components/Pages/Customer/OrderDetail';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import TableGrid from 'components/Basic/TableGrid';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useRouter } from 'next/router';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const Index: FC = () => {
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [customerOrdersUrl] = useGetInternationalizedStaticUrls(['/customer/orders'], domainConfig.url);
    const router = useRouter();
    const isUserLoggedIn = useShopsysSelector((state) => state.user.isUserLoggedIn);
    const parsedOrderNumber = getParsedOrderNumberQuery(router.query.orderNumber);
    const order = getOrderDetail(parsedOrderNumber, domainConfig);

    if (!isUserLoggedIn) {
        router.push('/');
        return null;
    }

    if (parsedOrderNumber === null) {
        router.push(customerOrdersUrl);
        return null;
    }

    return (
        <StaticUrlGuard domainUrl={domainConfig.url}>
            <CommonLayout>
                {order !== null ? (
                    <OrderDetail order={order} />
                ) : (
                    <Webline>
                        <TableGrid>
                            <tr>
                                <th>{t('Error occured when loading order detail')}</th>
                            </tr>
                        </TableGrid>
                    </Webline>
                )}
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

const getParsedOrderNumberQuery = (orderNumberQuery: string | string[] | undefined): string | null => {
    if (orderNumberQuery === undefined || Array.isArray(orderNumberQuery)) {
        return null;
    }

    return orderNumberQuery;
};

export default Index;

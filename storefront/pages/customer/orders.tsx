import { FC, useEffect } from 'react';
import { NavigationQueryDocumentApi, NotificationBarsDocumentApi } from 'graphql/generated';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import Orders from 'components/Pages/Customer/Orders';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useOrders } from 'connectors/customer/Orders';
import { useRouter } from 'next/router';

const Index: FC = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const router = useRouter();
    const isUserLoggedIn = useShopsysSelector((state) => state.user.isUserLoggedIn);
    const currentDomainConfig = useShopsysSelector((state) => state.domain);
    const ordersData = useOrders(currentDomainConfig);

    useEffect(() => {
        if (isUserLoggedIn === false) {
            router.push('/');
        }
    }, []);

    if (isUserLoggedIn === true) {
        return (
            <StaticUrlGuard domainUrl={domainUrl}>
                <CommonLayout>
                    <Orders orders={ordersData?.orders} totalCount={ordersData?.totalCount} />
                </CommonLayout>
            </StaticUrlGuard>
        );
    }

    return null;
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, [
        { query: NotificationBarsDocumentApi },
        { query: NavigationQueryDocumentApi },
    ]);
});

export default Index;

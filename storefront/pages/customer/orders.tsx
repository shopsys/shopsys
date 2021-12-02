import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { ReactElement, useEffect } from 'react';

import CommonLayout from 'components/Layout/CommonLayout';
import { getOrders } from 'connectors/customer/Orders';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import Orders from 'components/Pages/Customer/Orders';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useRouter } from 'next/router';

function Index(): ReactElement | null {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const router = useRouter();
    const isUserLoggedIn = useShopsysSelector((state) => state.user.isUserLoggedIn);
    const currentDomainConfig = useShopsysSelector((state) => state.domain);
    const ordersData = getOrders(currentDomainConfig);

    if (isUserLoggedIn === true) {
        return (
            <StaticUrlGuard domainUrl={domainUrl}>
                <CommonLayout>
                    <Orders orders={ordersData?.orders} totalCount={ordersData?.totalCount} />
                </CommonLayout>
            </StaticUrlGuard>
        );
    }

    useEffect(() => {
        router.push('/');
    }, []);

    return null;
}

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, [{ query: NavigationQueryDocumentApi }]);
});

export default Index;

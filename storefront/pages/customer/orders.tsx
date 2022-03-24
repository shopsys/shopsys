import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import Orders from 'components/Pages/Customer/Orders';
import PageGuard from 'components/Helpers/PageGuard';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useOrders } from 'connectors/customer/Orders';

const Index: FC = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const isUserLoggedIn = useShopsysSelector((state) => state.user.isUserLoggedIn);
    const currentDomainConfig = useShopsysSelector((state) => state.domain);
    const ordersData = useOrders(currentDomainConfig);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <PageGuard accessCondition={isUserLoggedIn} errorRedirectUrl="/">
                <CommonLayout>
                    <Orders orders={ordersData?.orders} totalCount={ordersData?.totalCount} />
                </CommonLayout>
            </PageGuard>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default Index;

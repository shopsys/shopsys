import PageGuard from 'components/Helpers/PageGuard';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import Orders from 'components/Pages/Customer/Orders';
import { useOrders } from 'connectors/customer/Orders';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const Index: FC = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const { isUserLoggedIn } = useCurrentUserData();
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

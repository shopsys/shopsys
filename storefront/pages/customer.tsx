import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import Customer from 'components/Pages/Customer';
import { FC } from 'react';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import PageGuard from 'components/Helpers/PageGuard';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';

const CustomerPage: FC = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const isUserLoggedIn = useShopsysSelector((state) => state.user.isUserLoggedIn);

    return (
        <PageGuard accessCondition={isUserLoggedIn} errorRedirectUrl="/">
            <StaticUrlGuard domainUrl={domainUrl}>
                <CommonLayout>
                    <Customer />
                </CommonLayout>
            </StaticUrlGuard>
        </PageGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default CustomerPage;

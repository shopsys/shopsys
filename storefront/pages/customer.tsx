import MetaRobots from 'components/Basic/Head/MetaRobots';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import Customer from 'components/Pages/Customer';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const CustomerPage: FC = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <CommonLayout>
                <Customer />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, true);
});

export default CustomerPage;

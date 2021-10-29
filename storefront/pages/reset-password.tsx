import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import ResetPassword from 'components/Pages/ResetPassword';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';

const Index: FC<ServerSidePropsType> = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout>
                <ResetPassword />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, [NavigationQueryDocumentApi]);
});

export default Index;

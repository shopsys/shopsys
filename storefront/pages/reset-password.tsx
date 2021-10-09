import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { navigationQuery } from 'connectors/navigation/Navigation';
import { nextReduxWrapper } from 'redux/main';
import ResetPassword from 'components/Pages/ResetPassword';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';

const Index: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <ResetPassword />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    return initServerSideProps(context, store, [navigationQuery]);
});

export default Index;

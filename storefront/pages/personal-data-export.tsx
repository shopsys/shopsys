import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import PersonalDataExport from 'components/Pages/PersonalData/Export';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';

const PersonalDataExportPage: FC = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout>
                <PersonalDataExport />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, [{ query: NavigationQueryDocumentApi }]);
});

export default PersonalDataExportPage;

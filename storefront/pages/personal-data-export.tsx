import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import PersonalDataExport from 'components/Pages/PersonalData/Export';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

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
    return initServerSideProps(context, store);
});

export default PersonalDataExportPage;

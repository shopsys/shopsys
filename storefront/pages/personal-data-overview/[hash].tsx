import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Error404Content } from 'components/Pages/ErrorPage/404/Error404Content';
import { PersonalDataDetailContent } from 'components/Pages/PersonalData/Detail/PersonalDataDetailContent';
import { PersonalDataDetailQueryDocumentApi, usePersonalDataDetailQueryApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { NextPage } from 'next';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

type PersonalDataDetailPageProps = {
    hash: string;
};

const PersonalDataOverviewByHashPage: NextPage<PersonalDataDetailPageProps> = ({ hash }) => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);

    const [{ data }] = usePersonalDataDetailQueryApi({ variables: { hash } });

    if (!data) {
        return <Error404Content />;
    }

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout>
                <PersonalDataDetailContent data={data} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);

    const hash = context.query.hash ?? '';

    const ssrProps = await initServerSideProps(context, store, false, [
        { query: PersonalDataDetailQueryDocumentApi, variables: { hash } },
    ]);

    return {
        props: {
            ...('props' in ssrProps ? ssrProps.props : {}),
            hash,
        },
    };
});

export default PersonalDataOverviewByHashPage;

import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Error404Content } from 'components/Pages/ErrorPage/404/Error404Content';
import { PersonalDataDetailContent } from 'components/Pages/PersonalData/Detail/PersonalDataDetailContent';
import { PersonalDataDetailQueryDocumentApi, usePersonalDataDetailQueryApi } from 'graphql/generated';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const PersonalDataOverviewByHashPage: NextPage = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const { query } = useRouter();
    const hash = getStringFromUrlQuery(query.hash);

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

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => {
            const hash = context.query.hash ?? '';

            return initServerSideProps({
                context,
                store,
                prefetchedQueries: [{ query: PersonalDataDetailQueryDocumentApi, variables: { hash } }],
                redisClient,
            });
        },
        store,
    ),
);

export default PersonalDataOverviewByHashPage;

import { CommonLayout } from 'components/Layout/CommonLayout';
import { Error404Content } from 'components/Pages/ErrorPage/404/Error404Content';
import { PersonalDataDetailContent } from 'components/Pages/PersonalData/Detail/PersonalDataDetailContent';
import { PersonalDataDetailQueryDocumentApi, usePersonalDataDetailQueryApi } from 'graphql/generated';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { nextReduxWrapper } from 'redux/main';

const PersonalDataOverviewByHashPage: NextPage = () => {
    const { query } = useRouter();
    const hash = getStringFromUrlQuery(query.hash);

    const [{ data }] = usePersonalDataDetailQueryApi({ variables: { hash } });

    if (!data) {
        return <Error404Content />;
    }

    return (
        <CommonLayout>
            <PersonalDataDetailContent data={data} />
        </CommonLayout>
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

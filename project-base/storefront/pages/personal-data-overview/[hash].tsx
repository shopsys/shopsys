import { CommonLayout } from 'components/Layout/CommonLayout';
import { Error404Content } from 'components/Pages/ErrorPage/Error404Content';
import { PersonalDataDetailContent } from 'components/Pages/PersonalData/Detail/PersonalDataDetailContent';
import { PersonalDataDetailQueryDocumentApi, usePersonalDataDetailQueryApi } from 'graphql/generated';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { NextPage } from 'next';
import { useRouter } from 'next/router';

const PersonalDataOverviewByHashPage: NextPage = () => {
    const { query } = useRouter();
    const hash = getStringFromUrlQuery(query.hash);

    const [{ data }] = useQueryError(usePersonalDataDetailQueryApi({ variables: { hash } }));

    if (!data) {
        return <Error404Content />;
    }

    return (
        <CommonLayout>
            <PersonalDataDetailContent data={data} />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient) => async (context) => {
    const hash = context.query.hash ?? '';

    return initServerSideProps({
        context,
        prefetchedQueries: [{ query: PersonalDataDetailQueryDocumentApi, variables: { hash } }],
        redisClient,
    });
});

export default PersonalDataOverviewByHashPage;

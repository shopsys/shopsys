import { SkeletonPagePersonalDataOverview } from 'components/Blocks/Skeleton/SkeletonPagePersonalDataOverview';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PersonalDataDetailContent } from 'components/Pages/PersonalData/Detail/PersonalDataDetailContent';
import {
    usePersonalDataDetailQuery,
    PersonalDataDetailQueryVariables,
} from 'graphql/requests/personalData/queries/PersonalDataDetailQuery.generated';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';

const PersonalDataOverviewByHashPage: NextPage = () => {
    const { t } = useTranslation();
    const { query } = useRouter();
    const hash = getStringFromUrlQuery(query.hash);

    const [{ data, fetching }] = usePersonalDataDetailQuery({ variables: { hash } });

    const content = data ? (
        <PersonalDataDetailContent data={data} />
    ) : (
        <Webline>
            <p className="my-28 text-center text-2xl">{t('Could not find personal data overview.')}</p>
        </Webline>
    );

    return <CommonLayout>{fetching ? <SkeletonPagePersonalDataOverview /> : content}</CommonLayout>;
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps<PersonalDataDetailQueryVariables>({
                context,
                redisClient,
                domainConfig,
                t,
            }),
);

export default PersonalDataOverviewByHashPage;

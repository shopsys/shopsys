import { SkeletonPagePersonalDataOverview } from 'components/Blocks/Skeleton/SkeletonPagePersonalDataOverview';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PersonalDataDetailContent } from 'components/Pages/PersonalData/Detail/PersonalDataDetailContent';
import { PersonalDataDetailQueryVariablesApi, usePersonalDataDetailQueryApi } from 'graphql/generated';
import { getStringFromUrlQuery } from 'helpers/parsing/urlParsing';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';

const PersonalDataOverviewByHashPage: NextPage = () => {
    const { t } = useTranslation();
    const { query } = useRouter();
    const hash = getStringFromUrlQuery(query.hash);

    const [{ data, fetching }] = usePersonalDataDetailQueryApi({ variables: { hash } });

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
            initServerSideProps<PersonalDataDetailQueryVariablesApi>({
                context,
                redisClient,
                domainConfig,
                t,
            }),
);

export default PersonalDataOverviewByHashPage;

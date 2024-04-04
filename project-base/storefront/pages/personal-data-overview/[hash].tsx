import { SkeletonPagePersonalDataOverview } from 'components/Blocks/Skeleton/SkeletonPagePersonalDataOverview';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PersonalDataDetailContent } from 'components/Pages/PersonalData/Detail/PersonalDataDetailContent';
import {
    usePersonalDataDetailQuery,
    TypePersonalDataDetailQueryVariables,
} from 'graphql/requests/personalData/queries/PersonalDataDetailQuery.generated';
import { NextPage } from 'next';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

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
            initServerSideProps<TypePersonalDataDetailQueryVariables>({
                context,
                redisClient,
                domainConfig,
                t,
            }),
);

export default PersonalDataOverviewByHashPage;

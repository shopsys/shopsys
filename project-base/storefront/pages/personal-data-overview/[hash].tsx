import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
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

    const [{ data: personalDataDetailData, fetching: arePersonalDataFetching }] = usePersonalDataDetailQuery({
        variables: { hash },
    });

    const content = personalDataDetailData ? (
        <PersonalDataDetailContent personalDataDetail={personalDataDetailData} />
    ) : (
        <Webline>
            <div className="my-28 flex items-center justify-center">
                <InfoIcon className="mr-4 w-8" />
                <div className="h3">{t('Could not find personal data overview.')}</div>
            </div>
        </Webline>
    );

    return <CommonLayout>{arePersonalDataFetching ? <SkeletonPagePersonalDataOverview /> : content}</CommonLayout>;
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

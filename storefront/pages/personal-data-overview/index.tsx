import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PersonalDataOverviewContent } from 'components/Pages/PersonalData/Overview/PersonalDataOverviewContent';
import { PersonalDataPageTextQueryDocumentApi, usePersonalDataPageTextQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const PersonalDataOverviewPage: FC = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [personalDataOverviewUrl] = getInternationalizedStaticUrls(['/personal-data-overview'], domainUrl);
    const [personalDataPageTextResult] = usePersonalDataPageTextQueryApi();
    const breadcrumbs = useMemo(
        () => [{ name: t('Personal Data Overview'), slug: personalDataOverviewUrl }],
        [personalDataOverviewUrl, t],
    );
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Personal Data Overview')}>
                <PersonalDataOverviewContent
                    breadcrumbs={breadcrumbs}
                    contentSiteText={personalDataPageTextResult.data?.personalDataPage?.displaySiteContent}
                />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) =>
            initServerSideProps({
                context,
                store,
                prefetchedQueries: [{ query: PersonalDataPageTextQueryDocumentApi }],
                redisClient,
            }),
        store,
    ),
);

export default PersonalDataOverviewPage;

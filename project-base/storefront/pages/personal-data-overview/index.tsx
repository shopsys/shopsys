import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PersonalDataOverviewContent } from 'components/Pages/PersonalData/Overview/PersonalDataOverviewContent';
import {
    BreadcrumbFragmentApi,
    PersonalDataPageTextQueryDocumentApi,
    usePersonalDataPageTextQueryApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';

import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'types/gtm/enums';

const PersonalDataOverviewPage: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [personalDataOverviewUrl] = getInternationalizedStaticUrls(['/personal-data-overview'], url);
    const [personalDataPageTextResult] = usePersonalDataPageTextQueryApi();
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('Personal Data Overview'), slug: personalDataOverviewUrl },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

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

export const getServerSideProps = getServerSidePropsWithRedisClient(
    (redisClient) => async (context) =>
        initServerSideProps({
            context,
            prefetchedQueries: [{ query: PersonalDataPageTextQueryDocumentApi }],
            redisClient,
        }),
);

export default PersonalDataOverviewPage;

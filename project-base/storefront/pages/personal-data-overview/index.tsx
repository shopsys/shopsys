import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PersonalDataOverviewContent } from 'components/Pages/PersonalData/Overview/PersonalDataOverviewContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { BreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    usePersonalDataPageTextQuery,
    PersonalDataPageTextQueryDocument,
} from 'graphql/requests/personalData/queries/PersonalDataPageTextQuery.generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import useTranslation from 'next-translate/useTranslation';

const PersonalDataOverviewPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [personalDataOverviewUrl] = getInternationalizedStaticUrls(['/personal-data-overview'], url);
    const [personalDataPageTextResult] = usePersonalDataPageTextQuery();
    const breadcrumbs: BreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Personal Data Overview'), slug: personalDataOverviewUrl },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout breadcrumbs={breadcrumbs} title={t('Personal Data Overview')}>
                <PersonalDataOverviewContent
                    contentSiteText={personalDataPageTextResult.data?.personalDataPage?.displaySiteContent}
                />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                prefetchedQueries: [{ query: PersonalDataPageTextQueryDocument }],
                redisClient,
                domainConfig,
                t,
            }),
);

export default PersonalDataOverviewPage;

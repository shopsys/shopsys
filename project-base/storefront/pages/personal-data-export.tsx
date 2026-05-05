import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PersonalDataExportContent } from 'components/Pages/PersonalData/Export/PersonalDataExportContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    PersonalDataPageTextQueryDocument,
    usePersonalDataPageTextQuery,
} from 'graphql/requests/personalData/queries/PersonalDataPageTextQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const PersonalDataExportPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [personalDataExportUrl] = getInternationalizedStaticUrls(['/personal-data-export'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Personal data export'), slug: personalDataExportUrl },
    ];
    const [personalDataPageTextResult] = usePersonalDataPageTextQuery();

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.other, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout breadcrumbs={breadcrumbs} title={t('Personal data export')}>
                <PersonalDataExportContent
                    contentSiteText={personalDataPageTextResult.data?.personalDataPage?.exportSiteContent}
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

export default PersonalDataExportPage;

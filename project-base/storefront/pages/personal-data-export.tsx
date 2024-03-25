import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PersonalDataExportContent } from 'components/Pages/PersonalData/Export/PersonalDataExportContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { BreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    usePersonalDataPageTextQuery,
    PersonalDataPageTextQueryDocument,
} from 'graphql/requests/personalData/queries/PersonalDataPageTextQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import useTranslation from 'next-translate/useTranslation';

const PersonalDataExportPage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [personalDataExportUrl] = getInternationalizedStaticUrls(['/personal-data-export'], url);
    const breadcrumbs: BreadcrumbFragment[] = [
        { __typename: 'Link', name: t('Personal Data Export'), slug: personalDataExportUrl },
    ];
    const [personalDataPageTextResult] = usePersonalDataPageTextQuery();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout breadcrumbs={breadcrumbs} title={t('Personal Data Export')}>
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

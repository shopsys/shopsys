import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PersonalDataExportContent } from 'components/Pages/PersonalData/Export/PersonalDataExportContent';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'gtm/types/enums';
import { BreadcrumbFragmentApi } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    usePersonalDataPageTextQueryApi,
    PersonalDataPageTextQueryDocumentApi,
} from 'graphql/requests/personalData/queries/PersonalDataPageTextQuery.generated';

const PersonalDataExportPage: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [personalDataExportUrl] = getInternationalizedStaticUrls(['/personal-data-export'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('Personal Data Export'), slug: personalDataExportUrl },
    ];
    const [personalDataPageTextResult] = usePersonalDataPageTextQueryApi();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Personal Data Export')}>
                <PersonalDataExportContent
                    breadcrumbs={breadcrumbs}
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
                prefetchedQueries: [{ query: PersonalDataPageTextQueryDocumentApi }],
                redisClient,
                domainConfig,
                t,
            }),
);

export default PersonalDataExportPage;

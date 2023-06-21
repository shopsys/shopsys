import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PersonalDataExportContent } from 'components/Pages/PersonalData/Export/PersonalDataExportContent';
import {
    BreadcrumbFragmentApi,
    PersonalDataPageTextQueryDocumentApi,
    usePersonalDataPageTextQueryApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'types/gtm/enums';

const PersonalDataExportPage: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [personalDataExportUrl] = getInternationalizedStaticUrls(['/personal-data-export'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('Personal Data Export'), slug: personalDataExportUrl },
    ];
    const [personalDataPageTextResult] = useQueryError(usePersonalDataPageTextQueryApi());

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

export const getServerSideProps = getServerSidePropsWithRedisClient(
    (redisClient) => async (context) =>
        initServerSideProps({
            context,
            prefetchedQueries: [{ query: PersonalDataPageTextQueryDocumentApi }],
            redisClient,
        }),
);

export default PersonalDataExportPage;

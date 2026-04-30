import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CatalogContent } from 'components/Pages/Catalog/CatalogContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { NavigationQueryDocument } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const CatalogPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [catalogUrl] = getInternationalizedStaticUrls(['/catalog'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Catalog'), slug: catalogUrl }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.catalog, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} title={t('Catalog')}>
            <Webline>
                <h1 className="mb-4">{t('Catalog')}</h1>
            </Webline>

            <CatalogContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    return initServerSideProps({
        context,
        prefetchedQueries: [{ query: NavigationQueryDocument }],
        redisClient,
        domainConfig,
        t,
    });
});

export default CatalogPage;

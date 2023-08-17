import { CommonLayout } from 'components/Layout/CommonLayout';
import { StoresContent } from 'components/Pages/Stores/StoresContent';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { GtmPageType } from 'gtm/types/enums';
import { BreadcrumbFragmentApi } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { useStoresQueryApi, StoresQueryDocumentApi } from 'graphql/requests/stores/queries/StoresQuery.generated';

const StoresPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const [{ data: storesData }] = useStoresQueryApi();
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Department stores'), slug: '' }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.stores, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <CommonLayout title={t('Stores')}>
                {storesData?.stores !== undefined && (
                    <StoresContent stores={storesData.stores} breadcrumbs={breadcrumbs} />
                )}
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                prefetchedQueries: [{ query: StoresQueryDocumentApi }],
                redisClient,
                domainConfig,
                t,
            }),
);

export default StoresPage;

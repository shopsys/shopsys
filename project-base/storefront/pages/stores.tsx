import { CommonLayout } from 'components/Layout/CommonLayout';
import { StoresContent } from 'components/Pages/Stores/StoresContent';
import { BreadcrumbFragmentApi, StoresQueryDocumentApi, useStoresQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import useTranslation from 'next-translate/useTranslation';

const StoresPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const [{ data: storesData, fetching }] = useStoresQueryApi();
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Department stores'), slug: '' }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.stores, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} isFetchingData={fetching} title={t('Stores')}>
            {storesData?.stores && <StoresContent stores={storesData.stores} />}
        </CommonLayout>
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

import { CommonLayout } from 'components/Layout/CommonLayout';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { StoresQueryDocument, useStoresQuery } from 'graphql/requests/stores/queries/StoresQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import dynamic from 'next/dynamic';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const StoresWrapper = dynamic(() =>
    import('components/Blocks/StoreList/StoresWrapper').then((mod) => mod.StoresWrapper),
);

const StoresPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const [{ data: storesData, fetching: isStoresFetching }] = useStoresQuery();
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Department stores'), slug: '' }];

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.stores, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} isFetchingData={isStoresFetching} title={t('Stores')}>
            {storesData?.stores && <StoresWrapper />}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                prefetchedQueries: [{ query: StoresQueryDocument }],
                redisClient,
                domainConfig,
                t,
            }),
);

export default StoresPage;

import { STORE_LIST_PAGE_SIZE } from 'components/Blocks/StoreList/constants';
import { StoreListError } from 'components/Blocks/StoreList/StoreListError';
import { usePaginatedStoreConnection } from 'components/Blocks/StoreList/usePaginatedStoreConnection';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { MapStoresQueryDocument } from 'graphql/requests/stores/queries/MapStoresQuery.generated';
import { StoresQueryDocument, TypeStoresQuery } from 'graphql/requests/stores/queries/StoresQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import dynamic from 'next/dynamic';
import { useCallback } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const StoresWrapper = dynamic(() =>
    import('components/Blocks/StoreList/StoresWrapper').then((mod) => mod.StoresWrapper),
);

const StoresPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const getStoreConnectionFromData = useCallback((data: TypeStoresQuery | undefined) => data?.stores, []);
    const {
        appliedSearchTextValue,
        isDistanceFromSearchText,
        isFetchingStores,
        isInitialStoresFetching,
        isLoadingMoreStores,
        loadMoreStores,
        searchTextValue,
        setSearchTextValue,
        setUserCoordinates,
        storeConnectionError,
        stores,
        userCoordinates,
    } = usePaginatedStoreConnection<TypeStoresQuery>({
        queryDocument: StoresQueryDocument,
        getStoreConnectionFromData,
    });
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Department stores'), slug: '' }];
    const storeConnectionErrorMessage = t('Stores could not be loaded. Please try again later.');

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.stores, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} isFetchingData={isInitialStoresFetching} title={t('Stores')}>
            {storeConnectionError && stores === null && (
                <Webline>
                    <h1 className="mb-4">{t('Stores')}</h1>
                    <StoreListError message={storeConnectionErrorMessage} />
                </Webline>
            )}

            {stores && (
                <StoresWrapper
                    appliedSearchTextValue={appliedSearchTextValue}
                    isDistanceFromSearchText={isDistanceFromSearchText}
                    isFetchingStores={isFetchingStores}
                    isLoadingMoreStores={isLoadingMoreStores}
                    searchTextValue={searchTextValue}
                    storeConnectionErrorMessage={storeConnectionError ? storeConnectionErrorMessage : undefined}
                    stores={stores}
                    userCoordinates={userCoordinates}
                    onLoadMoreStoresCallback={loadMoreStores}
                    onSearchTextCallback={setSearchTextValue}
                    onUserCoordinatesCallback={setUserCoordinates}
                />
            )}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                prefetchedQueries: [
                    {
                        query: StoresQueryDocument,
                        variables: {
                            searchText: null,
                            coordinates: null,
                            first: STORE_LIST_PAGE_SIZE,
                            after: null,
                        },
                    },
                    {
                        query: MapStoresQueryDocument,
                    },
                ],
                redisClient,
                domainConfig,
                t,
            }),
);

export default StoresPage;

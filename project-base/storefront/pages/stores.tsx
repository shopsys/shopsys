import { CommonLayout } from 'components/Layout/CommonLayout';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { StoresQueryDocument, useStoresQuery } from 'graphql/requests/stores/queries/StoresQuery.generated';
import { TypeCoordinates } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import dynamic from 'next/dynamic';
import { useCallback, useEffect, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { useDebounce } from 'utils/useDebounce';

const StoresWrapper = dynamic(() =>
    import('components/Blocks/StoreList/StoresWrapper').then((mod) => mod.StoresWrapper),
);

const StoresPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const defaultUserCoordinates = useSessionStore((s) => s.coordinates);
    const [searchTextValue, setSearchTextValue] = useState<string>('');
    const [userCoordinates, setUserCoordinates] = useState<TypeCoordinates | null>(defaultUserCoordinates);
    const [stores, setStores] = useState<TypeListedStoreConnectionFragment | null>(null);
    const debouncedSearchTextValue = useDebounce(searchTextValue, 700);
    const [{ data: storesData, fetching: isStoresFetching }] = useStoresQuery({
        variables: { searchText: debouncedSearchTextValue || null, coordinates: userCoordinates },
    });
    const isInitialStoresFetching = isStoresFetching && stores === null;
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Department stores'), slug: '' }];

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.stores, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    useEffect(() => {
        if (storesData?.stores) {
            setStores(storesData.stores);
        }
    }, [storesData?.stores]);

    const onSearchTextHandler = useCallback((searchText: string) => {
        setSearchTextValue(searchText);
    }, []);

    const onUserCoordinatesHandler = useCallback((coordinates: TypeCoordinates | null) => {
        setUserCoordinates(coordinates);
    }, []);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} isFetchingData={isInitialStoresFetching} title={t('Stores')}>
            {stores && (
                <StoresWrapper
                    isFetchingStores={isStoresFetching}
                    searchTextValue={searchTextValue}
                    stores={stores}
                    userCoordinates={userCoordinates}
                    onSearchTextCallback={onSearchTextHandler}
                    onUserCoordinatesCallback={onUserCoordinatesHandler}
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
                prefetchedQueries: [{ query: StoresQueryDocument, variables: { searchText: null, coordinates: null } }],
                redisClient,
                domainConfig,
                t,
            }),
);

export default StoresPage;

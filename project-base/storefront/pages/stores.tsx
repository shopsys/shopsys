import { STORE_LIST_PAGE_SIZE } from 'components/Blocks/StoreList/constants';
import { mergeStoreConnections } from 'components/Blocks/StoreList/mergeStoreConnections';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import {
    StoresQueryDocument,
    TypeStoresQuery,
    TypeStoresQueryVariables,
    useStoresQuery,
} from 'graphql/requests/stores/queries/StoresQuery.generated';
import { TypeCoordinates } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import dynamic from 'next/dynamic';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useClient } from 'urql';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { useDebounce } from 'utils/useDebounce';

const StoresWrapper = dynamic(() =>
    import('components/Blocks/StoreList/StoresWrapper').then((mod) => mod.StoresWrapper),
);

const StoresPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const client = useClient();
    const defaultUserCoordinates = useSessionStore((s) => s.coordinates);
    const [searchTextValue, setSearchTextValue] = useState<string>('');
    const [userCoordinates, setUserCoordinates] = useState<TypeCoordinates | null>(defaultUserCoordinates);
    const [stores, setStores] = useState<TypeListedStoreConnectionFragment | null>(null);
    const [isLoadingMoreStores, setIsLoadingMoreStores] = useState(false);
    const debouncedSearchTextValue = useDebounce(searchTextValue, 700);
    const isSearchTextDebouncing = searchTextValue !== debouncedSearchTextValue;
    const isDistanceFromSearchText = debouncedSearchTextValue.trim() !== '';
    const storesQueryVariables = useMemo(
        () => ({
            searchText: debouncedSearchTextValue || null,
            coordinates: userCoordinates,
            first: STORE_LIST_PAGE_SIZE,
            after: null,
        }),
        [debouncedSearchTextValue, userCoordinates],
    );
    const storesQueryKey = JSON.stringify(storesQueryVariables);
    const storesQueryKeyRef = useRef(storesQueryKey);
    const [{ data: storesData, fetching: isStoresFetching }] = useStoresQuery({
        variables: storesQueryVariables,
    });
    const isInitialStoresFetching = isStoresFetching && stores === null;
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Department stores'), slug: '' }];

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.stores, breadcrumbs);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    useEffect(() => {
        storesQueryKeyRef.current = storesQueryKey;
        setIsLoadingMoreStores(false);
    }, [storesQueryKey]);

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

    const onLoadMoreStoresHandler = useCallback(async () => {
        if (stores === null || !stores.pageInfo.hasNextPage || stores.pageInfo.endCursor === null) {
            return;
        }

        if (isStoresFetching || isLoadingMoreStores || isSearchTextDebouncing) {
            return;
        }

        const requestedStoresQueryKey = storesQueryKey;

        setIsLoadingMoreStores(true);

        try {
            const storesResponse = await client
                .query<TypeStoresQuery, TypeStoresQueryVariables>(StoresQueryDocument, {
                    ...storesQueryVariables,
                    after: stores.pageInfo.endCursor,
                })
                .toPromise();

            if (storesQueryKeyRef.current !== requestedStoresQueryKey || !storesResponse.data?.stores) {
                return;
            }

            setStores((currentStores) =>
                currentStores === null
                    ? storesResponse.data!.stores
                    : mergeStoreConnections(currentStores, storesResponse.data!.stores),
            );
        } finally {
            setIsLoadingMoreStores(false);
        }
    }, [
        client,
        isLoadingMoreStores,
        isSearchTextDebouncing,
        isStoresFetching,
        stores,
        storesQueryKey,
        storesQueryVariables,
    ]);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} isFetchingData={isInitialStoresFetching} title={t('Stores')}>
            {stores && (
                <StoresWrapper
                    isDistanceFromSearchText={isDistanceFromSearchText}
                    isFetchingStores={isStoresFetching || isSearchTextDebouncing}
                    isLoadingMoreStores={isLoadingMoreStores}
                    searchTextValue={searchTextValue}
                    stores={stores}
                    userCoordinates={userCoordinates}
                    onLoadMoreStoresCallback={onLoadMoreStoresHandler}
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
                ],
                redisClient,
                domainConfig,
                t,
            }),
);

export default StoresPage;

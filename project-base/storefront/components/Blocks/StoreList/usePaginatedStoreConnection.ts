import { STORE_LIST_PAGE_SIZE } from 'components/Blocks/StoreList/constants';
import { mergeStoreConnections } from 'components/Blocks/StoreList/mergeStoreConnections';
import { DocumentNode } from 'graphql';
import { TypeListedStoreConnectionFragment } from 'graphql/requests/stores/fragments/ListedStoreConnectionFragment.generated';
import { TypeCoordinates } from 'graphql/types';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { CombinedError, RequestPolicy, useClient, useQuery } from 'urql';
import { useDebounce } from 'utils/useDebounce';

type StoreConnectionQueryVariables = {
    searchText?: string | null;
    coordinates?: TypeCoordinates | null;
    first?: number | null;
    after?: string | null;
};

type UsePaginatedStoreConnectionProps<TData, TAdditionalQueryVariables extends Record<string, unknown>> = {
    queryDocument: DocumentNode;
    additionalQueryVariables?: TAdditionalQueryVariables;
    getStoreConnectionFromData: (data: TData | undefined) => TypeListedStoreConnectionFragment | null | undefined;
    requestPolicy?: RequestPolicy;
};

export const usePaginatedStoreConnection = <
    TData,
    TAdditionalQueryVariables extends Record<string, unknown> = Record<string, never>,
>({
    queryDocument,
    additionalQueryVariables,
    getStoreConnectionFromData,
    requestPolicy,
}: UsePaginatedStoreConnectionProps<TData, TAdditionalQueryVariables>) => {
    type QueryVariables = StoreConnectionQueryVariables & TAdditionalQueryVariables;

    const client = useClient();
    const defaultUserCoordinates = useSessionStore((s) => s.coordinates);
    const [searchTextValue, setSearchTextValue] = useState<string>('');
    const [userCoordinates, setUserCoordinates] = useState<TypeCoordinates | null>(defaultUserCoordinates);
    const [isLoadingMoreStores, setIsLoadingMoreStores] = useState(false);
    const [loadMoreStoresError, setLoadMoreStoresError] = useState<CombinedError | undefined>();
    const debouncedSearchTextValue = useDebounce(searchTextValue, 700);
    const isSearchTextDebouncing = searchTextValue !== debouncedSearchTextValue;
    const normalizedSearchTextValue = debouncedSearchTextValue.trim();
    const isDistanceFromSearchText = normalizedSearchTextValue !== '';

    const queryVariables = useMemo(
        () =>
            ({
                ...additionalQueryVariables,
                searchText: normalizedSearchTextValue || null,
                coordinates: isDistanceFromSearchText ? null : userCoordinates,
                first: STORE_LIST_PAGE_SIZE,
                after: null,
            }) as QueryVariables,
        [additionalQueryVariables, isDistanceFromSearchText, normalizedSearchTextValue, userCoordinates],
    );
    const queryKey = JSON.stringify(queryVariables);
    const queryKeyRef = useRef(queryKey);
    const [{ data, fetching: isStoreConnectionFetching, error: storeConnectionQueryError }] = useQuery<
        TData,
        QueryVariables
    >({
        query: queryDocument,
        variables: queryVariables,
        requestPolicy,
    });
    const initialStoreConnection = getStoreConnectionFromData(data);
    const [stores, setStores] = useState<TypeListedStoreConnectionFragment | null>(initialStoreConnection ?? null);

    useEffect(() => {
        queryKeyRef.current = queryKey;
        setIsLoadingMoreStores(false);
        setLoadMoreStoresError(undefined);
    }, [queryKey]);

    useEffect(() => {
        const storeConnection = getStoreConnectionFromData(data);

        if (storeConnection) {
            setStores(storeConnection);
            setLoadMoreStoresError(undefined);
        }
    }, [data, getStoreConnectionFromData]);

    const loadMoreStores = useCallback(async () => {
        if (stores === null || !stores.pageInfo.hasNextPage || stores.pageInfo.endCursor === null) {
            return;
        }

        if (isStoreConnectionFetching || isLoadingMoreStores || isSearchTextDebouncing) {
            return;
        }

        const requestedQueryKey = queryKey;

        setIsLoadingMoreStores(true);
        setLoadMoreStoresError(undefined);

        try {
            const storesResponse = await client
                .query<TData, QueryVariables>(
                    queryDocument,
                    {
                        ...queryVariables,
                        after: stores.pageInfo.endCursor,
                    },
                    { requestPolicy },
                )
                .toPromise();

            if (queryKeyRef.current !== requestedQueryKey) {
                return;
            }

            if (storesResponse.error) {
                setLoadMoreStoresError(storesResponse.error);

                return;
            }

            const nextStoreConnection = getStoreConnectionFromData(storesResponse.data);

            if (!nextStoreConnection) {
                return;
            }

            setStores((currentStores) =>
                currentStores === null
                    ? nextStoreConnection
                    : mergeStoreConnections(currentStores, nextStoreConnection),
            );
        } finally {
            if (queryKeyRef.current === requestedQueryKey) {
                setIsLoadingMoreStores(false);
            }
        }
    }, [
        client,
        getStoreConnectionFromData,
        isStoreConnectionFetching,
        isLoadingMoreStores,
        isSearchTextDebouncing,
        queryDocument,
        queryKey,
        queryVariables,
        requestPolicy,
        stores,
    ]);

    return {
        appliedSearchTextValue: normalizedSearchTextValue,
        isDistanceFromSearchText,
        isFetchingStores: isStoreConnectionFetching || isSearchTextDebouncing,
        isInitialStoresFetching: isStoreConnectionFetching && stores === null,
        isLoadingMoreStores,
        loadMoreStores,
        searchTextValue,
        setSearchTextValue,
        setUserCoordinates,
        storeConnectionError: storeConnectionQueryError ?? loadMoreStoresError,
        stores,
        userCoordinates,
    };
};

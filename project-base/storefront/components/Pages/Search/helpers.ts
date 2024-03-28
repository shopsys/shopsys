import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';
import {
    TypeSearchProductsQueryVariables,
    TypeSearchProductsQuery,
    SearchProductsQueryDocument,
} from 'graphql/requests/products/queries/SearchProductsQuery.generated';
import { TypeProductOrderingModeEnum, Maybe, TypeProductFilter } from 'graphql/types';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { calculatePageSize } from 'helpers/loadMore/calculatePageSize';
import { getPageSizeInfo } from 'helpers/loadMore/getPageSizeInfo';
import { hasReadAllProductsFromCache } from 'helpers/loadMore/hasReadAllProductsFromCache';
import { mergeProductEdges } from 'helpers/loadMore/mergeProductEdges';
import { useCurrentFilter } from 'hooks/queryParams/useCurrentFilter';
import { useCurrentLoadMore } from 'hooks/queryParams/useCurrentLoadMore';
import { useCurrentPage } from 'hooks/queryParams/useCurrentPage';
import { useCurrentSearchString } from 'hooks/queryParams/useCurrentSearchString';
import { useCurrentSort } from 'hooks/queryParams/useCurrentSort';
import { useRef, useState, useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useClient, Client } from 'urql';

export const useSearchProductsData = (
    totalProductCount: number,
): [TypeListedProductConnectionFragment['edges'] | undefined, boolean, boolean, boolean] => {
    const client = useClient();
    const currentPage = useCurrentPage();
    const currentFilter = useCurrentFilter();
    const currentSort = useCurrentSort();
    const currentSearchString = useCurrentSearchString();
    const currentLoadMore = useCurrentLoadMore();
    const mappedFilter = mapParametersFilter(currentFilter);

    const previousLoadMoreRef = useRef(currentLoadMore);
    const previousPageRef = useRef(currentPage);
    const initialPageSizeRef = useRef(calculatePageSize(currentLoadMore));

    const userIdentifier = usePersistStore((store) => store.userId)!;

    const [searchProductsData, setSearchProductsData] = useState(
        readSearchProductsFromCache(
            client,
            currentSearchString ?? '',
            currentSort,
            mappedFilter,
            getEndCursor(currentPage),
            initialPageSizeRef.current,
            userIdentifier,
        ),
    );

    const [fetching, setFetching] = useState(!searchProductsData);
    const [loadMoreFetching, setLoadMoreFetching] = useState(false);

    const fetchProducts = async (
        variables: TypeSearchProductsQueryVariables,
        previouslyQueriedProductsFromCache: TypeListedProductConnectionFragment['edges'] | undefined,
    ) => {
        const response = await client
            .query<TypeSearchProductsQuery, TypeSearchProductsQueryVariables>(SearchProductsQueryDocument, variables)
            .toPromise();

        if (!response.data) {
            setSearchProductsData({ products: undefined, hasNextPage: false });

            return;
        }

        setSearchProductsData({
            products: mergeProductEdges(previouslyQueriedProductsFromCache, response.data.productsSearch.edges),
            hasNextPage: response.data.productsSearch.pageInfo.hasNextPage,
        });
        stopFetching();
    };

    const startFetching = () => {
        if (previousLoadMoreRef.current === currentLoadMore || currentLoadMore === 0) {
            setFetching(true);
        } else {
            setLoadMoreFetching(true);
            previousLoadMoreRef.current = currentLoadMore;
        }
    };

    const stopFetching = () => {
        setFetching(false);
        setLoadMoreFetching(false);
    };

    useEffect(() => {
        if (previousPageRef.current !== currentPage) {
            previousPageRef.current = currentPage;
            initialPageSizeRef.current = DEFAULT_PAGE_SIZE;
        }

        const previousProductsFromCache = getPreviousProductsFromCache(
            client,
            currentSearchString ?? '',
            currentSort,
            mappedFilter,
            DEFAULT_PAGE_SIZE,
            currentPage,
            currentLoadMore,
            userIdentifier,
        );

        if (
            hasReadAllProductsFromCache(
                previousProductsFromCache?.length,
                currentLoadMore,
                currentPage,
                totalProductCount,
            )
        ) {
            return;
        }

        const { pageSize, isMoreThanOnePage } = getPageSizeInfo(!!previousProductsFromCache, currentLoadMore);
        const endCursor = getEndCursor(currentPage, isMoreThanOnePage ? undefined : currentLoadMore);

        startFetching();
        fetchProducts(
            {
                endCursor,
                filter: mappedFilter,
                orderingMode: currentSort,
                search: currentSearchString ?? '',
                pageSize,
                isAutocomplete: false,
                userIdentifier,
            },
            previousProductsFromCache,
        );
    }, [currentSearchString, currentSort, JSON.stringify(currentFilter), currentPage, currentLoadMore]);

    return [searchProductsData.products, searchProductsData.hasNextPage, fetching, loadMoreFetching];
};

const readSearchProductsFromCache = (
    client: Client,
    TypeSearchQuery: string,
    orderingMode: TypeProductOrderingModeEnum | null,
    filter: Maybe<TypeProductFilter>,
    endCursor: string,
    pageSize: number,
    userIdentifier: string,
): {
    products: TypeListedProductConnectionFragment['edges'] | undefined;
    hasNextPage: boolean;
} => {
    const dataFromCache = client.readQuery<TypeSearchProductsQuery, TypeSearchProductsQueryVariables>(
        SearchProductsQueryDocument,
        {
            search: TypeSearchQuery,
            orderingMode,
            filter,
            endCursor,
            pageSize,
            isAutocomplete: false,
            userIdentifier,
        },
    )?.data?.productsSearch;

    return {
        products: dataFromCache?.edges,
        hasNextPage: !!dataFromCache?.pageInfo.hasNextPage,
    };
};

const getPreviousProductsFromCache = (
    client: Client,
    TypeSearchQuery: string,
    sort: TypeProductOrderingModeEnum | null,
    filter: Maybe<TypeProductFilter>,
    pageSize: number,
    currentPage: number,
    currentLoadMore: number,
    userIdentifier: string,
) => {
    let cachedPartOfProducts: TypeListedProductConnectionFragment['edges'] | undefined;
    let iterationsCounter = currentLoadMore;

    while (iterationsCounter > 0) {
        const offsetEndCursor = getEndCursor(currentPage + currentLoadMore - iterationsCounter);
        const currentCacheSlice = readSearchProductsFromCache(
            client,
            TypeSearchQuery,
            sort,
            filter,
            offsetEndCursor,
            pageSize,
            userIdentifier,
        ).products;

        if (currentCacheSlice) {
            if (cachedPartOfProducts) {
                cachedPartOfProducts = mergeProductEdges(cachedPartOfProducts, currentCacheSlice);
            } else {
                cachedPartOfProducts = currentCacheSlice;
            }
        } else {
            return undefined;
        }

        iterationsCounter--;
    }

    return cachedPartOfProducts;
};

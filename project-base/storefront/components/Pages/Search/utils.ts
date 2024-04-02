import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { ListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';
import {
    SearchProductsQueryVariables,
    SearchProductsQuery,
    SearchProductsQueryDocument,
} from 'graphql/requests/products/queries/SearchProductsQuery.generated';
import { ProductOrderingModeEnum, Maybe, ProductFilter } from 'graphql/types';
import { useRef, useState, useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useClient, Client } from 'urql';
import { mapParametersFilter } from 'utils/filterOptions/mapParametersFilter';
import { calculatePageSize } from 'utils/loadMore/calculatePageSize';
import { getPageSizeInfo } from 'utils/loadMore/getPageSizeInfo';
import { hasReadAllProductsFromCache } from 'utils/loadMore/hasReadAllProductsFromCache';
import { mergeProductEdges } from 'utils/loadMore/mergeProductEdges';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useCurrentSearchStringQuery } from 'utils/queryParams/useCurrentSearchStringQuery';
import { useCurrentSortQuery } from 'utils/queryParams/useCurrentSortQuery';

export const useSearchProductsData = (
    totalProductCount: number,
): [ListedProductConnectionFragment['edges'] | undefined, boolean, boolean, boolean] => {
    const client = useClient();
    const currentPage = useCurrentPageQuery();
    const currentFilter = useCurrentFilterQuery();
    const currentSort = useCurrentSortQuery();
    const currentSearchString = useCurrentSearchStringQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();
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
        variables: SearchProductsQueryVariables,
        previouslyQueriedProductsFromCache: ListedProductConnectionFragment['edges'] | undefined,
    ) => {
        const response = await client
            .query<SearchProductsQuery, SearchProductsQueryVariables>(SearchProductsQueryDocument, variables)
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
    searchQuery: string,
    orderingMode: ProductOrderingModeEnum | null,
    filter: Maybe<ProductFilter>,
    endCursor: string,
    pageSize: number,
    userIdentifier: string,
): {
    products: ListedProductConnectionFragment['edges'] | undefined;
    hasNextPage: boolean;
} => {
    const dataFromCache = client.readQuery<SearchProductsQuery, SearchProductsQueryVariables>(
        SearchProductsQueryDocument,
        {
            search: searchQuery,
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
    searchQuery: string,
    sort: ProductOrderingModeEnum | null,
    filter: Maybe<ProductFilter>,
    pageSize: number,
    currentPage: number,
    currentLoadMore: number,
    userIdentifier: string,
) => {
    let cachedPartOfProducts: ListedProductConnectionFragment['edges'] | undefined;
    let iterationsCounter = currentLoadMore;

    while (iterationsCounter > 0) {
        const offsetEndCursor = getEndCursor(currentPage + currentLoadMore - iterationsCounter);
        const currentCacheSlice = readSearchProductsFromCache(
            client,
            searchQuery,
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

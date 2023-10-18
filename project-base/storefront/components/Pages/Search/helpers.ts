import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    ProductOrderingModeEnumApi,
    Maybe,
    ProductFilterApi,
    SearchProductsQueryApi,
    SearchProductsQueryVariablesApi,
    SearchProductsQueryDocumentApi,
    ListedProductConnectionFragmentApi,
} from 'graphql/generated';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { calculatePageSize, getPageSizeInfo, hasReadAllProductsFromCache, mergeProductEdges } from 'helpers/loadMore';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRef, useState, useEffect } from 'react';
import { useClient, Client } from 'urql';

export const useSearchProductsData = (
    totalProductCount: number,
): [ListedProductConnectionFragmentApi['edges'] | undefined, boolean, boolean, boolean] => {
    const client = useClient();
    const { filter, sort, currentPage, currentLoadMore, searchString } = useQueryParams();
    const mappedFilter = mapParametersFilter(filter);

    const previousLoadMoreRef = useRef(currentLoadMore);
    const previousPageRef = useRef(currentPage);
    const initialPageSizeRef = useRef(calculatePageSize(currentLoadMore));

    const [searchProductsData, setSearchProductsData] = useState(
        readSearchProductsFromCache(
            client,
            searchString ?? '',
            sort,
            mappedFilter,
            getEndCursor(currentPage),
            initialPageSizeRef.current,
        ),
    );

    const [fetching, setFetching] = useState(!searchProductsData);
    const [loadMoreFetching, setLoadMoreFetching] = useState(false);

    const fetchProducts = async (
        variables: SearchProductsQueryVariablesApi,
        previouslyQueriedProductsFromCache: ListedProductConnectionFragmentApi['edges'] | undefined,
    ) => {
        const response = await client
            .query<SearchProductsQueryApi, SearchProductsQueryVariablesApi>(SearchProductsQueryDocumentApi, variables)
            .toPromise();

        if (!response.data) {
            setSearchProductsData({ products: undefined, hasNextPage: false });

            return;
        }

        setSearchProductsData({
            products: mergeProductEdges(previouslyQueriedProductsFromCache, response.data.products.edges),
            hasNextPage: response.data.products.pageInfo.hasNextPage,
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
            searchString ?? '',
            sort,
            mappedFilter,
            DEFAULT_PAGE_SIZE,
            currentPage,
            currentLoadMore,
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
                orderingMode: sort,
                search: searchString ?? '',
                pageSize,
            },
            previousProductsFromCache,
        );
    }, [searchString, sort, JSON.stringify(filter), currentPage, currentLoadMore]);

    return [searchProductsData.products, searchProductsData.hasNextPage, fetching, loadMoreFetching];
};

const readSearchProductsFromCache = (
    client: Client,
    searchQuery: string,
    orderingMode: ProductOrderingModeEnumApi | null,
    filter: Maybe<ProductFilterApi>,
    endCursor: string,
    pageSize: number,
): {
    products: ListedProductConnectionFragmentApi['edges'] | undefined;
    hasNextPage: boolean;
} => {
    const dataFromCache = client.readQuery<SearchProductsQueryApi, SearchProductsQueryVariablesApi>(
        SearchProductsQueryDocumentApi,
        {
            search: searchQuery,
            orderingMode,
            filter,
            endCursor,
            pageSize,
        },
    )?.data?.products;

    return {
        products: dataFromCache?.edges,
        hasNextPage: !!dataFromCache?.pageInfo.hasNextPage,
    };
};

const getPreviousProductsFromCache = (
    client: Client,
    searchQuery: string,
    sort: ProductOrderingModeEnumApi | null,
    filter: Maybe<ProductFilterApi>,
    pageSize: number,
    currentPage: number,
    currentLoadMore: number,
) => {
    let cachedPartOfProducts: ListedProductConnectionFragmentApi['edges'] | undefined;
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

import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';
import {
    TypeSearchProductsQueryVariables,
    TypeSearchProductsQuery,
    SearchProductsQueryDocument,
} from 'graphql/requests/products/queries/SearchProductsQuery.generated';
import { TypeProductOrderingModeEnum, Maybe, TypeProductFilter } from 'graphql/types';
import { useRef, useState, useEffect } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
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

export const useSearchProductsData = (totalProductCount?: number) => {
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

    const [searchProductsData, setSearchProductsData] = useState<TypeSearchProductsQuery | undefined>();
    const [areSearchProductsFetching, setAreSearchProductsFetching] = useState(!searchProductsData);
    const [isLoadingMoreSearchProducts, setIsLoadingMoreSearchProducts] = useState(false);

    const userIdentifier = useCookiesStore((store) => store.userIdentifier);

    const fetchProducts = async (
        variables: TypeSearchProductsQueryVariables,
        previouslyQueriedProductsFromCache: TypeListedProductConnectionFragment['edges'] | undefined,
    ) => {
        const response = await client
            .query<TypeSearchProductsQuery, TypeSearchProductsQueryVariables>(SearchProductsQueryDocument, variables)
            .toPromise();

        if (!response.data?.productsSearch) {
            return;
        }

        setSearchProductsData({
            ...response.data,
            productsSearch: {
                ...response.data.productsSearch,
                edges: mergeProductEdges(previouslyQueriedProductsFromCache, response.data.productsSearch.edges),
            },
        });
        stopFetching();
    };

    const startFetching = () => {
        if (previousLoadMoreRef.current === currentLoadMore || currentLoadMore === 0) {
            setAreSearchProductsFetching(true);
        } else {
            setIsLoadingMoreSearchProducts(true);
            previousLoadMoreRef.current = currentLoadMore;
        }
    };

    const stopFetching = () => {
        setAreSearchProductsFetching(false);
        setIsLoadingMoreSearchProducts(false);
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

    return {
        searchProductsData: searchProductsData?.productsSearch,
        areSearchProductsFetching,
        isLoadingMoreSearchProducts,
    };
};

const readProductsSearchFromCache = (
    client: Client,
    TypeSearchQuery: string,
    orderingMode: TypeProductOrderingModeEnum | null,
    filter: Maybe<TypeProductFilter>,
    endCursor: string,
    pageSize: number,
    userIdentifier: string,
): TypeListedProductConnectionFragment | undefined => {
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

    return dataFromCache;
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
        const productsSearchFromCache = readProductsSearchFromCache(
            client,
            TypeSearchQuery,
            sort,
            filter,
            offsetEndCursor,
            pageSize,
            userIdentifier,
        );

        if (productsSearchFromCache) {
            if (cachedPartOfProducts) {
                cachedPartOfProducts = mergeProductEdges(cachedPartOfProducts, productsSearchFromCache.edges);
            } else {
                cachedPartOfProducts = productsSearchFromCache.edges;
            }
        } else {
            return undefined;
        }

        iterationsCounter--;
    }

    return cachedPartOfProducts;
};

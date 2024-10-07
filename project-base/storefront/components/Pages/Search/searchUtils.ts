import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';
import {
    TypeSearchProductsQuery,
    TypeSearchProductsQueryVariables,
    SearchProductsQueryDocument,
} from 'graphql/requests/search/queries/SearchProductsQuery.generated';
import {
    TypeSearchQuery,
    TypeSearchQueryVariables,
    SearchQueryDocument,
} from 'graphql/requests/search/queries/SearchQuery.generated';
import { TypeProductOrderingModeEnum, Maybe, TypeProductFilter } from 'graphql/types';
import { useRef, useState, useEffect } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { useClient, Client } from 'urql';
import { mapParametersFilter } from 'utils/filterOptions/mapParametersFilter';
import { calculatePageSize } from 'utils/loadMore/calculatePageSize';
import { getPageSizeInfo } from 'utils/loadMore/getPageSizeInfo';
import { hasReadAllItemsFromCache } from 'utils/loadMore/hasReadAllItemsFromCache';
import { mergeItemEdges } from 'utils/loadMore/mergeItemEdges';
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
    const parameters = mappedFilter?.parameters?.map((parameter) => parameter.parameter);

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
        const searchProductsResponse = await client
            .query<TypeSearchProductsQuery, TypeSearchProductsQueryVariables>(SearchProductsQueryDocument, variables)
            .toPromise();

        if (!searchProductsResponse.data?.productsSearch) {
            return;
        }

        setSearchProductsData({
            ...searchProductsResponse.data,
            productsSearch: {
                ...searchProductsResponse.data.productsSearch,
                edges: mergeItemEdges(
                    previouslyQueriedProductsFromCache,
                    searchProductsResponse.data.productsSearch.edges,
                ) as TypeListedProductConnectionFragment['edges'],
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
            parameters,
        );

        if (
            hasReadAllItemsFromCache(previousProductsFromCache?.length, currentLoadMore, currentPage, totalProductCount)
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
                parameters,
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
    parameters?: string[] | null,
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
            parameters,
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
    parameters?: string[] | null,
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
            parameters,
        );

        if (productsSearchFromCache) {
            if (cachedPartOfProducts) {
                cachedPartOfProducts = mergeItemEdges(
                    cachedPartOfProducts,
                    productsSearchFromCache.edges,
                ) as TypeListedProductConnectionFragment['edges'];
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

export const useSearchQuery = (searchString: string | undefined) => {
    const userIdentifier = useCookiesStore((store) => store.userIdentifier);
    const currentPage = useCurrentPageQuery();
    const currentFilter = useCurrentFilterQuery();
    const currentSort = useCurrentSortQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();
    const mappedFilter = mapParametersFilter(currentFilter);
    const parameters = mappedFilter?.parameters?.map((parameter) => parameter.parameter) ?? [];
    const { pageSize, isMoreThanOnePage } = getPageSizeInfo(false, currentLoadMore);
    const endCursor = getEndCursor(currentPage, isMoreThanOnePage ? undefined : currentLoadMore);
    const client = useClient();
    const [searchData, setSearchData] = useState<TypeSearchQuery | undefined>(undefined);
    const [isSearchFetching, setIsSearchFetching] = useState(true);

    useEffect(() => {
        if (searchString && userIdentifier) {
            setIsSearchFetching(true);
            client
                .query<TypeSearchQuery, TypeSearchQueryVariables>(SearchQueryDocument, {
                    search: searchString!,
                    isAutocomplete: false,
                    userIdentifier,
                    endCursor,
                    filter: mappedFilter,
                    orderingMode: currentSort,
                    pageSize,
                    parameters,
                })
                .then((searchResponse) => {
                    setSearchData(searchResponse.data);
                    setIsSearchFetching(false);
                });
        }
    }, [searchString, userIdentifier]);

    return {
        searchData,
        isSearchFetching,
    };
};

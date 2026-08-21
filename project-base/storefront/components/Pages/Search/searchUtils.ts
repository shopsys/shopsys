import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    SearchProductsQueryDocument,
    TypeSearchProductsQuery,
    TypeSearchProductsQueryVariables,
} from 'graphql/requests/search/queries/SearchProductsQuery.generated';
import {
    SearchQueryDocument,
    TypeSearchQuery,
    TypeSearchQueryVariables,
} from 'graphql/requests/search/queries/SearchQuery.generated';
import { Maybe, TypeProductFilter, TypeProductOrderingModeEnum } from 'graphql/types';
import { useRouter } from 'next/router';
import { useEffect, useEffectEvent, useRef, useState } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { Client, useClient } from 'urql';
import { isExpectedPriceFilterError } from 'utils/errors/expectedErrors';
import { mapParametersFilter } from 'utils/filterOptions/mapParametersFilter';
import { getPageSizeInfo } from 'utils/loadMore/getPageSizeInfo';
import { hasReadAllItemsFromCache } from 'utils/loadMore/hasReadAllItemsFromCache';
import { mergeItemEdges } from 'utils/loadMore/mergeItemEdges';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useCurrentSearchStringQuery } from 'utils/queryParams/useCurrentSearchStringQuery';
import { useCurrentSortQuery } from 'utils/queryParams/useCurrentSortQuery';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type SearchProductsConnection = TypeSearchProductsQuery['productsSearch'];

type UseSearchProductsDataOptions = {
    searchProductsDataFromMainQuery?: SearchProductsConnection;
    totalProductCount?: number;
};

export const useSearchProductsData = ({
    searchProductsDataFromMainQuery,
    totalProductCount,
}: UseSearchProductsDataOptions = {}) => {
    const client = useClient();
    const currentPage = useCurrentPageQuery();
    const currentFilter = useCurrentFilterQuery();
    const currentSort = useCurrentSortQuery();
    const currentSearchString = useCurrentSearchStringQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();
    const mappedFilter = mapParametersFilter(currentFilter);
    const parameters = mappedFilter?.parameters?.map((parameter) => parameter.parameter);
    const userIdentifier = useCookiesStore((store) => store.userIdentifier);
    const currentFilterSerialized = JSON.stringify(currentFilter);
    const searchProductsCriteriaKey = JSON.stringify([
        currentSearchString,
        currentSort,
        currentFilterSerialized,
        userIdentifier,
    ]);

    const previousLoadMoreRef = useRef(currentLoadMore);
    const previousPageRef = useRef(currentPage);
    const previousSearchProductsCriteriaKeyRef = useRef(searchProductsCriteriaKey);
    const latestSearchProductsRequestIdRef = useRef(0);

    const [searchProductsData, setSearchProductsData] = useState<TypeSearchProductsQuery | undefined>(
        searchProductsDataFromMainQuery ? { productsSearch: searchProductsDataFromMainQuery } : undefined,
    );
    const [areSearchProductsFetching, setAreSearchProductsFetching] = useState(!searchProductsDataFromMainQuery);
    const [isLoadingMoreSearchProducts, setIsLoadingMoreSearchProducts] = useState(false);

    useEffect(() => {
        if (!searchProductsDataFromMainQuery) {
            return;
        }

        setSearchProductsData({ productsSearch: searchProductsDataFromMainQuery });
        setAreSearchProductsFetching(false);
        setIsLoadingMoreSearchProducts(false);
    }, [searchProductsDataFromMainQuery]);

    useEffect(() => {
        const searchProductsRequestId = ++latestSearchProductsRequestIdRef.current;
        const hasSearchCriteriaChanged = previousSearchProductsCriteriaKeyRef.current !== searchProductsCriteriaKey;
        const hasPageChanged = previousPageRef.current !== currentPage;
        const hasLoadMoreChanged = previousLoadMoreRef.current !== currentLoadMore;
        const hasPaginationChanged = hasPageChanged || hasLoadMoreChanged;

        previousSearchProductsCriteriaKeyRef.current = searchProductsCriteriaKey;
        previousPageRef.current = currentPage;
        previousLoadMoreRef.current = currentLoadMore;

        if (searchProductsDataFromMainQuery && hasSearchCriteriaChanged) {
            setAreSearchProductsFetching(true);
            setIsLoadingMoreSearchProducts(false);

            return;
        }

        if (searchProductsDataFromMainQuery && !hasPaginationChanged) {
            return;
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

        if (!hasLoadMoreChanged || currentLoadMore === 0) {
            setAreSearchProductsFetching(true);
        } else {
            setIsLoadingMoreSearchProducts(true);
        }

        const fetchProducts = async () => {
            const searchProductsResponse = await client
                .query<TypeSearchProductsQuery, TypeSearchProductsQueryVariables>(SearchProductsQueryDocument, {
                    endCursor,
                    filter: mappedFilter,
                    orderingMode: currentSort,
                    search: currentSearchString ?? '',
                    pageSize,
                    isAutocomplete: false,
                    userIdentifier,
                    parameters,
                })
                .toPromise();

            if (searchProductsRequestId !== latestSearchProductsRequestIdRef.current) {
                return;
            }

            if (!searchProductsResponse.data?.productsSearch) {
                return;
            }

            setSearchProductsData({
                ...searchProductsResponse.data,
                productsSearch: {
                    ...searchProductsResponse.data.productsSearch,
                    edges: mergeItemEdges(
                        previousProductsFromCache,
                        searchProductsResponse.data.productsSearch.edges,
                    ) as SearchProductsConnection['edges'],
                },
            });
            setAreSearchProductsFetching(false);
            setIsLoadingMoreSearchProducts(false);
        };

        fetchProducts();
        // The dependency array is carefully curated to prevent redundant re-fetches.
        // `mappedFilter` and `parameters` are intentionally excluded — they create new references each render, so we track filter changes via `currentFilterSerialized` instead.
    }, [
        currentSearchString,
        currentSort,
        currentFilterSerialized,
        currentPage,
        currentLoadMore,
        client,
        userIdentifier,
        totalProductCount,
        searchProductsCriteriaKey,
        searchProductsDataFromMainQuery,
    ]);

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
): SearchProductsConnection | undefined => {
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
    let cachedPartOfProducts: SearchProductsConnection['edges'] | undefined;
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
                ) as SearchProductsConnection['edges'];
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
    const [loadedSearchString, setLoadedSearchString] = useState<string | undefined>(undefined);
    const latestSearchRequestIdRef = useRef(0);

    const router = useRouter();
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    const fetchSearchData = async (
        requestedSearchString: string,
        mappedFilter: TypeProductFilter | null,
        currentSort: TypeProductOrderingModeEnum | null,
    ) => {
        const searchResponse = await client.query<TypeSearchQuery, TypeSearchQueryVariables>(SearchQueryDocument, {
            search: requestedSearchString,
            isAutocomplete: false,
            userIdentifier,
            endCursor,
            filter: mappedFilter,
            orderingMode: currentSort,
            pageSize,
            parameters,
        });

        return searchResponse;
    };

    const refetchSearchData = async (requestedSearchString: string, searchRequestId: number) => {
        router.replace({
            pathname: searchUrl,
            query: { q: requestedSearchString },
        });

        fetchSearchData(requestedSearchString, null, null).then((retryResponse) => {
            if (searchRequestId !== latestSearchRequestIdRef.current) {
                return;
            }

            setSearchData(retryResponse.data);
            setLoadedSearchString(requestedSearchString);
        });
    };

    const currentFilterSerialized = JSON.stringify(currentFilter);

    const onSearch = useEffectEvent(() => {
        const searchRequestId = ++latestSearchRequestIdRef.current;

        if (searchString && userIdentifier) {
            const requestedSearchString = searchString;

            fetchSearchData(requestedSearchString, mappedFilter, currentSort).then((searchResponse) => {
                if (searchRequestId !== latestSearchRequestIdRef.current) {
                    return;
                }

                if (isExpectedPriceFilterError(searchResponse.error)) {
                    refetchSearchData(requestedSearchString, searchRequestId);

                    return;
                }

                setSearchData(searchResponse.data);
                setLoadedSearchString(requestedSearchString);
            });
        }
    });

    useEffect(() => {
        onSearch();
    }, [searchString, userIdentifier, currentSort, currentFilterSerialized]);

    return {
        searchData,
        isSearchPageFetching: !!searchString && loadedSearchString !== searchString,
    };
};

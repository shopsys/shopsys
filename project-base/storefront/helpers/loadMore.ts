import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { LOAD_MORE_QUERY_PARAMETER_NAME, PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { useQueryParams } from 'hooks/useQueryParams';
import { Redirect } from 'next';
import { useRouter } from 'next/router';
import { ParsedUrlQuery } from 'querystring';
import { useRef, useState, useEffect } from 'react';
import { Client, useClient } from 'urql';
import { getSlugFromUrl, getUrlQueriesWithoutDynamicPageQueries } from 'helpers/parsing/urlParsing';
import { ListedProductConnectionFragmentApi } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';
import {
    BrandProductsQueryDocumentApi,
    BrandProductsQueryVariablesApi,
} from 'graphql/requests/products/queries/BrandProductsQuery.generated';
import {
    CategoryProductsQueryDocumentApi,
    CategoryProductsQueryVariablesApi,
} from 'graphql/requests/products/queries/CategoryProductsQuery.generated';
import {
    FlagProductsQueryDocumentApi,
    FlagProductsQueryVariablesApi,
} from 'graphql/requests/products/queries/FlagProductsQuery.generated';
import { ProductOrderingModeEnumApi, ProductFilterApi, Maybe } from 'graphql/requests/types';

const PRODUCT_LIST_LIMIT = 100;

export const mergeProductEdges = (
    previousProductEdges?: ListedProductConnectionFragmentApi['edges'],
    newProductEdges?: ListedProductConnectionFragmentApi['edges'],
) => [...(previousProductEdges || []), ...(newProductEdges || [])];

export const getPreviousProductsFromCache = (
    queryDocument:
        | typeof BrandProductsQueryDocumentApi
        | typeof FlagProductsQueryDocumentApi
        | typeof CategoryProductsQueryDocumentApi,
    client: Client,
    urlSlug: string,
    sort: ProductOrderingModeEnumApi | null,
    filter: Maybe<ProductFilterApi>,
    pageSize: number,
    initialPageSize: number,
    currentPage: number,
    currentLoadMore: number,
    readProducts: typeof readProductsFromCache,
): ListedProductConnectionFragmentApi['edges'] | undefined => {
    let cachedPartOfProducts: ListedProductConnectionFragmentApi['edges'] | undefined;
    let iterationsCounter = currentLoadMore;

    if (initialPageSize !== pageSize) {
        const offsetEndCursor = getEndCursor(currentPage);
        const currentCacheSlice = readProductsFromCache(
            queryDocument,
            client,
            urlSlug,
            sort,
            filter,
            offsetEndCursor,
            initialPageSize,
        ).products;

        if (currentCacheSlice) {
            cachedPartOfProducts = currentCacheSlice;
            iterationsCounter -= initialPageSize / pageSize;
        } else {
            return undefined;
        }
    }

    while (iterationsCounter > 0) {
        const offsetEndCursor = getEndCursor(currentPage + currentLoadMore - iterationsCounter);
        const currentCacheSlice = readProducts(
            queryDocument,
            client,
            urlSlug,
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

export const getOffsetPageAndLoadMore = (
    currentPage: number,
    currentLoadMore: number,
    pageSize = DEFAULT_PAGE_SIZE,
) => {
    const loadedProductsDifference = calculatePageSize(currentLoadMore, pageSize) - PRODUCT_LIST_LIMIT;

    if (loadedProductsDifference <= 0) {
        return undefined;
    }

    const pageOffset = Math.ceil(loadedProductsDifference / pageSize);

    return {
        updatedPage: currentPage + pageOffset,
        updatedLoadMore: currentLoadMore - pageOffset,
    };
};

export const getRedirectWithOffsetPage = (
    currentPage: number,
    currentLoadMore: number,
    currentSlug: string,
    currentQuery: ParsedUrlQuery,
    pageSize = DEFAULT_PAGE_SIZE,
): { redirect: Redirect } | undefined => {
    const updatedQueries = getOffsetPageAndLoadMore(currentPage, currentLoadMore, pageSize);

    if (!updatedQueries) {
        return undefined;
    }

    const updatedQuery: ParsedUrlQuery = getUrlQueriesWithoutDynamicPageQueries(currentQuery);
    const searchParams = new URLSearchParams();

    for (const [key, value] of Object.entries(updatedQuery)) {
        if (!value) {
            continue;
        }

        if (Array.isArray(value)) {
            value.forEach((v) => searchParams.append(key, v));
        } else {
            searchParams.set(key, value);
        }
    }

    if (updatedQueries.updatedPage > 1) {
        searchParams.set(PAGE_QUERY_PARAMETER_NAME, updatedQueries.updatedPage.toString());
    } else {
        searchParams.delete(PAGE_QUERY_PARAMETER_NAME);
    }

    if (updatedQueries.updatedLoadMore > 0) {
        searchParams.set(LOAD_MORE_QUERY_PARAMETER_NAME, updatedQueries.updatedLoadMore.toString());
    } else {
        searchParams.delete(LOAD_MORE_QUERY_PARAMETER_NAME);
    }

    return {
        redirect: {
            destination: `${currentSlug}?${searchParams.toString()}`,
            permanent: false,
        },
    };
};

export const getPageSizeInfo = (
    readProductsFromCache: boolean,
    currentLoadMore: number,
    pageSize = DEFAULT_PAGE_SIZE,
) => {
    if (readProductsFromCache) {
        return { pageSize, isMoreThanOnePage: false };
    }

    return { pageSize: calculatePageSize(currentLoadMore, pageSize), isMoreThanOnePage: true };
};

export const hasReadAllProductsFromCache = (
    productsFromCacheLength: number | undefined,
    currentLoadMore: number,
    currentPage: number,
    totalProductCount: number,
    pageSize = DEFAULT_PAGE_SIZE,
) => {
    return (
        totalProductCount - (currentPage - 1) * pageSize === productsFromCacheLength ||
        productsFromCacheLength === calculatePageSize(currentLoadMore, pageSize)
    );
};

export const calculatePageSize = (currentLoadMore: number, pageSize = DEFAULT_PAGE_SIZE) => {
    return pageSize * (currentLoadMore + 1);
};

export const useProductsData = (
    queryDocument:
        | typeof BrandProductsQueryDocumentApi
        | typeof FlagProductsQueryDocumentApi
        | typeof CategoryProductsQueryDocumentApi,
    totalProductCount: number,
    additionalParams?: {
        shouldAbortFetchingProducts: boolean;
        abortedFetchCallback: () => void;
    },
): [ListedProductConnectionFragmentApi['edges'] | undefined, boolean, boolean, boolean] => {
    const client = useClient();
    const { asPath } = useRouter();
    const { filter, sort, currentPage, currentLoadMore } = useQueryParams();
    const urlSlug = getSlugFromUrl(asPath);
    const mappedFilter = mapParametersFilter(filter);

    const previousLoadMoreRef = useRef(currentLoadMore);
    const previousPageRef = useRef(currentPage);
    const initialPageSizeRef = useRef(calculatePageSize(currentLoadMore));

    const [productsData, setProductsData] = useState(
        readProductsFromCache(
            queryDocument,
            client,
            urlSlug,
            sort,
            mappedFilter,
            getEndCursor(currentPage),
            initialPageSizeRef.current,
        ),
    );

    const [fetching, setFetching] = useState(!productsData.products);
    const [loadMoreFetching, setLoadMoreFetching] = useState(false);

    const fetchProducts = async (
        variables: CategoryProductsQueryVariablesApi | FlagProductsQueryVariablesApi | BrandProductsQueryVariablesApi,
        previouslyQueriedProductsFromCache: ListedProductConnectionFragmentApi['edges'] | undefined,
    ) => {
        const response = await client.query(queryDocument, variables).toPromise();

        if (!response.data) {
            setProductsData({ products: undefined, hasNextPage: false });

            return;
        }

        setProductsData({
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
        if (additionalParams?.shouldAbortFetchingProducts) {
            additionalParams.abortedFetchCallback();

            return;
        }

        if (previousPageRef.current !== currentPage) {
            previousPageRef.current = currentPage;
            initialPageSizeRef.current = DEFAULT_PAGE_SIZE;
        }

        const previousProductsFromCache = getPreviousProductsFromCache(
            queryDocument,
            client,
            urlSlug,
            sort,
            mappedFilter,
            DEFAULT_PAGE_SIZE,
            initialPageSizeRef.current,
            currentPage,
            currentLoadMore,
            readProductsFromCache,
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
                urlSlug,
                pageSize,
            },
            previousProductsFromCache,
        );
    }, [urlSlug, sort, JSON.stringify(filter), currentPage, currentLoadMore]);

    return [productsData.products, productsData.hasNextPage, fetching, loadMoreFetching];
};

const readProductsFromCache = (
    queryDocument:
        | typeof BrandProductsQueryDocumentApi
        | typeof FlagProductsQueryDocumentApi
        | typeof CategoryProductsQueryDocumentApi,
    client: Client,
    urlSlug: string,
    orderingMode: ProductOrderingModeEnumApi | null,
    filter: Maybe<ProductFilterApi>,
    endCursor: string,
    pageSize: number,
): {
    products: ListedProductConnectionFragmentApi['edges'] | undefined;
    hasNextPage: boolean;
} => {
    const dataFromCache = client.readQuery(queryDocument, {
        urlSlug,
        orderingMode,
        filter,
        endCursor,
        pageSize,
    })?.data?.products;

    return {
        products: dataFromCache?.edges,
        hasNextPage: !!dataFromCache?.pageInfo.hasNextPage,
    };
};

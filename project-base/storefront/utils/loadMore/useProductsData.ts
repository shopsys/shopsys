import { calculatePageSize } from './calculatePageSize';
import { getPageSizeInfo } from './getPageSizeInfo';
import { getPreviousProductsFromCache } from './getPreviousProductsFromCache';
import { hasReadAllItemsFromCache } from './hasReadAllItemsFromCache';
import { mergeItemEdges } from './mergeItemEdges';
import { readProductsFromCache } from './readProductsFromCache';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { DocumentNode } from 'graphql';
import { TypeListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';
import {
    TypeBrandProductsQueryVariables,
    TypeBrandProductsQuery,
} from 'graphql/requests/products/queries/BrandProductsQuery.generated';
import {
    TypeCategoryProductsQueryVariables,
    TypeCategoryProductsQuery,
} from 'graphql/requests/products/queries/CategoryProductsQuery.generated';
import {
    TypeFlagProductsQueryVariables,
    TypeFlagProductsQuery,
} from 'graphql/requests/products/queries/FlagProductsQuery.generated';
import { useRouter } from 'next/router';
import { useRef, useState, useEffect } from 'react';
import { useClient } from 'urql';
import { mapParametersFilter } from 'utils/filterOptions/mapParametersFilter';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useCurrentSortQuery } from 'utils/queryParams/useCurrentSortQuery';

export const useProductsData = (
    queryDocument: DocumentNode,
    totalProductCount: number,
    additionalParams?: {
        shouldAbortFetchingProducts: boolean;
        abortedFetchCallback: () => void;
    },
) => {
    const client = useClient();
    const { asPath } = useRouter();
    const currentPage = useCurrentPageQuery();
    const currentFilter = useCurrentFilterQuery();
    const currentSort = useCurrentSortQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();
    const urlSlug = getSlugFromUrl(asPath);
    const mappedFilter = mapParametersFilter(currentFilter);
    const currentFilterSerialized = JSON.stringify(currentFilter);
    const shouldAbortFetchingProducts = additionalParams?.shouldAbortFetchingProducts ?? false;

    const previousLoadMoreRef = useRef(currentLoadMore);
    const previousPageRef = useRef(currentPage);
    const initialPageSizeRef = useRef(calculatePageSize(currentLoadMore));
    const abortedFetchCallbackRef = useRef<(() => void) | undefined>(additionalParams?.abortedFetchCallback);
    const hasHandledAbortRef = useRef(false);
    const totalProductCountRef = useRef(totalProductCount);
    totalProductCountRef.current = totalProductCount;

    const [productsData, setProductsData] = useState(
        readProductsFromCache(
            queryDocument,
            client,
            urlSlug,
            currentSort,
            mappedFilter,
            getEndCursor(currentPage),
            initialPageSizeRef.current,
        ),
    );

    const [areProductsFetching, setAreProductsFetching] = useState(!productsData.products);
    const [isLoadingMoreProducts, setIsLoadingMoreProducts] = useState(false);

    useEffect(() => {
        abortedFetchCallbackRef.current = additionalParams?.abortedFetchCallback;
    }, [additionalParams?.abortedFetchCallback]);

    useEffect(() => {
        if (shouldAbortFetchingProducts) {
            if (!hasHandledAbortRef.current) {
                hasHandledAbortRef.current = true;
                abortedFetchCallbackRef.current?.();
            }

            return;
        }

        hasHandledAbortRef.current = false;

        if (previousPageRef.current !== currentPage) {
            previousPageRef.current = currentPage;
            initialPageSizeRef.current = DEFAULT_PAGE_SIZE;
        }

        const previousProductsFromCache = getPreviousProductsFromCache(
            queryDocument,
            client,
            urlSlug,
            currentSort,
            mappedFilter,
            DEFAULT_PAGE_SIZE,
            initialPageSizeRef.current,
            currentPage,
            currentLoadMore,
            readProductsFromCache,
        );

        if (
            hasReadAllItemsFromCache(
                previousProductsFromCache?.length,
                currentLoadMore,
                currentPage,
                totalProductCountRef.current,
            )
        ) {
            return;
        }

        const { pageSize, isMoreThanOnePage } = getPageSizeInfo(!!previousProductsFromCache, currentLoadMore);
        const endCursor = getEndCursor(currentPage, isMoreThanOnePage ? undefined : currentLoadMore);

        if (previousLoadMoreRef.current === currentLoadMore || currentLoadMore === 0) {
            setAreProductsFetching(true);
        } else {
            setIsLoadingMoreProducts(true);
            previousLoadMoreRef.current = currentLoadMore;
        }

        const fetchProducts = async () => {
            const productsResponse = await client
                .query<
                    TypeCategoryProductsQuery | TypeBrandProductsQuery | TypeFlagProductsQuery,
                    | TypeCategoryProductsQueryVariables
                    | TypeFlagProductsQueryVariables
                    | TypeBrandProductsQueryVariables
                >(queryDocument, {
                    endCursor,
                    filter: mappedFilter,
                    orderingMode: currentSort,
                    urlSlug,
                    pageSize,
                })
                .toPromise();

            if (!productsResponse.data) {
                setProductsData({ products: undefined, hasNextPage: false });

                return;
            }

            setProductsData({
                products: mergeItemEdges(
                    previousProductsFromCache,
                    productsResponse.data.products.edges,
                ) as TypeListedProductConnectionFragment['edges'],
                hasNextPage: productsResponse.data.products.pageInfo.hasNextPage,
            });
            setAreProductsFetching(false);
            setIsLoadingMoreProducts(false);
        };

        fetchProducts();
        // eslint-disable-next-line react-hooks/exhaustive-deps -- mappedFilter is derived from currentFilter (tracked via currentFilterSerialized), totalProductCount is tracked via ref to avoid redundant re-fetches when category detail query updates the count
    }, [urlSlug, currentSort, currentFilterSerialized, currentPage, currentLoadMore, queryDocument, client]);

    return { ...productsData, areProductsFetching, isLoadingMoreProducts };
};

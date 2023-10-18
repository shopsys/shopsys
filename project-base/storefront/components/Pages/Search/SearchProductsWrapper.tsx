import { ResultProducts } from './ResultProducts';
import { useSearchProductsData } from './helpers';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ListedProductConnectionPreviewFragmentApi } from 'graphql/generated';
import { useGtmPaginatedProductListViewEvent } from 'gtm/hooks/productList/useGtmPaginatedProductListViewEvent';
import { GtmProductListNameType } from 'gtm/types/enums';
import { getMappedProducts } from 'helpers/mappers/products';
import { RefObject } from 'react';

type SearchProductsWrapperProps = {
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
    productsSearch: ListedProductConnectionPreviewFragmentApi;
};

export const SearchProductsWrapper: FC<SearchProductsWrapperProps> = ({
    paginationScrollTargetRef,
    productsSearch,
}) => {
    const [searchProductsData, hasNextPage, fetching, loadMoreFetching] = useSearchProductsData(
        productsSearch.totalCount,
    );

    const searchResultProducts = getMappedProducts(searchProductsData);

    useGtmPaginatedProductListViewEvent(searchResultProducts, GtmProductListNameType.search_results);

    return (
        <>
            {searchResultProducts && (
                <ResultProducts
                    areProductsShowed={productsSearch.totalCount > 0}
                    fetching={fetching}
                    loadMoreFetching={loadMoreFetching}
                    noProductsFound={parseInt(productsSearch.productFilterOptions.maximalPrice) === 0}
                    products={searchResultProducts}
                />
            )}
            <Pagination
                isWithLoadMore
                hasNextPage={hasNextPage}
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={productsSearch.totalCount}
            />
        </>
    );
};

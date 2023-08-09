import { ResultProducts } from './ResultProducts';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { getMappedProducts } from 'helpers/mappers/products';
import { useGtmPaginatedProductListViewEvent } from 'gtm/hooks/productList/useGtmPaginatedProductListViewEvent';
import { RefObject } from 'react';
import { GtmProductListNameType } from 'gtm/types/enums';
import { useSearchProductsData } from './helpers';
import { ListedProductConnectionPreviewFragmentApi } from 'graphql/generated';

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
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={productsSearch.totalCount}
                isWithLoadMore
                hasNextPage={hasNextPage}
            />
        </>
    );
};

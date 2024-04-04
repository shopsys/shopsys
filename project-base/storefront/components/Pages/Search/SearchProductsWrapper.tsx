import { ResultProducts } from './ResultProducts';
import { useSearchProductsData } from './utils';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { TypeListedProductConnectionPreviewFragment } from 'graphql/requests/products/fragments/ListedProductConnectionPreviewFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmPaginatedProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmPaginatedProductListViewEvent';
import { RefObject } from 'react';
import { getMappedProducts } from 'utils/mappers/products';

type SearchProductsWrapperProps = {
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
    productsSearch: TypeListedProductConnectionPreviewFragment;
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

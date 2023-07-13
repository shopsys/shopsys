import { ResultProducts } from './ResultProducts';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { useSearchProductsQueryApi } from 'graphql/generated';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { getMappedProducts } from 'helpers/mappers/products';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPaginatedProductListViewEvent } from 'hooks/gtm/productList/useGtmPaginatedProductListViewEvent';
import { useQueryParams } from 'hooks/useQueryParams';
import { RefObject } from 'react';
import { GtmProductListNameType } from 'types/gtm/enums';

type SearchProductsWrapperProps = {
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const SearchProductsWrapper: FC<SearchProductsWrapperProps> = ({ paginationScrollTargetRef }) => {
    const { currentPage, sort, filter, searchString } = useQueryParams();

    const [{ data: searchProductsData, fetching }] = useQueryError(
        useSearchProductsQueryApi({
            variables: {
                endCursor: getEndCursor(currentPage),
                filter: mapParametersFilter(filter),
                orderingMode: sort,
                search: searchString ?? '',
                pageSize: DEFAULT_PAGE_SIZE,
            },
        }),
    );

    const searchResultProducts = getMappedProducts(searchProductsData?.products.edges);

    useGtmPaginatedProductListViewEvent(searchResultProducts, GtmProductListNameType.search_results);

    return (
        <>
            {searchResultProducts && (
                <ResultProducts
                    areProductsShowed={(searchProductsData?.products.totalCount ?? 0) > 0}
                    fetching={fetching}
                    noProductsFound={
                        parseInt(searchProductsData?.products.productFilterOptions.maximalPrice ?? '') === 0
                    }
                    products={searchResultProducts}
                />
            )}
            <Pagination
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={searchProductsData?.products.totalCount ?? 0}
            />
        </>
    );
};

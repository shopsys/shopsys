import { ResultProducts } from './ResultProducts';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { useSearchProductsQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getMappedProducts } from 'helpers/mappers/products';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import {
    FILTER_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPaginatedProductListViewEvent } from 'hooks/gtm/productList/useGtmPaginatedProductListViewEvent';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { RefObject } from 'react';
import { GtmProductListNameType } from 'types/gtm/enums';

type SearchProductsWrapperProps = {
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const SearchProductsWrapper: FC<SearchProductsWrapperProps> = ({ paginationScrollTargetRef }) => {
    const { query } = useRouter();
    const { currentPage } = useQueryParams();
    const queryString = getStringFromUrlQuery(query[SEARCH_QUERY_PARAMETER_NAME]);
    const orderingMode = getProductListSort(parseProductListSortFromQuery(query[SORT_QUERY_PARAMETER_NAME]));
    const parametersFilter = getFilterOptions(parseFilterOptionsFromQuery(query[FILTER_QUERY_PARAMETER_NAME]));

    const [{ data: searchProductsData, fetching }] = useQueryError(
        useSearchProductsQueryApi({
            variables: {
                endCursor: getEndCursor(currentPage),
                filter: mapParametersFilter(parametersFilter),
                orderingMode,
                search: queryString,
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

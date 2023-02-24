import { ResultProducts } from './ResultProducts/ResultProducts';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { ListedProductFragmentApi, useSearchProductsQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import {
    FILTER_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmSearchResultsListView } from 'hooks/gtm/useGtmSearchResultsListView';
import { useListingForPagination } from 'hooks/ui/useListingForPagination';
import { useRouter } from 'next/router';
import { RefObject } from 'react';

type SearchProductsWrapperProps = {
    containerWrapperRef: RefObject<HTMLDivElement>;
};

export const SearchProductsWrapper: FC<SearchProductsWrapperProps> = ({ containerWrapperRef }) => {
    const { query } = useRouter();
    const [{ endCursor }] = usePaginationContext();
    const queryString = getStringFromUrlQuery(query[SEARCH_QUERY_PARAMETER_NAME]);
    const orderingMode = getProductListSort(parseProductListSortFromQuery(query[SORT_QUERY_PARAMETER_NAME]));
    const parametersFilter = getFilterOptions(parseFilterOptionsFromQuery(query[FILTER_QUERY_PARAMETER_NAME]));

    const [{ data, fetching }] = useQueryError(
        useSearchProductsQueryApi({
            variables: {
                endCursor: endCursor ?? '',
                filter: mapParametersFilter(parametersFilter),
                orderingMode,
                search: queryString,
                pageSize: DEFAULT_PAGE_SIZE,
            },
        }),
    );

    const [dataItems] = useListingForPagination<ListedProductFragmentApi>(data?.products.edges);

    useGtmSearchResultsListView(dataItems, queryString);

    return (
        <>
            <ResultProducts
                areProductsShowed={(data?.products.totalCount ?? 0) > 0}
                fetching={fetching}
                noProductsFound={parseInt(data?.products.productFilterOptions.maximalPrice ?? '') === 0}
                products={dataItems}
            />
            <Pagination containerWrapRef={containerWrapperRef} totalCount={data?.products.totalCount ?? 0} />
        </>
    );
};

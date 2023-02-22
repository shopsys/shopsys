import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { ProductOrderingModeEnumApi, SearchQueryApi, useSearchQueryApi } from 'graphql/generated';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { FilterOptionsUrlQueryType } from 'types/productFilter';

export const useSearch = (
    searchQuery: string,
    searchProductsSort: ProductOrderingModeEnumApi | null,
    optionsFilter: FilterOptionsUrlQueryType | null,
): SearchQueryApi | undefined => {
    const [result] = useSearchQueryApi({
        variables: {
            search: searchQuery,
            orderingMode: searchProductsSort,
            filter: mapParametersFilter(optionsFilter),
            pageSize: DEFAULT_PAGE_SIZE,
        },
    });
    useQueryError(result.error);

    return result.data;
};

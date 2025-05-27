import { useSearchParams } from 'next/navigation';
import { FilterOptionsUrlQueryType } from 'types/productFilter';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { FILTER_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useCurrentFilterQuery = (): FilterOptionsUrlQueryType | null => {
    try {
        const searchParams = useSearchParams();

        if (!searchParams) {
            return null;
        }

        const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(searchParams) as UrlQueries;
        const filterQueryAsString = query[FILTER_QUERY_PARAMETER_NAME];

        if (!filterQueryAsString) {
            return null;
        }

        const filterQuery = JSON.parse(filterQueryAsString) as FilterOptionsUrlQueryType;

        return filterQuery;
    } catch {
        // Failed to parse filter query or router not mounted, return null
        return null;
    }
};

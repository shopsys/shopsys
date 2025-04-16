import { useSearchParams } from 'next/navigation';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { SEARCH_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useCurrentSearchStringQuery = () => {
    const searchParams = useSearchParams();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(searchParams) as UrlQueries;
    const searchStringQuery = query[SEARCH_QUERY_PARAMETER_NAME];

    return searchStringQuery;
};

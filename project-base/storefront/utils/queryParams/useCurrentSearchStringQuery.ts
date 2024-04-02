import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { SEARCH_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useCurrentSearchStringQuery = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const searchStringQuery = query[SEARCH_QUERY_PARAMETER_NAME];

    return searchStringQuery;
};

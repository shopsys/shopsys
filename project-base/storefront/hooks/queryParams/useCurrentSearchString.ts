import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'helpers/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { SEARCH_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';

export const useCurrentSearchString = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const searchString = query[SEARCH_QUERY_PARAMETER_NAME];

    return searchString;
};

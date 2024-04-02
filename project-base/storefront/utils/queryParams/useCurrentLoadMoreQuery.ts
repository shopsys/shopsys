import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { LOAD_MORE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useCurrentLoadMoreQuery = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const currentLoadMoreQuery = Number(query[LOAD_MORE_QUERY_PARAMETER_NAME] || 0);

    return currentLoadMoreQuery;
};

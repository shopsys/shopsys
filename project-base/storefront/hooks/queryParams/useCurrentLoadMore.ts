import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'helpers/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { LOAD_MORE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';

export const useCurrentLoadMore = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const currentLoadMore = Number(query[LOAD_MORE_QUERY_PARAMETER_NAME] || 0);

    return currentLoadMore;
};

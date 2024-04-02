import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useCurrentPageQuery = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const currentPageQuery = Number(query[PAGE_QUERY_PARAMETER_NAME] || 1);

    return currentPageQuery;
};

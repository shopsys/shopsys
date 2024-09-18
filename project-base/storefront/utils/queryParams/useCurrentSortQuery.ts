import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';
import { getProductListSortFromUrlQuery } from 'utils/parsing/getProductListSortFromUrlQuery';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { SORT_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useCurrentSortQuery = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const sortQuery = getProductListSortFromUrlQuery(query[SORT_QUERY_PARAMETER_NAME]);

    return sortQuery;
};

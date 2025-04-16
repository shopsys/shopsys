import { useSearchParams } from 'next/navigation';
import { UrlQueries } from 'types/urlQueries';
import { getProductListSortFromUrlQuery } from 'utils/parsing/getProductListSortFromUrlQuery';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { SORT_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useCurrentSortQuery = () => {
    const searchParams = useSearchParams();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(searchParams) as UrlQueries;
    const sortQuery = getProductListSortFromUrlQuery(query[SORT_QUERY_PARAMETER_NAME]);

    return sortQuery;
};

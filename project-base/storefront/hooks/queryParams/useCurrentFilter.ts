import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'helpers/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { FILTER_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { useRouter } from 'next/router';
import { FilterOptionsUrlQueryType } from 'types/productFilter';
import { UrlQueries } from 'types/urlQueries';

export const useCurrentFilter = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const filterQuery = query[FILTER_QUERY_PARAMETER_NAME];
    const filter = filterQuery ? (JSON.parse(filterQuery) as FilterOptionsUrlQueryType) : null;

    return filter;
};
